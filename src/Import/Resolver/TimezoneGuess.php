<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\Resolver;

/**
 * A guessed IANA time zone and whether it is certain: a country with one zone is, a country with several zones
 * is not (the zone of the capital or, with coordinates, the nearest one is taken).
 */
final class TimezoneGuess
{
    public function __construct(
        public readonly string $timezone,
        public readonly bool $certain,
    ) {
    }
}
