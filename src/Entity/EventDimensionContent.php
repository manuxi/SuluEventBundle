<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Content\Domain\Model\AuditableInterface;
use Sulu\Content\Domain\Model\AuditableTrait;
use Sulu\Content\Domain\Model\AuthorInterface;
use Sulu\Content\Domain\Model\AuthorTrait;
use Sulu\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\DimensionContentTrait;
use Sulu\Content\Domain\Model\ExcerptInterface;
use Sulu\Content\Domain\Model\ExcerptTrait;
use Sulu\Content\Domain\Model\LinkInterface;
use Sulu\Content\Domain\Model\LinkTrait;
use Sulu\Content\Domain\Model\RoutableInterface;
use Sulu\Content\Domain\Model\RoutableTrait;
use Sulu\Content\Domain\Model\SeoInterface;
use Sulu\Content\Domain\Model\SeoTrait;
use Sulu\Content\Domain\Model\ShadowInterface;
use Sulu\Content\Domain\Model\ShadowTrait;
use Sulu\Content\Domain\Model\TaxonomyInterface;
use Sulu\Content\Domain\Model\TaxonomyTrait;
use Sulu\Content\Domain\Model\TemplateInterface;
use Sulu\Content\Domain\Model\TemplateTrait;
use Sulu\Content\Domain\Model\WebspaceInterface;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Content\Domain\Model\WebspaceTrait;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Sulu\Content\Domain\Model\WorkflowTrait;
use Symfony\Component\Serializer\Attribute\Ignore;

/**
 * @implements DimensionContentInterface<Event>
 */
class EventDimensionContent implements
    DimensionContentInterface,
    ExcerptInterface,
    TaxonomyInterface,
    SeoInterface,
    TemplateInterface,
    RoutableInterface,
    WorkflowInterface,
    AuthorInterface,
    WebspaceInterface,
    ShadowInterface,
    AuditableInterface,
    LinkInterface
{
    use AuthorTrait;
    use DimensionContentTrait;
    use ExcerptTrait;
    use TaxonomyTrait;
    use RoutableTrait;
    use SeoTrait;
    use ShadowTrait;
    use TemplateTrait {
        TemplateTrait::setTemplateData as parentSetTemplateData;
    }
    use WebspaceTrait;
    use WorkflowTrait;
    use AuditableTrait;
    use LinkTrait;

    protected int $id;

    #[Ignore]
    protected Event $event;

    protected ?string $type = 'default';
    protected ?\DateTimeImmutable $startDate = null;
    protected ?\DateTimeImmutable $endDate = null;
    protected ?string $email = null;
    protected ?string $phoneNumber = null;
    protected ?Location $location = null;
    protected ?EventSocialSettings $socialSettings = null;
    protected ?EventRecurrence $recurrence = null;

    protected ?string $title = null;
    protected ?string $subtitle = null;
    protected ?string $summary = null;
    protected ?string $text = null;
    protected ?string $footer = null;
    protected ?MediaInterface $image = null;
    protected ?array $images = null;
    protected ?MediaInterface $pdf = null;
    protected ?ContactInterface $speaker = null;
    protected ?bool $showAuthor = false;
    protected ?bool $showDate = false;

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->created = new \DateTimeImmutable();
        $this->changed = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getResource(): ContentRichEntityInterface
    {
        return $this->event;
    }

    public function getEvent(): Event
    {
        return $this->event;
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



    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName("locationId")]
    #[Serializer\Type("int")]
    public function getLocationId(): ?int
    {
        return $this->location ? $this->location->getId() : null;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): self
    {
        $this->subtitle = $subtitle;
        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;
        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function getFooter(): ?string
    {
        return $this->footer;
    }

    public function setFooter(?string $footer): self
    {
        $this->footer = $footer;
        return $this;
    }

    public function getImage(): ?MediaInterface
    {
        return $this->image;
    }

    public function setImage(?MediaInterface $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getImages(): ?array
    {
        return $this->images ?? [];
    }

    public function setImages(?array $images): self
    {
        $this->images = $images;
        return $this;
    }

    public function getPdf(): ?MediaInterface
    {
        return $this->pdf;
    }

    public function setPdf(?MediaInterface $pdf): self
    {
        $this->pdf = $pdf;
        return $this;
    }

    public function getSpeaker(): ?ContactInterface
    {
        return $this->speaker;
    }

    public function setSpeaker(?ContactInterface $speaker): self
    {
        $this->speaker = $speaker;
        return $this;
    }

    public function getShowAuthor(): ?bool
    {
        return $this->showAuthor;
    }

    public function setShowAuthor(?bool $showAuthor): self
    {
        $this->showAuthor = $showAuthor;
        return $this;
    }

    public function getShowDate(): ?bool
    {
        return $this->showDate;
    }

    public function setShowDate(?bool $showDate): self
    {
        $this->showDate = $showDate;
        return $this;
    }

    public function copyAttributesFrom(DimensionContentInterface $dimensionContent): void
    {
        if (!$dimensionContent instanceof self) {
            return;
        }

        $this->type = $dimensionContent->type;
        $this->startDate = $dimensionContent->startDate;
        $this->endDate = $dimensionContent->endDate;
        $this->email = $dimensionContent->email;
        $this->phoneNumber = $dimensionContent->phoneNumber;
        $this->location = $dimensionContent->location;
        $this->socialSettings = $dimensionContent->socialSettings;
        $this->recurrence = $dimensionContent->recurrence;

        $this->title = $dimensionContent->title;
        $this->subtitle = $dimensionContent->subtitle;
        $this->summary = $dimensionContent->summary;
        $this->text = $dimensionContent->text;
        $this->footer = $dimensionContent->footer;
        $this->image = $dimensionContent->image;
        $this->images = $dimensionContent->images;
        $this->pdf = $dimensionContent->pdf;
        $this->speaker = $dimensionContent->speaker;
        $this->showAuthor = $dimensionContent->showAuthor;
        $this->showDate = $dimensionContent->showDate;
        $this->author = $dimensionContent->author;
        $this->authored = $dimensionContent->authored;
    }

    public function setTemplateData(array $templateData): void
    {
        // Non-localized fields
        if (\array_key_exists('type', $templateData)) {
            $this->type = \is_string($templateData['type']) ? $templateData['type'] : null;
        }

        if (\array_key_exists('startDate', $templateData)) {
            $this->startDate = $templateData['startDate'] instanceof \DateTimeImmutable ? $templateData['startDate'] : null;
        }

        if (\array_key_exists('endDate', $templateData)) {
            $this->endDate = $templateData['endDate'] instanceof \DateTimeImmutable ? $templateData['endDate'] : null;
        }

        if (\array_key_exists('email', $templateData)) {
            $this->email = \is_string($templateData['email']) ? $templateData['email'] : null;
        }

        if (\array_key_exists('phoneNumber', $templateData)) {
            $this->phoneNumber = \is_string($templateData['phoneNumber']) ? $templateData['phoneNumber'] : null;
        }

        // Localized fields
        if (\array_key_exists('title', $templateData)) {
            $this->title = \is_string($templateData['title']) ? $templateData['title'] : null;
        }

        if (\array_key_exists('subtitle', $templateData)) {
            $this->subtitle = \is_string($templateData['subtitle']) ? $templateData['subtitle'] : null;
        }

        if (\array_key_exists('summary', $templateData)) {
            $this->summary = \is_string($templateData['summary']) ? $templateData['summary'] : null;
        }

        if (\array_key_exists('text', $templateData)) {
            $this->text = \is_string($templateData['text']) ? $templateData['text'] : null;
        }

        if (\array_key_exists('footer', $templateData)) {
            $this->footer = \is_string($templateData['footer']) ? $templateData['footer'] : null;
        }

        if (\array_key_exists('images', $templateData)) {
            $this->images = \is_array($templateData['images']) ? $templateData['images'] : null;
        }

        if (\array_key_exists('speaker', $templateData)) {
            $this->speaker = $templateData['speaker'] instanceof ContactInterface ? $templateData['speaker'] : null;
        }

        if (\array_key_exists('showAuthor', $templateData)) {
            $this->showAuthor = \is_bool($templateData['showAuthor']) ? $templateData['showAuthor'] : null;
        }

        if (\array_key_exists('showDate', $templateData)) {
            $this->showDate = \is_bool($templateData['showDate']) ? $templateData['showDate'] : null;
        }

        $this->parentSetTemplateData($templateData);
    }

    public static function getTemplateType(): string
    {
        return Event::TEMPLATE_TYPE;
    }

    public static function getResourceKey(): string
    {
        return Event::RESOURCE_KEY;
    }
}