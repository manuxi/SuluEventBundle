<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Manuxi\SuluEventBundle\Entity\AttendanceMode;

/**
 * Select of the way to take part (on site, online, both).
 */
class AttendanceModeSelect extends AbstractTranslatedSelect
{
    public function getDefaultValue(): string
    {
        return AttendanceMode::DEFAULT->value;
    }

    protected function getKeys(): array
    {
        return AttendanceMode::values();
    }

    protected function getTranslationPrefix(): string
    {
        return 'sulu_event.attendance_mode.';
    }
}
