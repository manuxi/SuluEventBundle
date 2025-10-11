<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Search;

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

    public function onPublished(EventPublishedEvent $event): void
    {
        $eventEntity = $event->getEntity();
        $this->logger->info('EventSearchSubscriber: PublishedEvent: id: '.$eventEntity->getId().', locale: '.$eventEntity->getLocale().', published: '.($eventEntity->getPublished() ? 1 : 0));
        if ($eventEntity->isPublished()) {
            $this->searchManager->index($eventEntity);
        }
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
        $entity = $event->getEntity();
        $this->searchManager->deindex($entity);
        if ($entity->isPublished()) {
            $this->searchManager->index($entity);
        }
    }

    public function onRemoved(EventRemovedEvent $event): void
    {
        $this->logger->info('EventSearchSubscriber: RemovedEvent');
        $entity = $event->getEntity();
        $this->searchManager->deindex($entity);
    }
}
