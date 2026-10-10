<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\Resolver;

use Manuxi\SuluEventBundle\Entity\Location;

final class ResolvedLocation
{
    public function __construct(
        public readonly Location $location,
        public readonly bool $created,
        public readonly bool $timezoneCertain = true,
    ) {
    }
}
