<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Symfony\Component\Serializer\Attribute\Ignore;

class EventRecurrence
{
    private ?int $id = null;

    #[Ignore]
    private Event $event;

    private bool $isRecurring = false;
    private ?string $frequency = null;
    private int $interval = 1;
    private array $byWeekday = [];
    private string $endType = 'never';
    private ?int $count = null;
    private ?\DateTime $until = null;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    #[Ignore]
    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getIsRecurring(): bool
    {
        return $this->isRecurring;
    }

    public function setIsRecurring(bool $isRecurring): self
    {
        $this->isRecurring = $isRecurring;
        return $this;
    }

    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    public function setFrequency(?string $frequency): self
    {
        $this->frequency = $frequency;
        return $this;
    }

    public function getInterval(): int
    {
        return $this->interval;
    }

    public function setInterval(int $interval): self
    {
        $this->interval = $interval;
        return $this;
    }

    public function getByWeekday(): array
    {
        return $this->byWeekday;
    }

    public function setByWeekday(array $byWeekday): self
    {
        $this->byWeekday = $byWeekday;
        return $this;
    }

    public function getEndType(): string
    {
        return $this->endType;
    }

    public function setEndType(string $endType): self
    {
        $this->endType = $endType;
        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(?int $count): self
    {
        $this->count = $count;
        return $this;
    }

    public function getUntil(): ?\DateTime
    {
        return $this->until;
    }

    public function setUntil(?\DateTime $until): self
    {
        $this->until = $until;
        return $this;
    }
}