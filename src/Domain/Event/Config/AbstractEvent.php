<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Domain\Event\Config;


use Manuxi\SuluEventBundle\Entity\EventSettings;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

abstract class AbstractEvent extends DomainEvent
{
    private EventSettings $event;
    private array $payload = [];

    public function __construct(EventSettings $event)
    {
        parent::__construct();
        $this->event = $event;
    }

    public function getEvent(): EventSettings
    {
        return $this->event;
    }

    public function getEventPayload(): ?array
    {
        return $this->payload;
    }

    public function getResourceKey(): string
    {
        return EventSettings::RESOURCE_KEY;
    }

    public function getResourceId(): string
    {
        return (string)$this->event->getId();
    }

    public function getResourceTitle(): ?string
    {
        return "Event Settings";
    }

    public function getResourceSecurityContext(): ?string
    {
        return EventSettings::SECURITY_CONTEXT;
    }
}
