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
class EventGetTool
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly ContentManagerInterface $contentManager,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    #[McpTool(
        name: 'sulu_event_get',
        title: 'Get Event',
        description: 'Get one event (draft) by UUID with all fields of its template: title, subtitle, type, start and end date, location id, summary, text, details, images, contact data and workflow state.',
    )]
    #[RequiresPermission(requirements: [
        new PermissionRequirement(Event::SECURITY_CONTEXT, PermissionTypes::VIEW),
    ])]
    public function getEvent(string $uuid, string $locale): array
    {
        try {
            $event = $this->eventRepository->findByUuid($uuid);
            if (null === $event) {
                return ['error' => \sprintf('Event "%s" not found.', $uuid)];
            }

            $dimensionContent = $this->contentManager->resolve($event, [
                'locale' => $locale,
                'stage' => DimensionContentInterface::STAGE_DRAFT,
            ]);

            return ['uuid' => $event->getUuid()] + $this->contentManager->normalize($dimensionContent);
        } catch (\Throwable $e) {
            return ['error' => \sprintf('Failed to get event: %s', $e->getMessage())];
        }
    }
}
