<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Mcp\Tool;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Domain\Event\Event\ModifiedEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Mcp\Capability\Attribute\McpTool;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Sulu\Mcp\Domain\Security\PermissionRequirement;
use Sulu\Mcp\Domain\Security\RequiresPermission;

/**
 * @internal
 */
class EventUpdateTool
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentManagerInterface $contentManager,
        private readonly ContentWorkflowInterface $contentWorkflow,
        private readonly DomainEventCollectorInterface $domainEventCollector,
    ) {
    }

    /**
     * @param array<string, mixed>|null $content
     *
     * @return array<string, mixed>
     */
    #[McpTool(
        name: 'sulu_event_update',
        title: 'Update Event',
        description: 'Change fields of an event (draft). Only the fields you pass in "content" are changed, for example {"summary": "...", "startDate": "2026-11-06T09:00:00"}; "title" is a separate parameter. A published event becomes a draft again and must be published again in the admin.',
    )]
    #[RequiresPermission(requirements: [
        new PermissionRequirement(Event::SECURITY_CONTEXT, PermissionTypes::EDIT),
    ])]
    public function updateEvent(string $uuid, string $locale, ?string $title = null, ?array $content = null): array
    {
        try {
            $event = $this->eventRepository->findByUuid($uuid);
            if (null === $event) {
                return ['error' => \sprintf('Event "%s" not found.', $uuid)];
            }

            $attributes = ['locale' => $locale, 'stage' => DimensionContentInterface::STAGE_DRAFT];

            // the content manager takes the data as a whole, so start from the current content
            $current = $this->contentManager->normalize($this->contentManager->resolve($event, $attributes));
            $data = ($content ?? []) + (null !== $title ? ['title' => $title] : []) + $current;

            $dimensionContent = $this->contentManager->persist($event, $data, $attributes);

            if (WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $dimensionContent->getWorkflowPlace()) {
                $this->contentWorkflow->apply($event, ['locale' => $locale], WorkflowInterface::WORKFLOW_TRANSITION_CREATE_DRAFT);
            }

            $this->domainEventCollector->collect(new ModifiedEvent($event, $data));
            $this->entityManager->flush();

            return ['success' => true, 'uuid' => $uuid, 'locale' => $locale];
        } catch (\Throwable $e) {
            return ['error' => \sprintf('Failed to update event: %s', $e->getMessage())];
        }
    }
}
