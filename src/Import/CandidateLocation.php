<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import;

/**
 * The place of a candidate as far as the source tells it. Only the name is required.
 */
final class CandidateLocation
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $street = null,
        public readonly ?string $number = null,
        public readonly ?string $postalCode = null,
        public readonly ?string $city = null,
        public readonly ?string $state = null,
        public readonly ?string $countryCode = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
    ) {
    }
}
