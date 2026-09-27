<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Mcp\Tool;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Mcp\Capability\Attribute\McpTool;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Mcp\Domain\Security\PermissionRequirement;
use Sulu\Mcp\Domain\Security\RequiresPermission;

/**
 * @internal
 */
class EventListTool
{
    private const SUMMARY_FIELDS = [
        'title', 'subtitle', 'template', 'url', 'type', 'startDate', 'endDate', 'locationId',
        'locale', 'stage', 'published', 'publishedState', 'workflowPlace', 'availableLocales', 'ghostLocale',
    ];

    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly ContentManagerInterface $contentManager,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    #[McpTool(
        name: 'sulu_event_list',
        title: 'List Events',
        description: 'List events of one locale (drafts). Returns lightweight summaries (title, type, start and end date, location id, workflow state). Use sulu_event_get with a UUID for the full content. Results are paginated with "page" and "limit".',
    )]
    #[RequiresPermission(requirements: [
        new PermissionRequirement(Event::SECURITY_CONTEXT, PermissionTypes::VIEW),
    ])]
    public function listEvents(string $locale, int $page = 1, int $limit = 20): array
    {
        try {
            $events = $this->eventRepository->findAllByLocale($locale, DimensionContentInterface::STAGE_DRAFT);
            $total = \count($events);
            $events = \array_slice($events, max(0, ($page - 1) * $limit), $limit);

            $results = [];
            foreach ($events as $event) {
                $dimensionContent = $this->contentManager->resolve($event, [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_DRAFT,
                ]);
                $normalized = $this->contentManager->normalize($dimensionContent);

                $summary = [];
                foreach (self::SUMMARY_FIELDS as $field) {
                    if (\array_key_exists($field, $normalized)) {
                        $summary[$field] = $normalized[$field];
                    }
                }

                $results[] = ['uuid' => $event->getUuid(), 'data' => $summary];
            }

            return ['events' => $results, 'total' => $total, 'page' => $page, 'limit' => $limit];
        } catch (\Throwable $e) {
            return ['error' => \sprintf('Failed to list events: %s', $e->getMessage())];
        }
    }
}
