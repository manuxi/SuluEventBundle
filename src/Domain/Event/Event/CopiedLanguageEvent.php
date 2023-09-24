<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Domain\Event\Event;

class CopiedLanguageEvent extends AbstractEvent
{
    public function getEventType(): string
    {
        return 'published';
    }
}
