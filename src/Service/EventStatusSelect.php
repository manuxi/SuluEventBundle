<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Manuxi\SuluEventBundle\Entity\EventStatus;

/**
 * Select of the event status (scheduled, postponed, rescheduled, cancelled, moved_online).
 */
class EventStatusSelect extends AbstractTranslatedSelect
{
    public function getDefaultValue(): string
    {
        return EventStatus::DEFAULT->value;
    }

    protected function getKeys(): array
    {
        return EventStatus::values();
    }

    protected function getTranslationPrefix(): string
    {
        return 'sulu_event.status.';
    }
}
