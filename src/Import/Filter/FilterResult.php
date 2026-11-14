<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\Filter;

/**
 * The verdict of the sieve about one candidate: it passes, or it is sieved out with a code (for lists and tests) and a
 * readable reason (stored with the import entry, shown in the admin and by "--explain").
 */
final class FilterResult
{
    private function __construct(
        public readonly bool $passed,
        public readonly string $code,
        public readonly string $reason,
    ) {
    }

    public static function pass(): self
    {
        return new self(true, 'ok', '');
    }

    public static function reject(string $code, string $reason): self
    {
        return new self(false, $code, mb_substr($reason, 0, 255));
    }
}
