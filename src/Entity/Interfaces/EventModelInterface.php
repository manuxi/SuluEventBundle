<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

use Manuxi\SuluEventBundle\Entity\Event;
use Symfony\Component\HttpFoundation\Request;

interface EventModelInterface
{
    public function getEvent(int $id, ?Request $request = null): Event;

    public function deleteEvent(Event $entity): void;

    public function createEvent(Request $request): Event;

    public function updateEvent(int $id, Request $request): Event;

    public function publish(int $id, Request $request): Event;

    public function unpublish(int $id, Request $request): Event;
}
