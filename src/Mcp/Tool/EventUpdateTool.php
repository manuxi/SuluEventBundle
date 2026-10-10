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
use Sulu\Content\Domain\Model\TemplateInterface;
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
        description: 'Change fields of an event (draft). Only the fields you pass in "content" are changed, for example {"summary": "...", "startDate": "2026-11-06T09:00:00"}; "title" is a separate parameter. A published event becomes a draft again and must be published again in the admin. Besides the fields of sulu_event_create (start/end date, "locationId", ...) you can pass: Participation and time: "allDay" (true: whole days, times are set to 00:00:00 and 23:59:59), "timezone" (IANA name such as "America/Los_Angeles" for the time of day in startDate/endDate; leave it out to use the zone of the location or of the website), "status" (scheduled, postponed, rescheduled, cancelled, moved_online), "attendanceMode" (offline, online, mixed), "onlineUrl" (stream or meeting link), "registrationUrl" (tickets or registration), "isFree" (true/false), "priceInfo" (short text per locale, e.g. "from 99 EUR"), "organizerName", "organizerUrl", "languages" (e.g. ["de", "en"]). Urls must start with http:// or https://. Topics and image for cards: "excerptCategories" (category ids from sulu_category_list), "excerptTags" (tag ids from sulu_tag_list or sulu_tag_create, not names; unknown ids are ignored) and "excerpt": {"image": {"id": 12}, "title": "...", "description": "..."}.',
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

            // The data mappers only touch the fields that are part of the data, so pass just those. The template key
            // is always needed: the template data mapper falls back to the default template without it.
            // Do not start from normalize(): that is the form representation (e.g. the full media object of the
            // image) and writing it back would store it in the content.
            $current = $this->contentManager->resolve($event, $attributes);
            $templateKey = $current instanceof TemplateInterface ? $current->getTemplateKey() : null;
            $data = ($content ?? [])
                + (null !== $title ? ['title' => $title] : [])
                + (null !== $templateKey ? ['template' => $templateKey] : []);

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
