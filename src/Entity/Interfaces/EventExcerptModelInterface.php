<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

use Manuxi\SuluEventBundle\Entity\EventExcerpt;
use Symfony\Component\HttpFoundation\Request;

interface EventExcerptModelInterface
{
    public function updateEventExcerpt(EventExcerpt $eventExcerpt, Request $request): EventExcerpt;
}
