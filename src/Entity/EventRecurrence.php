<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

#[ORM\Entity]
#[ORM\Table(name: 'app_event_recurrence')]
#[ORM\Index(columns: ['event_id'], name: 'idx_event_recurrence')]
class EventRecurrence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Serializer\Ignore]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'recurrence', targetEntity: Event::class)]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Event $event;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $frequency = 'weekly'; // daily, weekly, monthly, yearly

    #[ORM\Column(type: Types::INTEGER)]
    private int $interval = 1; // every X days/weeks/months

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $byWeekday = null; // [1,3,5] for Mon, Wed, Fri

    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $endType = 'never'; // never, count, until

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $count = null; // number of occurrences

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $until = null; // end date

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getFrequency(): string
    {
        return $this->frequency;
    }

    public function setFrequency(string $frequency): self
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

    public function getByWeekday(): ?array
    {
        return $this->byWeekday;
    }

    public function setByWeekday(?array $byWeekday): self
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

    public function getUntil(): ?\DateTimeInterface
    {
        return $this->until;
    }

    public function setUntil(?\DateTimeInterface $until): self
    {
        $this->until = $until;

        return $this;
    }
}
