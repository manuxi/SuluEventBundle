<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Domain\Event\Event\CreatedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\ModifiedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\PublishedEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\EventImport;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Import\Filter\CandidateFilter;
use Manuxi\SuluEventBundle\Import\Filter\FilterResult;
use Manuxi\SuluEventBundle\Import\Resolver\CategoryResolver;
use Manuxi\SuluEventBundle\Import\Resolver\LocationResolver;
use Manuxi\SuluEventBundle\Import\Resolver\MediaResolver;
use Manuxi\SuluEventBundle\Import\Resolver\TagResolver;
use Manuxi\SuluEventBundle\Repository\EventImportRepository;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;

/**
 * Brings the candidates of the sources into the database as drafts.
 *
 * - A candidate is known by (source, external id); the match key joins the candidates of different sources for the
 *   same event. The source with the higher priority maintains the event, the others only keep their link.
 * - New events are created as drafts, nothing is ever published by the import (except date and status changes if
 *   "auto_update_published_dates" is on).
 * - Changes are written to the draft only. A field that was changed in the admin since the last import (it differs
 *   from what the import wrote) is locked: it is not overwritten, the difference is reported.
 * - Texts, categories and tags are only filled in, never replaced. A candidate whose event was deleted in the admin
 *   is not imported again.
 * - The sieve (CandidateFilter, rules of the project) runs before anything is created or changed: a candidate that does
 *   not pass gets no event, its entry is marked "filtered" with the reason; an event that exists is left alone.
 */
class EventImporter
{
    /** Fields that are the same in all locales (and mirrored into every locale row). */
    private const UNLOCALIZED_FIELDS = [
        'type', 'startDate', 'endDate', 'allDay', 'timezone', 'status', 'attendanceMode', 'locationId',
        'onlineUrl', 'registrationUrl', 'organizerName', 'organizerUrl', 'isFree', 'languages',
    ];

    /** Changes to these may go live by themselves ("auto_update_published_dates"). */
    private const LIVE_FIELDS = ['startDate', 'endDate', 'allDay', 'timezone', 'status'];

    private const PUBLISHED_PLACES = [
        WorkflowInterface::WORKFLOW_PLACE_PUBLISHED,
        WorkflowInterface::WORKFLOW_PLACE_DRAFT,
        WorkflowInterface::WORKFLOW_PLACE_REVIEW_DRAFT,
    ];

    /** @var \Closure(): \DateTimeImmutable */
    private \Closure $now;

    private EventImageChooser $imageChooser;

    private CandidateFilter $filter;

    /**
     * Entries whose source changed the external id of the event: source key and new id => id of the entry.
     *
     * @var array<string, int>
     */
    private array $adopted = [];

    /**
     * @param (\Closure(): \DateTimeImmutable)|null $now    replaceable for tests
     * @param CandidateFilter|null                  $filter the sieve; without it everything passes
     */
    public function __construct(
        private readonly SourceRegistry $sources,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventImportRepository $importRepository,
        private readonly EventRepository $eventRepository,
        private readonly ContentManagerInterface $contentManager,
        private readonly ContentWorkflowInterface $contentWorkflow,
        private readonly DomainEventCollectorInterface $domainEventCollector,
        private readonly LocationResolver $locationResolver,
        private readonly CategoryResolver $categoryResolver,
        private readonly TagResolver $tagResolver,
        private readonly ImportUrlGenerator $urlGenerator,
        private readonly ImportUserSwitcher $userSwitcher,
        private readonly ImportSettings $settings,
        private readonly MediaResolver $mediaResolver,
        ?\Closure $now = null,
        ?CandidateFilter $filter = null,
    ) {
        $this->now          = $now    ?? static fn (): \DateTimeImmutable => new \DateTimeImmutable();
        $this->filter       = $filter ?? new CandidateFilter();
        $this->imageChooser = new EventImageChooser($settings, $mediaResolver);
    }

    /**
     * @param string|null $sourceKey only this source
     * @param bool        $dryRun    compute and report everything, write nothing
     * @param int|null    $limit     at most this many candidates per source (the check for vanished events is skipped then)
     * @param (\Closure(string, int, int, int, int): void)|null $progress called after every candidate with the key of the source, its
     *                                 position and the number of sources, the candidates done and the candidates known so far
     *
     * @throws \InvalidArgumentException for an unknown source key
     */
    public function import(?string $sourceKey = null, bool $dryRun = false, ?int $limit = null, ?\Closure $progress = null): ImportReport
    {
        $sources = $this->sources->all();
        if (null !== $sourceKey) {
            if (!$this->sources->has($sourceKey)) {
                throw new \InvalidArgumentException(\sprintf('There is no import source "%s". Known sources: %s.', $sourceKey, [] === $sources ? '(none)' : implode(', ', $this->sources->getKeys())));
            }

            $sources = [$sourceKey => $sources[$sourceKey]];
        }

        if (!$dryRun) {
            // fails early with a clear message if the user is missing
            $this->userSwitcher->checkUser();
        }

        $report   = new ImportReport();
        $runStart = ($this->now)();

        // the progress of the whole run: candidates done and known so far
        $state = ['done' => 0, 'known' => 0, 'index' => 0, 'count' => \count($sources)];
        foreach ($sources as $key => $source) {
            ++$state['index'];
            $this->importSource($key, $source, $report, $dryRun, $limit, $runStart, $progress, $state);
        }

        return $report;
    }

    /**
     * @param (\Closure(string, int, int, int, int): void)|null $progress
     * @param array{done: int, known: int, index: int, count: int}  $state
     */
    private function importSource(string $key, EventSourceInterface $source, ImportReport $report, bool $dryRun, ?int $limit, \DateTimeImmutable $runStart, ?\Closure $progress = null, array &$state = ['done' => 0, 'known' => 0, 'index' => 1, 'count' => 1]): void
    {
        $delivered = [];
        $complete  = true;
        $count     = 0;

        try {
            $candidates = $source->fetch();
            // the candidates of the source are collected first (a few hundred small objects), so the progress knows the total
            if (!\is_array($candidates)) {
                $candidates = iterator_to_array($candidates, false);
            }
            $candidates = $this->mergeDuplicates($candidates);
            foreach ($this->findRenamed($key, $candidates) as $oldId) {
                // the old id is not "gone": the entry follows the event to its new id
                $delivered[$oldId] = true;
            }
            $state['known'] += null !== $limit ? min($limit, \count($candidates)) : \count($candidates);

            foreach ($candidates as $candidate) {
                if (null !== $limit && $count >= $limit) {
                    $complete = false;
                    break;
                }
                ++$count;

                $delivered[$candidate->externalId] = true;

                if (!$this->runCandidate($key, $source->getPriority(), $candidate, $report, $dryRun, $runStart)) {
                    return;
                }

                ++$state['done'];
                if (null !== $progress) {
                    $progress($key, $state['index'], $state['count'], $state['done'], $state['known']);
                }
            }
        } catch (\Throwable $e) {
            // the source itself failed (not reachable, not allowed by robots.txt, not a calendar): nothing is known
            // about the candidates, so none is marked as vanished
            $report->add(ImportReport::ACTION_ERROR, $key, null, null, null, $e->getMessage());

            return;
        }

        if ($complete) {
            $this->markVanished($key, $delivered, $report, $dryRun);
        }
    }

    /**
     * Imports one candidate that does not come from a source (the MCP tool for the editorial work): the same matching,
     * priority and lock rules as for a source, drafts only. It runs as the user who is logged in, not as the import
     * user.
     */
    public function importCandidate(string $sourceKey, int $priority, EventCandidate $candidate, bool $dryRun = false): ImportReport
    {
        $report = new ImportReport();
        $this->runCandidate($sourceKey, $priority, $candidate, $report, $dryRun, ($this->now)(), false);

        return $report;
    }

    /**
     * @return bool false if the entity manager is closed and nothing more can be done
     */
    private function runCandidate(string $key, int $priority, EventCandidate $candidate, ImportReport $report, bool $dryRun, \DateTimeImmutable $now, bool $asImportUser = true): bool
    {
        try {
            $process = fn () => $this->processCandidate($key, $priority, $candidate, $report, $dryRun, $now);
            $asImportUser ? $this->guard($dryRun, $process) : $process();
        } catch (\Throwable $e) {
            $report->add(ImportReport::ACTION_ERROR, $key, $candidate->externalId, $this->titleOf($candidate), null, $e->getMessage());
            if (!$this->entityManager->isOpen()) {
                $report->add(ImportReport::ACTION_ERROR, $key, null, null, null, 'The database connection of the import was closed by the error above, the run is stopped.');

                return false;
            }

            // drop what the failed candidate left half done
            $this->entityManager->clear();
            $this->locationResolver->reset();
            $this->tagResolver->reset();
        }

        return true;
    }

    /**
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    private function guard(bool $dryRun, callable $callback): mixed
    {
        return $dryRun ? $callback() : $this->userSwitcher->runAsImportUser($callback);
    }

    /**
     * A source usually knows one language. The languages an event should have (sulu_event.import.locales) get a draft with
     * the title of the source right away, so that the editors (or the MCP tools) only have to write the texts and the
     * facts are there in every language. A title that someone changes later is locked like any other field.
     */
    private function withRequiredLocales(EventCandidate $candidate): EventCandidate
    {
        $first = null;
        foreach ($candidate->titles as $title) {
            if ('' !== trim($title)) {
                $first = $title;

                break;
            }
        }
        if (null === $first || [] === $this->settings->requiredLocales) {
            return $candidate;
        }

        $titles = $candidate->titles;
        foreach ($this->settings->requiredLocales as $locale) {
            if ('' === trim($titles[$locale] ?? '')) {
                $titles[$locale] = $first;
            }
        }
        if ($titles === $candidate->titles) {
            return $candidate;
        }

        $copy         = clone $candidate;
        $copy->titles = $titles;

        return $copy;
    }

    private function processCandidate(string $sourceKey, int $priority, EventCandidate $candidate, ImportReport $report, bool $dryRun, \DateTimeImmutable $now): void
    {
        $candidate = $this->withRequiredLocales($candidate);
        $title     = $this->titleOf($candidate);
        if (null === $candidate->startDate || [] === $candidate->getLocales()) {
            $report->add(ImportReport::ACTION_ERROR, $sourceKey, $candidate->externalId, $title, null, 'The source gives no title or no start date.');

            return;
        }

        $matchKey = $candidate->resolveMatchKey();
        $import   = $this->importRepository->findBySourceAndExternalId($sourceKey, $candidate->externalId) ?? $this->findAdopted($sourceKey, $candidate->externalId);
        $event    = null;

        if (null !== $import && EventImport::STATE_REJECTED === $import->getState()) {
            $this->touch($import, $now, $dryRun);
            $report->add(ImportReport::ACTION_REJECTED, $sourceKey, $candidate->externalId, $title, null, $import->getStateReason() ?? 'The event was deleted in the admin and is not imported again.');

            return;
        }

        $verdict = $this->evaluateFilter($sourceKey, $candidate, $now);
        if (!$verdict->passed) {
            $this->recordFiltered($sourceKey, $priority, $candidate, $import, $matchKey, $verdict, $title, $report, $dryRun, $now);

            return;
        }

        $candidate = $this->limitTags($sourceKey, $candidate, $report);

        if (null !== $import) {
            if (!$dryRun && $import->getExternalId() !== $candidate->externalId) {
                $report->addWarning($sourceKey, $candidate->externalId, \sprintf('The source delivers this event under a new id (before: %s), the entry follows it.', $import->getExternalId()));
                $import->setExternalId($candidate->externalId);
            }
            if (!$dryRun && EventImport::STATE_FILTERED === $import->getState()) {
                // it passes the sieve now (the rules changed): it is imported like any other candidate
                $import->setState(EventImport::STATE_ACTIVE);
            }

            $event = $import->getEvent();
            if (null === $event && null !== $import->getLastEventUuid()) {
                // the event is gone and the listener did not see it (deleted by hand in the database, ...)
                if (!$dryRun) {
                    $import->setState(EventImport::STATE_REJECTED, 'The linked event does not exist any more.');
                    $this->touch($import, $now, false);
                }
                $report->add(ImportReport::ACTION_REJECTED, $sourceKey, $candidate->externalId, $title, null, 'The linked event does not exist any more.');

                return;
            }
        }

        if (null === $event && null !== $matchKey) {
            if ([] !== $this->otherSources($sourceKey, $this->importRepository->findRejectedByMatchKey($matchKey))) {
                $report->add(ImportReport::ACTION_REJECTED, $sourceKey, $candidate->externalId, $title, null, \sprintf('An event "%s" was deleted in the admin and is not imported again.', $matchKey));

                return;
            }

            $linked = $this->otherSources($sourceKey, $this->importRepository->findLinkedByMatchKey($matchKey));
            $event  = [] !== $linked ? $linked[0]->getEvent() : null;
        }

        if (null === $import) {
            $import = new EventImport($sourceKey, $candidate->externalId);
        }

        if (null === $event) {
            if ('cancelled' === $candidate->status) {
                $report->add(ImportReport::ACTION_UNCHANGED, $sourceKey, $candidate->externalId, $title, null, 'Cancelled, so it is not created.');

                return;
            }

            $this->create($import, $candidate, $matchKey, $priority, $title, $report, $dryRun, $now);

            return;
        }

        $this->update($import, $event, $candidate, $matchKey, $priority, $title, $report, $dryRun, $now);
    }

    /**
     * The match key joins the same event of different sources. Within one source it does not: a calendar delivers many
     * events of the same series (every date of a weekly meetup has the same key), each is an event of its own.
     *
     * @param EventImport[] $imports
     *
     * @return EventImport[]
     */
    private function otherSources(string $sourceKey, array $imports): array
    {
        return array_values(array_filter($imports, static fn (EventImport $import): bool => $import->getSource() !== $sourceKey));
    }

    /**
     * Asks the sieve; the country of a place that is only given by its id is looked up.
     */
    private function evaluateFilter(string $sourceKey, EventCandidate $candidate, \DateTimeImmutable $now): FilterResult
    {
        $country = null;
        if (null === $candidate->location && null !== $candidate->locationId) {
            $known   = $this->entityManager->find(Location::class, $candidate->locationId);
            $country = $known instanceof Location ? $known->getCountryCode() : null;
        }

        return $this->filter->evaluate($sourceKey, $candidate, $now, $country, $candidate->type ?? $this->settings->defaultType);
    }

    /**
     * A candidate that does not pass the sieve gets no event; its entry says why (state "filtered"). An event that exists
     * is not touched, "sulu:event:import:prune" decides what happens to it.
     */
    private function recordFiltered(string $sourceKey, int $priority, EventCandidate $candidate, ?EventImport $import, ?string $matchKey, FilterResult $verdict, ?string $title, ImportReport $report, bool $dryRun, \DateTimeImmutable $now): void
    {
        $country = $candidate->location?->countryCode;
        $report->addFiltered($sourceKey, $candidate->externalId, $title, null !== $country ? strtoupper($country) : null, $verdict->code, $verdict->reason, $import?->getEvent()?->getUuid());

        if ($dryRun) {
            return;
        }

        $import ??= new EventImport($sourceKey, $candidate->externalId);
        if ($import->getExternalId() !== $candidate->externalId) {
            $import->setExternalId($candidate->externalId);
        }

        $import->setMatchKey($matchKey)
            ->setPriority($priority)
            ->setSourceUrl($candidate->sourceUrl)
            ->setLastSeenAt($now)
            ->setLastError(null)
            ->setState(EventImport::STATE_FILTERED, $verdict->reason);

        $this->importRepository->add($import);
        $this->entityManager->flush();
    }

    /**
     * Only the tags the project allows (sulu_event.import.filters.tags_allowed) go to the event; the others are named in the report.
     */
    private function limitTags(string $sourceKey, EventCandidate $candidate, ImportReport $report): EventCandidate
    {
        [$kept, $dropped] = $this->filter->filterTags($sourceKey, $candidate->tags);
        if ([] === $dropped) {
            return $candidate;
        }

        $report->addWarning($sourceKey, $candidate->externalId, 'Tags that are not on the list of allowed tags were left out: ' . implode(', ', $dropped) . '.');

        $copy       = clone $candidate;
        $copy->tags = array_values($kept);

        return $copy;
    }

    /**
     * The same event twice in one delivery (confs.tech lists a conference under every topic it belongs to) is one
     * candidate: the first one stays, categories and tags of the others are added.
     *
     * @param list<EventCandidate> $candidates
     *
     * @return list<EventCandidate>
     */
    private function mergeDuplicates(array $candidates): array
    {
        $byId = [];
        foreach ($candidates as $candidate) {
            if (!isset($byId[$candidate->externalId])) {
                $byId[$candidate->externalId] = $candidate;

                continue;
            }

            $first      = $byId[$candidate->externalId];
            $categories = array_values(array_unique(array_merge($first->categoryKeys, $candidate->categoryKeys)));
            $tags       = array_values(array_unique(array_merge($first->tags, $candidate->tags)));
            if ($categories !== $first->categoryKeys || $tags !== $first->tags) {
                $first                        = clone $first;
                $first->categoryKeys          = $categories;
                $first->tags                  = $tags;
                $byId[$candidate->externalId] = $first;
            }
        }

        return array_values($byId);
    }

    /**
     * A source that changes the id of an event (a page that gave none and now does, a new url) would get a second event
     * and lose the first one ("vanished"). If a source delivers exactly one candidate for a match key, has exactly one
     * active entry with that key and the id of that entry is not delivered, it is the same event under a new id.
     *
     * @param list<EventCandidate> $candidates
     *
     * @return string[] the old ids that are taken over
     */
    private function findRenamed(string $sourceKey, array $candidates): array
    {
        $this->adopted = array_filter($this->adopted, static fn (string $key): bool => !str_starts_with($key, $sourceKey . "\0"), \ARRAY_FILTER_USE_KEY);

        $delivered = [];
        $byKey     = [];
        foreach ($candidates as $candidate) {
            $delivered[$candidate->externalId] = true;
            $matchKey                          = $candidate->resolveMatchKey();
            if (null !== $matchKey) {
                $byKey[$matchKey][] = $candidate;
            }
        }

        $known   = [];
        $missing = [];
        foreach ($this->importRepository->findBySource($sourceKey) as $import) {
            $known[$import->getExternalId()] = true;
            $matchKey                        = $import->getMatchKey();
            if (null !== $matchKey && EventImport::STATE_ACTIVE === $import->getState() && null !== $import->getEvent() && !isset($delivered[$import->getExternalId()])) {
                $missing[$matchKey][] = $import;
            }
        }

        $taken = [];
        foreach ($missing as $matchKey => $imports) {
            $new = $byKey[$matchKey] ?? [];
            if (1 !== \count($imports) || 1 !== \count($new) || isset($known[$new[0]->externalId]) || null === $imports[0]->getId()) {
                continue;
            }

            $this->adopted[$sourceKey . "\0" . $new[0]->externalId] = $imports[0]->getId();
            $taken[]                                                = $imports[0]->getExternalId();
        }

        return $taken;
    }

    private function findAdopted(string $sourceKey, string $externalId): ?EventImport
    {
        $id = $this->adopted[$sourceKey . "\0" . $externalId] ?? null;

        return null !== $id ? $this->importRepository->find($id) : null;
    }

    private function create(EventImport $import, EventCandidate $candidate, ?string $matchKey, int $priority, ?string $title, ImportReport $report, bool $dryRun, \DateTimeImmutable $now): void
    {
        $location               = $this->resolveLocation($candidate, $dryRun, $report, $import->getSource());
        [$categoryIds, $tagIds] = $this->resolveTaxonomy($candidate, $dryRun, $report, $import->getSource());
        $imageId                = $this->resolveImageId($candidate, $report, $import->getSource(), $location);

        $wanted   = $this->wantedValues($candidate, $location?->getId());
        $event    = new Event();
        $lastData = [];

        if (!$dryRun) {
            $this->entityManager->persist($event);
        }

        foreach ($candidate->getLocales() as $locale) {
            $lastData = $this->createData($candidate, $locale, $wanted, $categoryIds, $tagIds, $imageId);
            if (!$dryRun) {
                $this->contentManager->persist($event, $lastData, ['locale' => $locale, 'stage' => DimensionContentInterface::STAGE_DRAFT]);
            }
        }

        if (!$dryRun) {
            $import->setEvent($event);
            $this->fillImport($import, $candidate, $matchKey, $priority, $wanted, [], $now);
            $this->importRepository->add($import);
            $this->domainEventCollector->collect(new CreatedEvent($event, $lastData));
            $this->entityManager->flush();
        }

        $report->add(ImportReport::ACTION_CREATED, $import->getSource(), $candidate->externalId, $title, $dryRun ? null : $event->getUuid(), 'Created as a draft.');
    }

    private function update(EventImport $import, Event $linkedEvent, EventCandidate $candidate, ?string $matchKey, int $priority, ?string $title, ImportReport $report, bool $dryRun, \DateTimeImmutable $now): void
    {
        $source = $import->getSource();
        $event  = $this->eventRepository->findByUuid($linkedEvent->getUuid()) ?? $linkedEvent;
        $uuid   = $event->getUuid();

        if (EventImport::STATE_VANISHED === $import->getState() && !$dryRun) {
            $import->setState(EventImport::STATE_ACTIVE);
        }

        $isNewLink = null === $import->getEvent();
        if ($isNewLink && !$dryRun) {
            $import->setEvent($event);
        }

        $fingerprint = $this->fingerprint($candidate);
        if (!$isNewLink && $fingerprint === $import->getContentHash() && [] === $import->getLockedFields()) {
            $this->touch($import, $now, $dryRun);
            $report->add(ImportReport::ACTION_UNCHANGED, $source, $candidate->externalId, $title, $uuid);

            return;
        }

        if (!$this->mayWrite($import, $event, $priority)) {
            if (!$dryRun) {
                $this->fillImport($import, $candidate, $matchKey, $priority, $import->getImportedValues(), $import->getLockedFields(), $now, false);
                $this->importRepository->add($import);
                $this->entityManager->flush();
            }
            $report->add(ImportReport::ACTION_UNCHANGED, $source, $candidate->externalId, $title, $uuid, 'Another source with a higher priority maintains this event.');

            return;
        }

        $rows = [];
        foreach ($candidate->getLocales() as $locale) {
            $rows[$locale] = $this->findDraft($event, $locale);
        }
        $base = null;
        foreach ($rows as $row) {
            $base ??= $row;
        }
        $base ??= $this->findAnyDraft($event);
        if (null === $base) {
            throw new \RuntimeException(\sprintf('The event %s has no draft to compare with.', $uuid));
        }

        $location               = $this->resolveLocation($candidate, $dryRun, $report, $source);
        [$categoryIds, $tagIds] = $this->resolveTaxonomy($candidate, $dryRun, $report, $source);
        $imageId                = $this->resolveImageId($candidate, $report, $source, $location);

        $wanted      = $this->wantedValues($candidate, $location?->getId());
        $current     = $this->currentValues(array_keys($wanted), $rows, $base);
        $imported    = $import->getImportedValues();
        $locked      = $import->getLockedFields();
        $changes     = [];
        $newImported = $imported;
        $lockedDiffs = [];

        foreach ($wanted as $field => $value) {
            $exists       = \array_key_exists($field, $current);
            $currentValue = $current[$field] ?? null;

            if ($exists && \array_key_exists($field, $imported) && $imported[$field] !== $currentValue && !\in_array($field, $locked, true)) {
                // someone changed the field in the admin after the last import
                $locked[] = $field;
            }

            if (\in_array($field, $locked, true)) {
                if ($currentValue !== $value) {
                    $lockedDiffs[] = $field;
                }

                continue;
            }

            if (!$exists || $currentValue !== $value) {
                $changes[$field] = $value;
            } else {
                $newImported[$field] = $value;
            }
        }

        $fill = $this->fillData($candidate, $rows, $categoryIds, $tagIds, $imageId);

        if ([] === $changes && [] === $fill) {
            if (!$dryRun) {
                $this->fillImport($import, $candidate, $matchKey, $priority, $newImported, $locked, $now);
                $this->importRepository->add($import);
                $this->entityManager->flush();
            }
            $report->add(ImportReport::ACTION_UNCHANGED, $source, $candidate->externalId, $title, $uuid, [] !== $lockedDiffs ? 'Differs from the source, but the fields are locked.' : '', $lockedDiffs);

            return;
        }

        if (!$dryRun) {
            $this->writeChanges($event, $candidate, $rows, $current, $changes, $fill, $title);
            foreach ($changes as $field => $value) {
                $newImported[$field] = $value;
            }
            $this->fillImport($import, $candidate, $matchKey, $priority, $newImported, $locked, $now);
            $this->importRepository->add($import);
            $this->entityManager->flush();
        }

        $report->add(
            ImportReport::ACTION_UPDATED,
            $source,
            $candidate->externalId,
            $title,
            $uuid,
            'Changed in the draft: ' . implode(', ', array_merge(array_keys($changes), array_map(static fn (string $field): string => $field . ' (filled in)', array_values(array_unique(array_merge([], ...array_values(array_map('array_keys', $fill)))))))) . '.',
            $lockedDiffs
        );
    }

    /**
     * @param array<string, EventDimensionContent|null> $rows
     * @param array<string, mixed>                      $current
     * @param array<string, mixed>                      $changes
     * @param array<string, array<string, mixed>>       $fill
     */
    private function writeChanges(Event $event, EventCandidate $candidate, array $rows, array $current, array $changes, array $fill, ?string $title): void
    {
        $final = [];
        foreach (self::UNLOCALIZED_FIELDS as $field) {
            if (\array_key_exists($field, $changes)) {
                $final[$field] = $changes[$field];
            } elseif (\array_key_exists($field, $current)) {
                $final[$field] = $current[$field];
            }
        }

        $onlyLiveFields = [] === $fill && [] === array_diff(array_keys($changes), self::LIVE_FIELDS);
        $lastData       = [];
        $inSync         = [];

        foreach ($candidate->getLocales() as $locale) {
            $row      = $rows[$locale];
            $wasPlace = $row?->getWorkflowPlace();
            $data     = [];

            foreach ($final as $field => $value) {
                $data[$field] = 'startDate' === $field || 'endDate' === $field ? $this->toDataDate($value) : $value;
            }

            if (null === $row) {
                // a language the event does not have yet
                $data += $this->createData($candidate, $locale, $this->wantedValues($candidate, $final['locationId'] ?? null), $fill[$locale]['excerptCategories'] ?? [], $fill[$locale]['excerptTags'] ?? [], $fill[$locale]['image'] ?? null);
            } else {
                if (\array_key_exists('title:' . $locale, $changes)) {
                    $data['title'] = $changes['title:' . $locale];
                }
                if (\array_key_exists('priceInfo:' . $locale, $changes)) {
                    $data['priceInfo'] = $changes['priceInfo:' . $locale];
                }
                $data = array_replace($data, $fill[$locale] ?? []);
            }

            $dimensionContent = $this->contentManager->persist($event, $data, ['locale' => $locale, 'stage' => DimensionContentInterface::STAGE_DRAFT]);
            if ($dimensionContent instanceof WorkflowInterface && WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $dimensionContent->getWorkflowPlace()) {
                $this->contentWorkflow->apply($event, ['locale' => $locale], WorkflowInterface::WORKFLOW_TRANSITION_CREATE_DRAFT);
            }

            $inSync[$locale] = WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $wasPlace;
            $lastData        = $data;
        }

        $this->domainEventCollector->collect(new ModifiedEvent($event, $lastData + ['title' => $title]));
        $this->entityManager->flush();

        if ($this->settings->autoUpdatePublishedDates && $onlyLiveFields) {
            // only a date or status change, and the draft had nothing else waiting: it may go live
            $published = false;
            foreach ($inSync as $locale => $wasInSync) {
                if ($wasInSync) {
                    $this->contentWorkflow->apply($event, ['locale' => $locale], WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH);
                    $published = true;
                }
            }

            if ($published) {
                $this->domainEventCollector->collect(new PublishedEvent($event, $lastData + ['title' => $title]));
                $this->entityManager->flush();
            }
        }
    }

    /**
     * @param array<string, mixed> $wanted
     * @param int[]                $categoryIds
     * @param int[]                $tagIds
     *
     * @return array<string, mixed>
     */
    private function createData(EventCandidate $candidate, string $locale, array $wanted, array $categoryIds, array $tagIds, ?int $imageId = null): array
    {
        $title = $candidate->titles[$locale];
        $data  = [
            'template' => $this->settings->template,
            'title'    => $title,
            'url'      => $this->urlGenerator->generate($title, $candidate->getYear(), $locale),
            'type'     => $wanted['type'] ?? $this->settings->defaultType,
        ];

        foreach (self::UNLOCALIZED_FIELDS as $field) {
            if ('type' !== $field && \array_key_exists($field, $wanted)) {
                $data[$field] = 'startDate' === $field || 'endDate' === $field ? $this->toDataDate($wanted[$field]) : $wanted[$field];
            }
        }

        if (isset($wanted['priceInfo:' . $locale])) {
            $data['priceInfo'] = $wanted['priceInfo:' . $locale];
        }
        foreach (['subtitle' => $candidate->subtitles, 'summary' => $candidate->summaries, 'text' => $candidate->texts] as $key => $texts) {
            if (isset($texts[$locale]) && '' !== trim($texts[$locale])) {
                $data[$key] = $texts[$locale];
            }
        }
        if ([] !== $categoryIds) {
            $data['excerptCategories'] = $categoryIds;
        }
        if ([] !== $tagIds) {
            $data['excerptTags'] = $tagIds;
        }
        if (null !== $imageId) {
            // the form of the admin: the template stores {id}, a bare id is not resolved on the website (no image on the cards)
            $data['image']   = ['id' => $imageId];
            $data['excerpt'] = ['image' => ['id' => $imageId]];
        }
        if (null !== $this->settings->webspace) {
            $data['mainWebspace'] = $this->settings->webspace;
        }

        return $data;
    }

    /**
     * What is filled in on an existing event: texts, categories and tags that are still empty. Never replaced.
     *
     * @param array<string, EventDimensionContent|null> $rows
     * @param int[]                                     $categoryIds
     * @param int[]                                     $tagIds
     *
     * @return array<string, array<string, mixed>> locale => data
     */
    private function fillData(EventCandidate $candidate, array $rows, array $categoryIds, array $tagIds, ?int $imageId = null): array
    {
        $fill = [];
        foreach ($rows as $locale => $row) {
            if (null === $row) {
                $fill[$locale] = [];
                if (null !== $imageId) {
                    $fill[$locale]['image'] = ['id' => $imageId];
                }
                if ([] !== $categoryIds) {
                    $fill[$locale]['excerptCategories'] = $categoryIds;
                }
                if ([] !== $tagIds) {
                    $fill[$locale]['excerptTags'] = $tagIds;
                }

                continue;
            }

            $data = [];
            foreach (['subtitle' => [$candidate->subtitles, $row->getSubtitle()], 'summary' => [$candidate->summaries, $row->getSummary()], 'text' => [$candidate->texts, $row->getText()]] as $key => [$texts, $existing]) {
                if (isset($texts[$locale]) && '' !== trim($texts[$locale]) && (null === $existing || '' === trim(strip_tags($existing)))) {
                    $data[$key] = $texts[$locale];
                }
            }
            if ([] !== $categoryIds && [] === $row->getExcerptCategories()) {
                $data['excerptCategories'] = $categoryIds;
            }
            if ([] !== $tagIds && [] === $row->getExcerptTags()) {
                $data['excerptTags'] = $tagIds;
            }
            if (null !== $imageId && null === $row->getImage()) {
                $data['image'] = ['id' => $imageId];
            }
            if (null !== $imageId && null === $row->getExcerptImage()) {
                // the excerpt data is replaced as a whole, so the other entries are carried along
                $data['excerpt'] = array_replace($row->getExcerptData(), ['image' => ['id' => $imageId]]);
            }

            if ([] !== $data) {
                $fill[$locale] = $data;
            }
        }

        return array_filter($fill, static fn (array $data): bool => [] !== $data);
    }

    /**
     * The values the source wants, in the form they are compared in. Fields the source says nothing about are left
     * out, so they stay as the editors have them.
     *
     * @return array<string, mixed>
     */
    private function wantedValues(EventCandidate $candidate, ?int $locationId): array
    {
        $wanted = [];
        foreach ($candidate->getLocales() as $locale) {
            $wanted['title:' . $locale] = trim($candidate->titles[$locale]);
        }
        foreach ($candidate->priceInfos as $locale => $priceInfo) {
            if ('' !== trim($priceInfo)) {
                $wanted['priceInfo:' . $locale] = trim($priceInfo);
            }
        }

        // an all-day event covers whole days, as the draft stores it
        $start               = $candidate->allDay ? $candidate->startDate?->setTime(0, 0, 0) : $candidate->startDate;
        $end                 = $candidate->allDay ? $candidate->endDate?->setTime(23, 59, 59) : $candidate->endDate;
        $wanted['startDate'] = $start?->format('Y-m-d H:i:s');
        if (null !== $end) {
            $wanted['endDate'] = $end->format('Y-m-d H:i:s');
        }
        $wanted['allDay']         = $candidate->allDay;
        $wanted['status']         = $candidate->status;
        $wanted['attendanceMode'] = $candidate->attendanceMode;

        $optional = [
            'timezone'        => $candidate->timezone,
            'type'            => $candidate->type,
            'locationId'      => $locationId,
            'onlineUrl'       => $candidate->onlineUrl,
            'registrationUrl' => $candidate->registrationUrl,
            'organizerName'   => $candidate->organizerName,
            'organizerUrl'    => $candidate->organizerUrl,
            'isFree'          => $candidate->isFree,
        ];
        foreach ($optional as $field => $value) {
            if (\is_string($value)) {
                $value = trim($value);
            }
            if (null !== $value && '' !== $value) {
                $wanted[$field] = $value;
            }
        }

        $languages = array_values(array_unique(array_map('strtolower', $candidate->languages)));
        if ([] !== $languages) {
            sort($languages);
            $wanted['languages'] = $languages;
        }

        return $wanted;
    }

    /**
     * The same fields as read from the draft rows of the event. A field of a locale the event does not have is missing.
     *
     * @param string[]                                  $fields
     * @param array<string, EventDimensionContent|null> $rows
     *
     * @return array<string, mixed>
     */
    private function currentValues(array $fields, array $rows, EventDimensionContent $base): array
    {
        $current = [];
        foreach ($fields as $field) {
            if (str_contains($field, ':')) {
                [$name, $locale] = explode(':', $field, 2);
                $row             = $rows[$locale] ?? null;
                if (null === $row) {
                    continue;
                }

                $value           = 'title' === $name ? $row->getTitle() : $row->getPriceInfo();
                $current[$field] = null !== $value ? trim($value) : null;

                continue;
            }

            $current[$field] = match ($field) {
                'startDate'       => $base->getStartDate()?->format('Y-m-d H:i:s'),
                'endDate'         => $base->getEndDate()?->format('Y-m-d H:i:s'),
                'allDay'          => $base->getAllDay(),
                'timezone'        => $this->emptyToNull($base->getTimezone()),
                'status'          => $base->getStatus(),
                'attendanceMode'  => $base->getAttendanceMode(),
                'type'            => $this->emptyToNull($base->getType()),
                'locationId'      => $base->getLocation()?->getId(),
                'onlineUrl'       => $this->emptyToNull($base->getOnlineUrl()),
                'registrationUrl' => $this->emptyToNull($base->getRegistrationUrl()),
                'organizerName'   => $this->emptyToNull($base->getOrganizerName()),
                'organizerUrl'    => $this->emptyToNull($base->getOrganizerUrl()),
                'isFree'          => $base->getIsFree(),
                'languages'       => $this->sortedLanguages($base->getLanguages()),
                default           => null,
            };
        }

        return $current;
    }

    /**
     * @param string[]|null $languages
     *
     * @return string[]
     */
    private function sortedLanguages(?array $languages): array
    {
        $languages = array_values(array_unique(array_map('strtolower', $languages ?? [])));
        sort($languages);

        return $languages;
    }

    private function emptyToNull(?string $value): ?string
    {
        $value = null !== $value ? trim($value) : null;

        return '' === $value ? null : $value;
    }

    private function toDataDate(mixed $value): ?string
    {
        return \is_string($value) && '' !== $value ? str_replace(' ', 'T', $value) : null;
    }

    /**
     * May this source write to the event? Not if an active entry of another source with a higher priority (or the same
     * priority and the older link) maintains it.
     */
    private function mayWrite(EventImport $import, Event $event, int $priority): bool
    {
        foreach ($this->importRepository->findByEventUuid($event->getUuid()) as $other) {
            if ($other === $import || EventImport::STATE_ACTIVE !== $other->getState()) {
                continue;
            }

            if ($other->getPriority() > $priority) {
                return false;
            }

            if ($other->getPriority() === $priority && null === $import->getId()) {
                // same rank: the one that was there first keeps the event
                return false;
            }

            if ($other->getPriority() === $priority && null !== $other->getId() && $other->getId() < $import->getId()) {
                return false;
            }
        }

        return true;
    }

    private function resolveLocation(EventCandidate $candidate, bool $dryRun, ImportReport $report, string $source): ?Location
    {
        if (null !== $candidate->locationId) {
            $known = $this->entityManager->find(Location::class, $candidate->locationId);
            if ($known instanceof Location) {
                return $known;
            }

            $report->addWarning($source, $candidate->externalId, \sprintf('The location with the id %d does not exist.', $candidate->locationId));
        }

        if (null === $candidate->location || 'online' === $candidate->attendanceMode) {
            return null;
        }

        $resolved = $this->locationResolver->resolve($candidate->location, !$dryRun, $candidate->timezone);
        if ($resolved->created) {
            if (!$dryRun) {
                // a new place has no id before it is written; the event gets the id, so it must exist now (waiting for a later
                // query to write it left events without a place as soon as the categories and tags were known already)
                $this->entityManager->flush();
            }
            $this->giveNewLocationItsImage($resolved->location, $dryRun, $report, $source, $candidate->externalId);
            $report->addNewLocation($resolved->location->getName() ?? $candidate->location->name, $resolved->location->getTimezone(), $resolved->timezoneCertain);
        }

        return $resolved->location;
    }

    /**
     * A source does not know a photo of the place: a new place gets the image of its own (sulu_event.import.location_image),
     * so that no place is without one. The editors replace it with a real photo.
     */
    private function giveNewLocationItsImage(Location $location, bool $dryRun, ImportReport $report, string $source, string $externalId): void
    {
        $reference = $this->settings->locationImage;
        if (null === $reference || '' === (string) $reference) {
            return;
        }

        $id = $this->mediaResolver->resolve($reference);
        if (null === $id) {
            $report->addWarning($source, $externalId, \sprintf('The image "%s" for new places does not exist.', $reference));

            return;
        }

        if (!$dryRun) {
            $location->setImage($this->entityManager->getReference(Media::class, $id));
        }
    }

    /**
     * The image of our own for a new event: a photo of the series, else the photo of the place, else the placeholder of its
     * first category (see EventImageChooser). Images of a source are never copied (they belong to the source).
     */
    private function resolveImageId(EventCandidate $candidate, ImportReport $report, string $source, ?Location $location = null): ?int
    {
        $reference = $this->pickImageReference($candidate, $location);
        if (null === $reference) {
            return null;
        }

        $id = $this->mediaResolver->resolve($reference);
        if (null === $id) {
            $report->addWarning($source, $candidate->externalId, \sprintf('The image "%s" does not exist, the event gets no image.', $reference));
        }

        return $id;
    }

    /**
     * @return int|string|null id or file name of the image
     */
    private function pickImageReference(EventCandidate $candidate, ?Location $location): int|string|null
    {
        if (null !== $candidate->imageMediaId) {
            return $candidate->imageMediaId;
        }

        return $this->imageChooser->reference($candidate->series, $location, $candidate->categoryKeys);
    }

    /**
     * @return array{0: int[], 1: int[]} ids of categories and tags
     */
    private function resolveTaxonomy(EventCandidate $candidate, bool $dryRun, ImportReport $report, string $source): array
    {
        [$categories, $unknown] = $this->categoryResolver->resolve($candidate->categoryKeys);
        if ([] !== $unknown) {
            $report->addWarning($source, $candidate->externalId, 'Unknown category keys: ' . implode(', ', $unknown));
        }

        $tags = $this->tagResolver->resolve($candidate->tags, !$dryRun);
        if (!$dryRun && [] !== $tags) {
            // new tags need their id for the event
            $this->entityManager->flush();
        }

        return [
            array_values(array_filter(array_map(static fn ($category): ?int => $category->getId(), $categories))),
            array_values(array_filter(array_map(static fn ($tag): ?int => $tag->getId(), $tags))),
        ];
    }

    /**
     * Writes the bookkeeping of the entry after a run.
     *
     * @param array<string, mixed> $values what the import wrote (and what the next run compares with)
     * @param string[]             $locked
     */
    private function fillImport(EventImport $import, EventCandidate $candidate, ?string $matchKey, int $priority, array $values, array $locked, \DateTimeImmutable $now, bool $synced = true): void
    {
        $values['_title'] = $this->titleOf($candidate);
        if (null !== $candidate->sourceDescription) {
            $values['_sourceDescription'] = $candidate->sourceDescription;
        } else {
            unset($values['_sourceDescription']);
        }

        $import->setMatchKey($matchKey)
            ->setPriority($priority)
            ->setSourceUrl($candidate->sourceUrl)
            ->setImportedValues($values)
            ->setLockedFields($locked)
            ->setContentHash($synced ? $this->fingerprint($candidate) : null)
            ->setLastSeenAt($now)
            ->setLastError(null)
            ->setState(EventImport::STATE_VANISHED === $import->getState() ? EventImport::STATE_ACTIVE : $import->getState());

        if ($synced) {
            $import->setLastSyncedAt($now);
        }
    }

    private function touch(EventImport $import, \DateTimeImmutable $now, bool $dryRun): void
    {
        if (!$dryRun) {
            $import->setLastSeenAt($now);
            $this->entityManager->flush();
        }
    }

    /**
     * @param array<string, true> $delivered
     */
    private function markVanished(string $sourceKey, array $delivered, ImportReport $report, bool $dryRun): void
    {
        $today = ($this->now)()->format('Y-m-d');

        foreach ($this->importRepository->findBySource($sourceKey) as $import) {
            if (EventImport::STATE_ACTIVE !== $import->getState() || null === $import->getEvent() || isset($delivered[$import->getExternalId()])) {
                continue;
            }

            $values = $import->getImportedValues();
            $start  = \is_string($values['startDate'] ?? null) ? mb_substr($values['startDate'], 0, 10) : null;
            $end    = \is_string($values['endDate'] ?? null) ? mb_substr($values['endDate'], 0, 10) : $start;
            if (null === $start || (string) $end < $today) {
                // events in the past are not expected from the source any more
                continue;
            }

            if (!$dryRun) {
                $import->setState(EventImport::STATE_VANISHED);
            }

            $report->add(ImportReport::ACTION_VANISHED, $sourceKey, $import->getExternalId(), \is_string($values['_title'] ?? null) ? $values['_title'] : null, $import->getEvent()->getUuid(), 'The source does not deliver the event any more. The event stays.');
        }

        if (!$dryRun) {
            $this->entityManager->flush();
        }
    }

    private function findDraft(Event $event, string $locale): ?EventDimensionContent
    {
        foreach ($event->getDimensionContents() as $dimensionContent) {
            if ($dimensionContent instanceof EventDimensionContent
                && DimensionContentInterface::STAGE_DRAFT === $dimensionContent->getStage()
                && $locale                                === $dimensionContent->getLocale()
            ) {
                return $dimensionContent;
            }
        }

        return null;
    }

    private function findAnyDraft(Event $event): ?EventDimensionContent
    {
        foreach ($event->getDimensionContents() as $dimensionContent) {
            if ($dimensionContent instanceof EventDimensionContent
                && DimensionContentInterface::STAGE_DRAFT === $dimensionContent->getStage()
                && null !== $dimensionContent->getLocale()
            ) {
                return $dimensionContent;
            }
        }

        return null;
    }

    /**
     * Identifies the content of a candidate; the same fingerprint as last time means nothing changed in the source.
     */
    private function fingerprint(EventCandidate $candidate): string
    {
        $data = get_object_vars($candidate);
        foreach (['startDate', 'endDate'] as $field) {
            $data[$field] = $candidate->{$field}?->format('Y-m-d H:i:s T');
        }
        $data['location'] = null !== $candidate->location ? get_object_vars($candidate->location) : null;
        // a new image setting must reach events that are otherwise unchanged
        $reference      = $this->pickImageReference($candidate, null);
        $data['_image'] = null !== $reference ? $this->mediaResolver->resolve($reference) : null;

        return sha1((string) json_encode($data, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PARTIAL_OUTPUT_ON_ERROR));
    }

    private function titleOf(EventCandidate $candidate): ?string
    {
        foreach ($candidate->getLocales() as $locale) {
            return $candidate->titles[$locale];
        }

        return null;
    }
}
