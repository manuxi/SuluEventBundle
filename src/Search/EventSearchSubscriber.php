<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Search;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Search\Event\EventPublishedEvent;
use Manuxi\SuluEventBundle\Search\Event\EventRemovedEvent;
use Manuxi\SuluEventBundle\Search\Event\EventSavedEvent;
use Manuxi\SuluEventBundle\Search\Event\EventUnpublishedEvent;
use Massive\Bundle\SearchBundle\Search\SearchManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSearchSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SearchManagerInterface $searchManager,
        private LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EventPublishedEvent::class => 'onPublished',
            EventUnpublishedEvent::class => 'onUnpublished',
            EventSavedEvent::class => 'onSaved',
            EventRemovedEvent::class => 'onRemoved',
        ];
    }

    private function refreshEntity (Event $entity)
    {
        //$repository->findById($entity->getId(), $entity->getLocale());
    }

    public function onPublished(EventPublishedEvent $event): void
    {
        $eventEntity = $event->getEntity();
        $this->logger->info('EventSearchSubscriber: PublishedEvent: id: '.$eventEntity->getId().', locale: '.$eventEntity->getLocale().', published: '.($eventEntity->getPublished() ? 1 : 0));
        $this->searchManager->index($eventEntity);
    }

    public function onUnpublished(EventUnpublishedEvent $event): void
    {
        $eventEntity = $event->getEntity();
        $this->logger->info('EventSearchSubscriber: UnpublishedEvent: id: '.$eventEntity->getId().', locale: '.$eventEntity->getLocale().', published: '.($eventEntity->getPublished() ? 1 : 0));
        $this->searchManager->deindex($eventEntity);
    }

    public function onSaved(EventSavedEvent $event): void
    {
        $this->logger->info('EventSearchSubscriber: SavedEvent');
        $eventEntity = $event->getEntity();
        $this->searchManager->index($eventEntity);

    }

    public function onRemoved(EventRemovedEvent $event): void
    {
        $this->logger->info('EventSearchSubscriber: RemovedEvent');
        $eventEntity = $event->getEntity();
        $this->searchManager->deindex($eventEntity);
    }
}
