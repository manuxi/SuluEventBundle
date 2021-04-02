<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

use Manuxi\SuluEventBundle\Entity\Location;
use Symfony\Component\HttpFoundation\Request;

interface LocationModelInterface
{
    public function createLocation(Request $request): Location;
    public function updateLocation(int $id, Request $request): Location;
    public function getLocation(int $id): Location;
}
