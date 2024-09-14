<?php

namespace Manuxi\SuluEventBundle\Domain\Event\Location;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\Location;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

abstract class AbstractDeletedEvent extends DomainEvent
{
    public function __construct(private int $id, private string $title = '')
    {
        parent::__construct();
    }

    public function getResourceKey(): string
    {
        return Location::RESOURCE_KEY;
    }

    public function getResourceId(): string
    {
        return (string)$this->id;
    }

    public function getResourceTitle(): ?string
    {
        return $this->title;
    }

    public function getResourceSecurityContext(): ?string
    {
        return Event::SECURITY_CONTEXT;
    }
}
