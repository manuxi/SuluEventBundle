<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluEventBundle\Entity\Interfaces\AuditableInterface;
use Manuxi\SuluEventBundle\Entity\Traits\AuditableTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="app_event_translation")
 * @ORM\Entity(repositoryClass="Manuxi\SuluEventBundle\Repository\EventTranslationRepository")
 */
class EventTranslation implements AuditableInterface
{
    use AuditableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Manuxi\SuluEventBundle\Entity\Event", inversedBy="translations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $event;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $locale;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subtitle;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $summary;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $text;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $footer;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $routePath;

    public function __construct(Event $event, string $locale)
    {
        $this->event  = $event;
        $this->locale = $locale;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
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

    public function getRoutePath(): string
    {
        return $this->routePath ?? '';
    }

    public function setRoutePath(string $routePath): self
    {
        $this->routePath = $routePath;
        return $this;
    }

}
