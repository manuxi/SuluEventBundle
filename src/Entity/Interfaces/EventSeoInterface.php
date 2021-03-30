<?php

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

use Manuxi\SuluEventBundle\Entity\EventSeo;
use Symfony\Component\HttpFoundation\Request;

interface EventSeoInterface
{
    public function updateEventSeo(EventSeo $eventSeo, Request $request): EventSeo;
}
