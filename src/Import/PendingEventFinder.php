<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\EventImport;
use Manuxi\SuluEventBundle\Repository\EventImportRepository;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;

/**
 * Finds the imported events that still need work before they are worth publishing: a text or summary is missing, a
 * language is missing, there is no topic or no image. The result carries what the source said about the event (link
 * and description) as an aid for whoever does the research.
 */
class PendingEventFinder
{
    public const MISSING_TEXT     = 'text';
    public const MISSING_SUMMARY  = 'summary';
    public const MISSING_LOCALE   = 'locale';
    public const MISSING_CATEGORY = 'categories';
    public const MISSING_IMAGE    = 'image';

    public function __construct(
        private readonly EventImportRepository $importRepository,
        private readonly EventRepository $eventRepository,
        private readonly ImportSettings $settings,
    ) {
    }

    /**
     * @param string[]|null $locales locales an event should have a text in; null = the configured ones, otherwise those the event has
     *
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    public function find(?array $locales = null, int $limit = 20, int $offset = 0): array
    {
        $locales = $locales ?? ([] !== $this->settings->requiredLocales ? $this->settings->requiredLocales : null);

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
            if (null === $event) {
                continue;
            }

            $item = $this->check($event, $imports, $locales);
            if ([] !== $item['missing']) {
                $items[] = $item;
            }
        }

        usort($items, static fn (array $a, array $b): int => ($a['startDate'] ?? '9999') <=> ($b['startDate'] ?? '9999'));

        return [
            'items' => \array_slice($items, max(0, $offset), max(1, $limit)),
            'total' => \count($items),
        ];
    }

    /**
     * @param list<EventImport> $imports the entries of the event, the one with the highest priority first
     * @param string[]|null     $locales
     *
     * @return array<string, mixed>
     */
    private function check(Event $event, array $imports, ?array $locales): array
    {
        $rows = [];
        foreach ($event->getDimensionContents() as $row) {
            if ($row instanceof EventDimensionContent && DimensionContentInterface::STAGE_DRAFT === $row->getStage() && null !== $row->getLocale()) {
                $rows[$row->getLocale()] = $row;
            }
        }

        $missing = [];
        foreach ($locales ?? array_keys($rows) as $locale) {
            $row = $rows[$locale] ?? null;
            if (null === $row) {
                $missing[] = self::MISSING_LOCALE . ':' . $locale;

                continue;
            }

            if ($this->isEmpty($row->getText())) {
                $missing[] = self::MISSING_TEXT . ':' . $locale;
            }
            if ($this->isEmpty($row->getSummary())) {
                $missing[] = self::MISSING_SUMMARY . ':' . $locale;
            }
        }

        $categories = false;
        $image      = false;
        $published  = false;
        foreach ($rows as $row) {
            $categories = $categories || []   !== $row->getExcerptCategories();
            $image      = $image      || null !== $row->getImage() || null !== $row->getExcerptImage();
            $published  = $published  || \in_array($row->getWorkflowPlace(), [
                WorkflowInterface::WORKFLOW_PLACE_PUBLISHED,
                WorkflowInterface::WORKFLOW_PLACE_DRAFT,
                WorkflowInterface::WORKFLOW_PLACE_REVIEW_DRAFT,
            ], true);
        }

        if ([] !== $rows) {
            if (!$categories) {
                $missing[] = self::MISSING_CATEGORY;
            }
            if (!$image) {
                $missing[] = self::MISSING_IMAGE;
            }
        }

        $first  = $rows[array_key_first($rows) ?? ''] ?? null;
        $import = $imports[0];
        $values = $import->getImportedValues();

        return [
            'uuid'              => $event->getUuid(),
            'title'             => $first?->getTitle() ?? (\is_string($values['_title'] ?? null) ? $values['_title'] : null),
            'startDate'         => $first?->getStartDate()?->format('Y-m-d H:i:s'),
            'source'            => $import->getSource(),
            'sources'           => array_map(static fn (EventImport $entry): string => $entry->getSource(), $imports),
            'state'             => $import->getState(),
            'published'         => $published,
            'locales'           => array_keys($rows),
            'missing'           => $missing,
            'sourceUrl'         => $import->getSourceUrl(),
            'sourceDescription' => \is_string($values['_sourceDescription'] ?? null) ? $values['_sourceDescription'] : null,
        ];
    }

    private function isEmpty(?string $value): bool
    {
        return null === $value || '' === trim(strip_tags($value));
    }
}
