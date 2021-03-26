<?php

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table(name="app_event_seo")
 * @ORM\Entity(repositoryClass="Manuxi\SuluEventBundle\Repository\EventSeoRepository")
 */
class EventSeo
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id = null;

    /**
     * @ORM\OneToOne(targetEntity="Manuxi\SuluEventBundle\Entity\Event", inversedBy="eventSeo", cascade={"persist", "remove"})
     * @JoinColumn(name="event_id", referencedColumnName="id", nullable=false)
     *
     * @Serializer\Exclude
     */
    private $event = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hideInSitemap = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $noFollow = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $noIndex = false;

    /**
     * @var Collection<string, EventTranslation>
     *
     * @ORM\OneToMany(targetEntity="EventSeoTranslation", mappedBy="eventSeo", cascade={"ALL"}, indexBy="locale")
     *
     * @Serializer\Exclude
     */
    private $translations;

    private $locale = 'en';

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;
        return $this;
    }

    public function getHideInSitemap(): bool
    {
        return $this->hideInSitemap;
    }

    public function setHideInSitemap(bool $hideInSitemap): self
    {
        $this->hideInSitemap = $hideInSitemap;
        return $this;
    }

    public function getNoFollow(): bool
    {
        return $this->noFollow;
    }

    public function setNoFollow(bool $noFollow): self
    {
        $this->noFollow = $noFollow;
        return $this;
    }

    public function getNoIndex(): bool
    {
        return $this->noIndex;
    }

    public function setNoIndex(bool $noIndex): self
    {
        $this->noIndex = $noIndex;
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="title")
     */
    public function getTitle(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }

        return $translation->getTitle();
    }

    public function setTitle(?string $title): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setTitle($title);

        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="keywords")
     */
    public function getKeywords(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }

        return $translation->getKeywords();
    }

    public function setKeywords(?string $keywords): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setKeywords($keywords);

        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="canonicalUrl")
     */
    public function getCanonicalUrl(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }

        return $translation->getCanonicalUrl();
    }

    public function setCanonicalUrl(?string $canonicalUrl): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setCanonicalUrl($canonicalUrl);

        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="description")
     */
    public function getDescription(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }

        return $translation->getDescription();
    }

    public function setDescription(?string $description): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setDescription($description);

        return $this;
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

    /**
     * @return EventTranslation[]
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    protected function getTranslation(string $locale): ?EventSeoTranslation
    {
        if (!$this->translations->containsKey($locale)) {
            return null;
        }

        return $this->translations->get($locale);
    }

    protected function createTranslation(string $locale): EventSeoTranslation
    {
        $translation = new EventSeoTranslation($this, $locale);
        $this->translations->set($locale, $translation);

        return $translation;
    }
}
