<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Domain\Event\Location;

class ModifiedEvent extends AbstractEvent
{
    public function getEventType(): string
    {
        return 'modified';
    }
}
