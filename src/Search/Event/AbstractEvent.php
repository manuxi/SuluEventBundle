<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Search\Event;

use Manuxi\SuluEventBundle\Entity\Event;
use Symfony\Contracts\EventDispatcher\Event as SymfonyEvent;

abstract class AbstractEvent extends SymfonyEvent
{
    public function __construct(private readonly Event $entity)
    {
    }

    public function getEntity(): Event
    {
        return $this->entity;
    }
}
