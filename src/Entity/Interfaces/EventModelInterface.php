<?php

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

use Manuxi\SuluEventBundle\Entity\Event;
use Symfony\Component\HttpFoundation\Request;

interface EventModelInterface
{
    public function createEvent(Request $request): Event;
    public function updateEvent(int $id, Request $request): Event;
    public function enableEvent(int $id, Request $request): Event;

}
