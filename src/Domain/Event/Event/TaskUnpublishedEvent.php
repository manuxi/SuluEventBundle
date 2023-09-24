<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Domain\Event\Event;

use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

class TaskUnpublishedEvent extends AbstractEvent
{
    public function getEventType(): string
    {
        return 'unpublished';
    }
}
