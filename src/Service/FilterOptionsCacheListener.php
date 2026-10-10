<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Manuxi\SuluEventBundle\Domain\Event\Event\CopiedLanguageEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\CreatedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\ModifiedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\PublishedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\RemovedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\RestoredEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\TaskPublishedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\TaskUnpublishedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\UnpublishedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Forgets the cached filter options when an event is created, changed, published, unpublished, copied, removed or
 * restored.
 */
class FilterOptionsCacheListener implements EventSubscriberInterface
{
    public function __construct(private readonly EventFilterOptions $filterOptions)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CreatedEvent::class         => 'clear',
            ModifiedEvent::class        => 'clear',
            PublishedEvent::class       => 'clear',
            UnpublishedEvent::class     => 'clear',
            CopiedLanguageEvent::class  => 'clear',
            RemovedEvent::class         => 'clear',
            RestoredEvent::class        => 'clear',
            TaskPublishedEvent::class   => 'clear',
            TaskUnpublishedEvent::class => 'clear',
        ];
    }

    public function clear(): void
    {
        $this->filterOptions->clear();
    }
}
