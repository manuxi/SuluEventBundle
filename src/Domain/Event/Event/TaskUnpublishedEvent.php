<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Domain\Event\Event;

class TaskUnpublishedEvent extends AbstractEvent
{
    public function getEventType(): string
    {
        return 'unpublished';
    }
}
