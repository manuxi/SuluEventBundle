<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Domain\Event;

use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

class ModifiedEvent extends DomainEvent
{
    private Event $event;
    private array $payload = [];

    public function __construct(Event $event)
    {
        parent::__construct();
        $this->event = $event;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getEventPayload(): ?array
    {
        return $this->payload;
    }

    public function getEventType(): string
    {
        return 'modified';
    }

    public function getResourceKey(): string
    {
        return Event::RESOURCE_KEY;
    }

    public function getResourceId(): string
    {
        return (string)$this->event->getId();
    }

    public function getResourceTitle(): ?string
    {
        return $this->event->getTitle();
    }

    public function getResourceSecurityContext(): ?string
    {
        return Event::SECURITY_CONTEXT;
    }
}
