<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Domain\Event\Location;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\Location;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

abstract class AbstractEvent extends DomainEvent
{
    private array $payload = [];

    public function __construct(private Location $location)
    {
        parent::__construct();
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getEventPayload(): ?array
    {
        return $this->payload;
    }

    public function getResourceKey(): string
    {
        return Location::RESOURCE_KEY;
    }

    public function getResourceId(): string
    {
        return (string)$this->location->getId();
    }

    public function getResourceTitle(): ?string
    {
        return $this->location->getName();
    }

    public function getResourceSecurityContext(): ?string
    {
        return Event::SECURITY_CONTEXT;
    }
}
