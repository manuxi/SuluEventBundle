<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Sulu\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Content\Domain\Model\ContentRichEntityTrait;
use Sulu\Content\Domain\Model\DimensionContentInterface;

/**
 * @implements ContentRichEntityInterface<EventDimensionContent>
 */
class Event implements ContentRichEntityInterface
{
    /**
     * @phpstan-use ContentRichEntityTrait<EventDimensionContent>
     */
    use ContentRichEntityTrait;

    public const RESOURCE_KEY = 'events';
    public const FORM_KEY = 'event_details';
    public const LIST_KEY = 'events';
    public const SECURITY_CONTEXT = 'sulu.events.events';
    public const TEMPLATE_TYPE = 'event';

    private ?int $id = null;

    private ?string $type = 'default';

    private ?\DateTimeImmutable $startDate = null;

    private ?\DateTimeImmutable $endDate = null;

    private ?string $email = null;

    private ?string $phoneNumber = null;

    private ?Location $location = null;

    private ?EventSocialSettings $socialSettings = null;

    private ?EventRecurrence $recurrence = null;

    public function __construct()
    {
        $this->initializeDimensionContents();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return EventDimensionContent
     */
    public function createDimensionContent(): DimensionContentInterface
    {
        return new EventDimensionContent($this);
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getSocialSettings(): ?EventSocialSettings
    {
        return $this->socialSettings;
    }

    public function setSocialSettings(?EventSocialSettings $socialSettings): self
    {
        $this->socialSettings = $socialSettings;

        if ($socialSettings && $socialSettings->getEvent() !== $this) {
            $socialSettings->setEvent($this);
        }

        return $this;
    }

    public function getRecurrence(): ?EventRecurrence
    {
        return $this->recurrence;
    }

    public function setRecurrence(?EventRecurrence $recurrence): self
    {
        $this->recurrence = $recurrence;

        if ($recurrence && $recurrence->getEvent() !== $this) {
            $recurrence->setEvent($this);
        }

        return $this;
    }
}