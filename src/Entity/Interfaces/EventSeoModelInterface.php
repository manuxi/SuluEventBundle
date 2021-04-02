<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

use Manuxi\SuluEventBundle\Entity\EventSeo;
use Symfony\Component\HttpFoundation\Request;

interface EventSeoModelInterface
{
    public function updateEventSeo(EventSeo $eventSeo, Request $request): EventSeo;
}
