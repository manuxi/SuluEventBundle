<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

/**
 * How visitors take part: on site, online or both. Stored as the string value.
 */
enum AttendanceMode: string
{
    case Offline = 'offline';
    case Online  = 'online';
    case Mixed   = 'mixed';

    public const DEFAULT = self::Offline;

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
