<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

/**
 * Whether an event takes place as announced. Stored as the string value.
 */
enum EventStatus: string
{
    case Scheduled   = 'scheduled';
    case Postponed   = 'postponed';
    case Rescheduled = 'rescheduled';
    case Cancelled   = 'cancelled';
    case MovedOnline = 'moved_online';

    public const DEFAULT = self::Scheduled;

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
