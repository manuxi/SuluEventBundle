<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Mcp\Tool;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Import\PendingEventFinder;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Mcp\Domain\Security\PermissionRequirement;
use Sulu\Mcp\Domain\Security\RequiresPermission;

/**
 * @internal
 */
class EventImportPendingTool
{
    public function __construct(private readonly PendingEventFinder $finder)
    {
    }

    /**
     * @param string[]|null $locales
     *
     * @return array<string, mixed>
     */
    #[McpTool(
        name: 'sulu_event_import_pending',
        title: 'Imported events that need work',
        description: 'List the events created by the import that still lack something, with the reason: "text:de" / "summary:en" (empty), "locale:en" (no draft in that language), "categories" (no topic) and "image" (no image). Each item has the uuid, title, start date, source, "sourceUrl" (the page of the event) and "sourceDescription" (what the source says about it, only as research aid - write your own text). "published" tells whether the event is already live. "locales" limits the languages that are checked (default: the configured ones, otherwise the languages the event has). "autoPublish" (only when sulu_event.import.auto_publish is on and covers the event) says what the event still lacks for the automatic publishing: "state" is "ready" (it will be published by the next run), "waiting" (see "missing": "title:de", "subtitle:en", "summary:de", "text:en", "locale:en", "categories", "image", "place") or "skipped" (see "note", for example fields locked by hand); complete exactly what is listed there. Events that the sieve left out ("filtered") or that were rejected (sulu_event_import_reject) are not listed. Newest work first by start date; paginated with "page" and "limit". Use sulu_event_get / sulu_event_update (or sulu_event_import_upsert) to complete an event; publishing is done by the automatic publishing or in the admin.',
    )]
    #[RequiresPermission(requirements: [
        new PermissionRequirement(Event::SECURITY_CONTEXT, PermissionTypes::VIEW),
    ])]
    public function pending(
        #[Schema(type: 'array', description: 'Locales to check, e.g. ["de", "en"]', items: ['type' => 'string'])]
        ?array $locales = null,
        int $page = 1,
        int $limit = 20,
    ): array {
        try {
            $page  = max(1, $page);
            $limit = min(100, max(1, $limit));
            $found = $this->finder->find($locales, $limit, ($page - 1) * $limit);

            return ['events' => $found['items'], 'total' => $found['total'], 'page' => $page, 'limit' => $limit];
        } catch (\Throwable $e) {
            return ['error' => \sprintf('Failed to list the imported events: %s', $e->getMessage())];
        }
    }
}
