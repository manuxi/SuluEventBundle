<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sulu\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Content\Domain\Model\ContentRichEntityTrait;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Symfony\Component\Uid\Uuid;

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
    public const FORM_KEY = 'event_detailed';
    public const LIST_KEY = 'events';
    public const LIST_KEY_PUBLISHED = 'events_published';

    public const SECURITY_CONTEXT = 'sulu.events.events';
    public const TEMPLATE_TYPE = 'event';

    protected string $uuid;

    protected ?EventSocialSettings $socialSettings = null;

    protected ?EventRecurrence $recurrence = null;

    /**
     * The entries of the import that point to this event (read only; it is the list of the admin that needs it).
     *
     * @var Collection<int, EventImport>
     */
    protected Collection $imports;

    public function __construct(?string $uuid = null)
    {
        $this->uuid = $uuid ?: Uuid::v7()->toRfc4122();
        $this->initializeDimensionContents();
        $this->imports = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->uuid;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function createDimensionContent(): DimensionContentInterface
    {
        return new EventDimensionContent($this);
    }

    public function getSocialSettings(): ?EventSocialSettings
    {
        return $this->socialSettings;
    }

    public function setSocialSettings(?EventSocialSettings $socialSettings): self
    {
        $this->socialSettings = $socialSettings;

        return $this;
    }

    public function getRecurrence(): ?EventRecurrence
    {
        return $this->recurrence;
    }

    public function setRecurrence(?EventRecurrence $recurrence): self
    {
        $this->recurrence = $recurrence;

        return $this;
    }
}