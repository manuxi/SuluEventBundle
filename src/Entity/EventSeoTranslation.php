<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="app_event_seo_translation")
 * @ORM\Entity(repositoryClass="Manuxi\SuluEventBundle\Repository\EventSeoTranslationRepository")
 */
class EventSeoTranslation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Manuxi\SuluEventBundle\Entity\EventSeo", inversedBy="translations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $eventSeo;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $locale;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $canonicalUrl = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $keywords = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description = null;

    public function __construct(EventSeo $eventSeo, string $locale)
    {
        $this->eventSeo = $eventSeo;
        $this->locale   = $locale;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEventSeo(): EventSeo
    {
        return $this->eventSeo;
    }

    public function getLocale(): string
    {
        return $this->locale;
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

    public function getCanonicalUrl(): ?string
    {
        return $this->canonicalUrl;
    }

    public function setCanonicalUrl(?string $canonicalUrl): self
    {
        $this->canonicalUrl = $canonicalUrl;

        return $this;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(?string $keywords): self
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
