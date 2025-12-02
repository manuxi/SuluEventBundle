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
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSearchListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly EngineInterface $engine,
        private readonly WebspaceManagerInterface $webspaceManager,
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

        // Always update admin index
        $this->indexForAdmin($event);

        // Update website index only if published
        if ($event->isPublished()) {
            $this->indexForWebsite($event);
        }
    }

    public function onPublished(PublishedEvent $domainEvent): void
    {
        $event = $domainEvent->getEntity();

        // Update both indexes
        $this->indexForAdmin($event);
        $this->indexForWebsite($event);
    }

    public function onUnpublished(UnpublishedEvent $domainEvent): void
    {
        $event = $domainEvent->getEntity();

        // Update admin index
        $this->indexForAdmin($event);

        // Remove from website index
        $documentId = $this->getDocumentId($event);
        $this->engine->deleteDocument('website', $documentId);  // ← 'website'
    }

    public function onRemoved(RemovedEvent $domainEvent): void
    {
        // Remove from all locale variants in both indexes
        foreach ($this->getLocales() as $locale) {
            $documentId = 'event-'.$domainEvent->getResourceId().'-'.$locale;
            $this->engine->deleteDocument('admin', $documentId);  // ← 'admin'
            $this->engine->deleteDocument('website', $documentId);  // ← 'website'
        }
    }

    private function indexForAdmin(Event $event): void
    {
        $this->engine->saveDocument('admin', [
            'id' => $this->getDocumentId($event),
            'resourceKey' => Event::RESOURCE_KEY,
            'resourceId' => (string) $event->getId(),
            'locale' => $event->getLocale(),
            'securityContext' => Event::SECURITY_CONTEXT,
            'title' => $event->getTitle() ?? '',
            'mediaId' => $event->getImage()?->getId(),
            'changedAt' => $event->getChanged()?->format('c'),
            'createdAt' => $event->getCreated()?->format('c'),
            'published' => $event->isPublished() ? 1 : 0,
            'startDate' => $event->getStartDate()?->format('c'),
        ]);
    }

    private function indexForWebsite(Event $event): void
    {
        $content = array_filter([
            $event->getSubtitle(),
            $event->getSummary(),
            $event->getText(),
            $event->getFooter(),
        ]);

        $this->engine->saveDocument('website', [
            'id' => $this->getDocumentId($event),
            'resourceKey' => Event::RESOURCE_KEY,
            'resourceId' => (string) $event->getId(),
            'locale' => $event->getLocale(),
            'webspaces' => [],
            'title' => $event->getTitle() ?? '',
            'url' => $event->getRoutePath() ?? '',
            'content' => $content,
            'mediaId' => $event->getImage()?->getId(),
            'startDate' => $event->getStartDate()?->format('c'),
        ]);
    }

    private function getDocumentId(Event $event): string
    {
        return 'event-'.$event->getId().'-'.$event->getLocale();
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