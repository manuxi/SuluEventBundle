<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Mcp\Tool;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Domain\Event\Event\CreatedEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Mcp\Capability\Attribute\McpTool;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Mcp\Domain\Security\PermissionRequirement;
use Sulu\Mcp\Domain\Security\RequiresPermission;

/**
 * @internal
 */
class EventCreateTool
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentManagerInterface $contentManager,
        private readonly DomainEventCollectorInterface $domainEventCollector,
    ) {
    }

    /**
     * @param array<string, mixed>|null $content
     *
     * @return array<string, mixed>
     */
    #[McpTool(
        name: 'sulu_event_create',
        title: 'Create Event',
        description: 'Create an event as a draft (publish it in the admin). Pass the template ("event", "event_basic" or "event_detailed"), the title and the template fields in "content", for example {"url": "/events/my-event", "type": "conference", "startDate": "2026-11-05T09:00:00", "endDate": "2026-11-05T17:00:00", "locationId": 1, "summary": "...", "text": "<p>...</p>", "image": {"id": 12}, "images": {"ids": [12, 13]}, "email": "a@b.de"}. "url" (a path) and "locationId" (see sulu_location_list) are required in the default template. Media fields take media ids from sulu_media_list.',
    )]
    #[RequiresPermission(requirements: [
        new PermissionRequirement(Event::SECURITY_CONTEXT, PermissionTypes::ADD),
    ])]
    public function createEvent(string $locale, string $title, string $template = 'event', ?array $content = null): array
    {
        try {
            $data = ['template' => $template, 'title' => $title] + ($content ?? []);

            $event = new Event();
            $this->entityManager->persist($event);
            $this->contentManager->persist($event, $data, [
                'locale' => $locale,
                'stage' => DimensionContentInterface::STAGE_DRAFT,
            ]);

            $this->domainEventCollector->collect(new CreatedEvent($event, $data));
            $this->entityManager->flush();

            return ['success' => true, 'uuid' => $event->getUuid(), 'title' => $title, 'locale' => $locale, 'workflowPlace' => 'draft'];
        } catch (\Throwable $e) {
            return ['error' => \sprintf('Failed to create event: %s', $e->getMessage())];
        }
    }
}
