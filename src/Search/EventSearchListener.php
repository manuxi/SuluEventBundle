<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Search;

use CmsIg\Seal\EngineInterface;
use Manuxi\SuluEventBundle\Domain\Event\Event\CreatedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\ModifiedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\PublishedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\RemovedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\UnpublishedEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSearchListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly EngineInterface $engine,
        private readonly WebspaceManagerInterface $webspaceManager,
        private readonly ContentAggregatorInterface $contentAggregator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CreatedEvent::class => 'onCreatedOrModified',
            ModifiedEvent::class => 'onCreatedOrModified',
            PublishedEvent::class => 'onPublished',
            UnpublishedEvent::class => 'onUnpublished',
            RemovedEvent::class => 'onRemoved',
        ];
    }

    public function onCreatedOrModified(CreatedEvent|ModifiedEvent $domainEvent): void
    {
        $event = $domainEvent->getEntity();

        // Index for all locales
        foreach ($this->getLocales() as $locale) {
            /** @var EventDimensionContent $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate(
                $event,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_DRAFT,
                    'version' => DimensionContentInterface::CURRENT_VERSION,
                ]
            );

            // Skip if no content for this locale
            if (!$dimensionContent->getTitle()) {
                continue;
            }

            // Always update admin index
            $this->indexForAdmin($event, $dimensionContent, $locale);

            // Update website index only if published
            if (WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $dimensionContent->getWorkflowPlace()) {
                $this->indexForWebsite($event, $dimensionContent, $locale);
            }
        }
    }

    public function onPublished(PublishedEvent $domainEvent): void
    {
        $event = $domainEvent->getEntity();

        foreach ($this->getLocales() as $locale) {
            /** @var EventDimensionContent $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate(
                $event,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_LIVE,
                    'version' => DimensionContentInterface::CURRENT_VERSION,
                ]
            );

            if (!$dimensionContent->getTitle()) {
                continue;
            }

            // Update both indexes
            $this->indexForAdmin($event, $dimensionContent, $locale);
            $this->indexForWebsite($event, $dimensionContent, $locale);
        }
    }

    public function onUnpublished(UnpublishedEvent $domainEvent): void
    {
        $event = $domainEvent->getEntity();

        foreach ($this->getLocales() as $locale) {
            /** @var EventDimensionContent $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate(
                $event,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_DRAFT,
                    'version' => DimensionContentInterface::CURRENT_VERSION,
                ]
            );

            if (!$dimensionContent->getTitle()) {
                continue;
            }

            // Update admin index
            $this->indexForAdmin($event, $dimensionContent, $locale);

            // Remove from website index
            $documentId = $this->getDocumentId($event, $locale);
            $this->engine->deleteDocument('website', $documentId);
        }
    }

    public function onRemoved(RemovedEvent $domainEvent): void
    {
        // Remove from all locale variants in both indexes
        foreach ($this->getLocales() as $locale) {
            $documentId = 'event-'.$domainEvent->getResourceId().'-'.$locale;
            $this->engine->deleteDocument('admin', $documentId);
            $this->engine->deleteDocument('website', $documentId);
        }
    }

    private function indexForAdmin(Event $event, EventDimensionContent $dimensionContent, string $locale): void
    {
        $this->engine->saveDocument('admin', [
            'id' => $this->getDocumentId($event, $locale),
            'resourceKey' => Event::RESOURCE_KEY,
            'resourceId' => (string) $event->getId(),
            'locale' => $locale,
            'securityContext' => Event::SECURITY_CONTEXT,
            'title' => $dimensionContent->getTitle() ?? '',
            'mediaId' => $dimensionContent->getImage()?->getId(),
            'changedAt' => $dimensionContent->getChanged()?->format('c'),
            'createdAt' => $dimensionContent->getCreated()?->format('c'),
            'published' => WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $dimensionContent->getWorkflowPlace() ? 1 : 0,
            'startDate' => $event->getStartDate()?->format('c'),
        ]);
    }

    private function indexForWebsite(Event $event, EventDimensionContent $dimensionContent, string $locale): void
    {
        $content = array_filter([
            $dimensionContent->getSubtitle(),
            $dimensionContent->getSummary(),
            $dimensionContent->getText(),
            $dimensionContent->getFooter(),
        ]);

        $this->engine->saveDocument('website', [
            'id' => $this->getDocumentId($event, $locale),
            'resourceKey' => Event::RESOURCE_KEY,
            'resourceId' => (string) $event->getId(),
            'locale' => $locale,
            'webspaces' => [],
            'title' => $dimensionContent->getTitle() ?? '',
            'url' => $dimensionContent->getRoute()?->getSlug() ?? '',
            'content' => $content,
            'mediaId' => $dimensionContent->getImage()?->getId(),
            'startDate' => $event->getStartDate()?->format('c'),
        ]);
    }

    private function getDocumentId(Event $event, string $locale): string
    {
        return 'event-'.$event->getId().'-'.$locale;
    }

    private function getLocales(): array
    {
        $locales = [];
        foreach ($this->webspaceManager->getWebspaceCollection() as $webspace) {
            foreach ($webspace->getAllLocalizations() as $localization) {
                $locales[$localization->getLocale()] = true;
            }
        }

        return array_keys($locales);
    }
}
