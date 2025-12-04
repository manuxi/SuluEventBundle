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
use Sulu\Content\Domain\Model\WebspaceTrait;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Sulu\Content\Domain\Model\WorkflowTrait;

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
    protected Event $event;
    protected ?string $title = null;
    protected ?string $subtitle = null;
    protected ?string $summary = null;
    protected ?string $text = null;
    protected ?string $footer = null;
    protected ?MediaInterface $image = null;
    protected ?array $images = null;
    protected ?MediaInterface $pdf = null;
    protected ?ContactInterface $speaker = null;
    protected ?bool $showAuthor = true;
    protected ?bool $showDate = true;

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

    /**
     * @param array<string, mixed> $templateData
     */
    public function setTemplateData(array $templateData): void
    {
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