<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\AutoPublish;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Domain\Event\Event\PublishedEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\EventImport;
use Manuxi\SuluEventBundle\Entity\EventImportRun;
use Manuxi\SuluEventBundle\Import\ImportSettings;
use Manuxi\SuluEventBundle\Import\ImportUserSwitcher;
use Manuxi\SuluEventBundle\Repository\EventImportRepository;
use Manuxi\SuluEventBundle\Repository\EventImportRunRepository;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;

/**
 * Publishes imported events by themselves once they are complete (sulu_event.import.auto_publish), the way the admin does:
 * through the content workflow, as the import user, with the domain events (search, activity log, cache).
 *
 * Events whose import entry is "filtered", "rejected" or "vanished" are never touched, nor are events that are over. A language an editor unpublished
 * or put in review is left alone, and a published event that got a new draft is only published again if
 * "republish_changes" is on and no field of it is locked by hand.
 *
 * What a run did is kept as a run of its own (source "auto-publish", no database change): the settings tab and the report
 * tool show it.
 *
 * @phpstan-type Item array{uuid: string, title: string, startDate: string|null, source: string, state: string, action: string|null, locales: list<string>, missing: list<string>, note: string|null}
 */
class AutoPublisher
{
    public const STATE_READY   = 'ready';
    public const STATE_WAITING = 'waiting';
    public const STATE_SKIPPED = 'skipped';

    public const ACTION_PUBLISH   = 'publish';
    public const ACTION_REPUBLISH = 'republish';

    public const MISSING_TITLE    = 'title';
    public const MISSING_SUBTITLE = 'subtitle';
    public const MISSING_SUMMARY  = 'summary';
    public const MISSING_TEXT     = 'text';
    public const MISSING_LOCALE   = 'locale';
    public const MISSING_CATEGORY = 'categories';
    public const MISSING_IMAGE    = 'image';
    public const MISSING_PLACE    = 'place';

    public const NOTE_NOT_COVERED = 'not covered by the conditions of "when"';

    /** @var \Closure(): \DateTimeImmutable */
    private \Closure $now;

    public function __construct(
        private readonly EventImportRepository $importRepository,
        private readonly EventRepository $eventRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentWorkflowInterface $contentWorkflow,
        private readonly DomainEventCollectorInterface $domainEventCollector,
        private readonly ImportUserSwitcher $userSwitcher,
        private readonly EventImportRunRepository $runRepository,
        private readonly AutoPublishRules $rules,
        private readonly ImportSettings $settings,
        ?\Closure $now = null,
    ) {
        $this->now = $now ?? static fn (): \DateTimeImmutable => new \DateTimeImmutable();
    }

    public function isEnabled(): bool
    {
        return $this->rules->enabled;
    }

    public function getRules(): AutoPublishRules
    {
        return $this->rules;
    }

    /**
     * Looks at every imported event and says what would happen to it.
     *
     * @return list<Item>
     */
    public function evaluateAll(): array
    {
        /** @var array<string, list<EventImport>> $byEvent */
        $byEvent = [];
        foreach ($this->importRepository->findAllWithEvent() as $import) {
            $event = $import->getEvent();
            if (null !== $event) {
                $byEvent[$event->getUuid()][] = $import;
            }
        }

        $items = [];
        foreach ($byEvent as $uuid => $imports) {
            $event = $this->eventRepository->findByUuid($uuid);
            if (null !== $event) {
                $items[] = $this->evaluate($event, $imports);
            }
        }

        usort($items, static fn (array $a, array $b): int => ($a['startDate'] ?? '9999') <=> ($b['startDate'] ?? '9999'));

        return $items;
    }

    /**
     * What an event needs before the automatic publishing takes it, for the tool that lists the events that need work.
     * Null if the rules do not concern the event (switched off, not an imported event, not covered by "when").
     *
     * @return array{state: string, action: string|null, missing: list<string>, note: string|null}|null
     */
    public function assess(Event $event): ?array
    {
        if (!$this->rules->enabled) {
            return null;
        }

        $imports = array_values($this->importRepository->findByEventUuid($event->getUuid()));
        if ([] === $imports) {
            return null;
        }

        $item = $this->evaluate($event, $imports);
        if (self::STATE_SKIPPED === $item['state'] && self::NOTE_NOT_COVERED === $item['note']) {
            return null;
        }

        return ['state' => $item['state'], 'action' => $item['action'], 'missing' => $item['missing'], 'note' => $item['note']];
    }

    /**
     * Publishes what is ready, and keeps the result.
     *
     * @return array{published: int, waiting: int, skipped: int, errors: int, items: list<array<string, mixed>>}
     */
    public function publishReady(bool $dryRun = false, ?int $limit = null): array
    {
        $result = ['published' => 0, 'waiting' => 0, 'skipped' => 0, 'errors' => 0, 'items' => []];
        if (!$this->rules->enabled) {
            return $result;
        }

        $run  = new EventImportRun(new \DateTimeImmutable(), EventImportRun::SOURCE_AUTO_PUBLISH, $dryRun);
        $done = 0;

        foreach ($this->evaluateAll() as $item) {
            if (self::STATE_WAITING === $item['state']) {
                ++$result['waiting'];
                $result['items'][] = $item + ['result' => 'waiting'];

                continue;
            }

            if (self::STATE_SKIPPED === $item['state']) {
                ++$result['skipped'];
                $result['items'][] = $item + ['result' => 'skipped'];

                continue;
            }

            if (null !== $limit && $done >= $limit) {
                ++$result['skipped'];
                $result['items'][] = ['note' => 'over the limit of this run'] + $item + ['result' => 'skipped'];

                continue;
            }

            if ($dryRun) {
                ++$done;
                ++$result['published'];
                $result['items'][] = $item + ['result' => 'would publish'];

                continue;
            }

            try {
                $this->publish($item);
                ++$done;
                ++$result['published'];
                $result['items'][] = $item + ['result' => 'published'];
            } catch (\Throwable $e) {
                ++$result['errors'];
                $result['items'][] = ['note' => $e->getMessage()] + $item + ['result' => 'error'];

                // a failed flush closes the entity manager: nothing more can be done in this run
                if (!$this->entityManager->isOpen()) {
                    break;
                }
                $this->entityManager->clear();
            }
        }

        if ($this->entityManager->isOpen()) {
            $this->store($run, $result);
        }

        return $result;
    }

    /**
     * @param array{published: int, waiting: int, skipped: int, errors: int, items: list<array<string, mixed>>} $result
     */
    private function store(EventImportRun $run, array $result): void
    {
        $counts = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'locked' => 0, 'vanished' => 0, 'rejected' => 0, 'filtered' => 0, 'errors' => $result['errors'], 'newLocations' => 0];

        $waiting   = [];
        $published = [];
        foreach ($result['items'] as $item) {
            if ('waiting' === $item['result']) {
                $waiting[] = ['uuid' => $item['uuid'], 'title' => $item['title'], 'missing' => $item['missing']];
            } elseif (\in_array($item['result'], ['published', 'would publish'], true)) {
                $published[] = ['uuid' => $item['uuid'], 'title' => $item['title'], 'action' => $item['action'], 'locales' => $item['locales']];
            }
        }

        $run->finish(new \DateTimeImmutable(), $counts, [
            'entries'      => [],
            'newLocations' => [],
            'warnings'     => [],
            'autoPublish'  => [
                'published'      => $result['published'],
                'waiting'        => $result['waiting'],
                'skipped'        => $result['skipped'],
                'errors'         => $result['errors'],
                'publishedItems' => $published,
                'waitingItems'   => $waiting,
            ],
        ]);
        $this->runRepository->add($run);
        $this->entityManager->flush();
        $this->runRepository->prune();
    }

    /**
     * @param Item $item
     */
    private function publish(array $item): void
    {
        $this->userSwitcher->runAsImportUser(function () use ($item): void {
            $event = $this->eventRepository->findByUuid($item['uuid']);
            if (null === $event) {
                throw new \RuntimeException('The event does not exist any more.');
            }

            foreach ($item['locales'] as $locale) {
                $this->contentWorkflow->apply($event, ['locale' => $locale], WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH);
                $this->domainEventCollector->collect(new PublishedEvent($event, ['title' => $item['title'], 'locale' => $locale]));
            }

            $this->entityManager->flush();
        });
    }

    /**
     * @param list<EventImport> $imports the entries of the event, the one with the highest priority first
     *
     * @return Item
     */
    private function evaluate(Event $event, array $imports): array
    {
        $rows = [];
        foreach ($event->getDimensionContents() as $row) {
            // the current draft of a language, not the saved versions of it
            if ($row instanceof EventDimensionContent && DimensionContentInterface::STAGE_DRAFT === $row->getStage() && null !== $row->getLocale() && DimensionContentInterface::CURRENT_VERSION === $row->getVersion()) {
                $rows[$row->getLocale()] = $row;
            }
        }

        $first = [] !== $rows ? $rows[array_key_first($rows)] : null;
        $item  = [
            'uuid'      => $event->getUuid(),
            'title'     => $first?->getTitle() ?? $event->getUuid(),
            'startDate' => $first?->getStartDate()?->format('Y-m-d H:i:s'),
            'source'    => implode(', ', array_map(static fn (EventImport $entry): string => $entry->getSource(), $imports)),
            'state'     => self::STATE_SKIPPED,
            'action'    => null,
            'locales'   => [],
            'missing'   => [],
            'note'      => null,
        ];

        $active = array_values(array_filter($imports, static fn (EventImport $entry): bool => EventImport::STATE_ACTIVE === $entry->getState()));
        if ([] === $active) {
            return ['note' => \sprintf('the import entry is "%s"', $imports[0]->getState())] + $item;
        }

        if (!$this->matchesWhen($active, $first)) {
            return ['note' => self::NOTE_NOT_COVERED] + $item;
        }

        if ([] === $rows) {
            return ['note' => 'the event has no draft'] + $item;
        }

        // nobody is helped by an event that is over
        $over = $first?->getEndDate() ?? $first?->getStartDate();
        if (null !== $over && $over < ($this->now)()) {
            return ['note' => 'the event is over'] + $item;
        }

        $locales = [] !== $this->rules->locales ? $this->rules->locales : array_keys($rows);

        // what is to be done per language; a decision of an editor stops the whole event
        $publish   = [];
        $republish = [];
        foreach ($locales as $locale) {
            $row = $rows[$locale] ?? null;
            if (null === $row) {
                continue;
            }

            $place = $row->getWorkflowPlace();
            if (WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $place) {
                continue;
            }

            if (\in_array($place, [WorkflowInterface::WORKFLOW_PLACE_REVIEW, WorkflowInterface::WORKFLOW_PLACE_REVIEW_DRAFT], true)) {
                return ['note' => \sprintf('%s: in review, an editor decides', $locale)] + $item;
            }

            $wasPublished = null !== $row->getWorkflowPublished();
            if (WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED === $place && $wasPublished) {
                return ['note' => \sprintf('%s: was unpublished by an editor', $locale)] + $item;
            }

            if ($wasPublished) {
                $republish[] = $locale;
            } else {
                $publish[] = $locale;
            }
        }

        if ([] === $publish && [] === $republish) {
            return ['note' => 'already published'] + $item;
        }

        if ([] !== $republish) {
            if (!$this->rules->republishChanges) {
                return ['note' => 'published, with changes waiting (republish_changes is off)'] + $item;
            }

            $locked = [];
            foreach ($imports as $entry) {
                $locked = array_merge($locked, $entry->getLockedFields());
            }
            if ([] !== $locked) {
                return ['note' => 'fields locked by hand: ' . implode(', ', array_values(array_unique($locked)))] + $item;
            }
        }

        $item['missing'] = $this->missing($rows, $locales);
        $item['locales'] = array_merge($publish, $republish);
        $item['action']  = [] === $publish ? self::ACTION_REPUBLISH : self::ACTION_PUBLISH;
        $item['state']   = [] === $item['missing'] ? self::STATE_READY : self::STATE_WAITING;

        return $item;
    }

    /**
     * @param list<EventImport> $active
     */
    private function matchesWhen(array $active, ?EventDimensionContent $row): bool
    {
        foreach ($active as $entry) {
            if ($this->rules->series && null !== $entry->getMatchKey() && '' !== $entry->getMatchKey()) {
                return true;
            }

            if (\in_array($entry->getSource(), $this->rules->sources, true)) {
                return true;
            }
        }

        if ([] !== $this->rules->types) {
            return \in_array($row?->getType() ?? $this->settings->defaultType, $this->rules->types, true);
        }

        return false;
    }

    /**
     * @param array<string, EventDimensionContent> $rows
     * @param list<string>                         $locales
     *
     * @return list<string>
     */
    private function missing(array $rows, array $locales): array
    {
        $missing = [];
        foreach ($locales as $locale) {
            $row = $rows[$locale] ?? null;
            if (null === $row) {
                $missing[] = self::MISSING_LOCALE . ':' . $locale;

                continue;
            }

            $fields = [
                self::MISSING_TITLE    => $row->getTitle(),
                self::MISSING_SUBTITLE => $row->getSubtitle(),
                self::MISSING_SUMMARY  => $row->getSummary(),
                self::MISSING_TEXT     => $row->getText(),
            ];
            foreach ($fields as $field => $value) {
                if (null === $value || '' === trim(strip_tags($value))) {
                    $missing[] = $field . ':' . $locale;
                }
            }
        }

        $category = false;
        $image    = false;
        $place    = false;
        foreach ($rows as $row) {
            $category = $category || []   !== $row->getExcerptCategories();
            $image    = $image    || null !== $row->getImage() || null !== $row->getExcerptImage();
            $place    = $place    || null !== $row->getLocation() || '' !== trim((string) $row->getOnlineUrl());
        }

        if ($this->rules->requireCategory && !$category) {
            $missing[] = self::MISSING_CATEGORY;
        }
        if ($this->rules->requireImage && !$image) {
            $missing[] = self::MISSING_IMAGE;
        }
        if ($this->rules->requirePlaceOrOnline && !$place) {
            $missing[] = self::MISSING_PLACE;
        }

        return $missing;
    }
}
