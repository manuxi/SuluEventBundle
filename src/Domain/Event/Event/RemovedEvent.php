<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Domain\Event\Event;

class RemovedEvent extends AbstractDeletedEvent
{
    public function getEventType(): string
    {
        return 'removed';
    }
}
