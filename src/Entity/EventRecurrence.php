<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

class EventRecurrence
{
    private ?int $id = null;
    private EventDimensionContent $dimensionContent;
    private bool $isRecurring = false;
    private ?string $frequency = null; // daily, weekly, monthly, yearly
    private int $interval = 1; // every X days/weeks/months
    private array $byWeekday = []; // [1,3,5] for Mon, Wed, Fri
    private string $endType = 'never'; // never, count, until
    private ?int $count = null; // number of occurrences
    private ?\DateTime $until = null; // end date

    public function __construct(EventDimensionContent $dimensionContent)
    {
        $this->dimensionContent = $dimensionContent;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDimensionContent(): EventDimensionContent
    {
        return $this->dimensionContent;
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