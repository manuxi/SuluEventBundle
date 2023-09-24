<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Domain\Event\Location;

class RestoredEvent extends AbstractEvent
{
    public function getEventType(): string
    {
        return 'restored';
    }
}
