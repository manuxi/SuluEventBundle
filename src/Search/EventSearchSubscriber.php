<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Search;

use Manuxi\SuluEventBundle\Search\Event\EventPublishedEvent;
use Manuxi\SuluEventBundle\Search\Event\EventRemovedEvent;
use Manuxi\SuluEventBundle\Search\Event\EventSavedEvent;
use Manuxi\SuluEventBundle\Search\Event\EventUnpublishedEvent;
use Massive\Bundle\SearchBundle\Search\SearchManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSearchSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SearchManagerInterface $searchManager,
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
        $this->searchManager->index($eventEntity);
    }

    public function onUnpublished(EventUnpublishedEvent $event): void
    {
        $eventEntity = $event->getEntity();
        $this->searchManager->deindex($eventEntity);
    }

    public function onSaved(EventSavedEvent $event): void
    {
        $eventEntity = $event->getEntity();
        $this->searchManager->index($eventEntity);

    }

    public function onRemoved(EventRemovedEvent $event): void
    {
        $eventEntity = $event->getEntity();
        $this->searchManager->deindex($eventEntity);
    }
}
