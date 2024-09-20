<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content;

use JMS\Serializer\Annotation as Serializer;
use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Component\SmartContent\ItemInterface;

#[Serializer\ExclusionPolicy("all")]
class EventDataItem implements ItemInterface
{
    public function __construct(
        private Event $entity
    ) {}

    #[Serializer\VirtualProperty]
    public function getId(): string
    {
        return (string) $this->entity->getId();
    }

    #[Serializer\VirtualProperty]
    public function getTitle(): string
    {
        return (string) $this->entity->getTitle();
    }

    #[Serializer\VirtualProperty]
    public function getImage(): ?int
    {
        return null;
    }

    public function getResource(): Event
    {
        return $this->entity;
    }
}
