<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="app_event")
 * @ORM\Entity(repositoryClass="Manuxi\SuluEventBundle\Repository\EventRepository")
 */
class Event
{
    public const RESOURCE_KEY = 'events';

    public const FORM_KEY = 'event_details';

    public const LIST_KEY = 'events';

    public const SECURITY_CONTEXT = 'sulu.events.events';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Manuxi\SuluEventBundle\Entity\EventSeo", mappedBy="event", cascade={"persist", "remove"})
     *
     * @Serializer\Exclude
     */
    private $eventSeo = null;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $enabled;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $startDate = null;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $endDate = null;

    /**
     * @ORM\ManyToOne(targetEntity="Manuxi\SuluEventBundle\Entity\Location")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $location = null;

    /**
     * @var Collection<string, EventTranslation>
     *
     * @ORM\OneToMany(targetEntity="Manuxi\SuluEventBundle\Entity\EventTranslation", mappedBy="event", cascade={"ALL"}, indexBy="locale")
     */
    private $translations;

    private $locale = 'en';

    private $ext = [];

    /**
     * @ORM\ManyToOne(targetEntity="Sulu\Bundle\MediaBundle\Entity\MediaInterface")
     *
     * @Serializer\Exclude
     */
    private $image = null;

    public function __construct()
    {
        $this->enabled      = false;
        $this->translations = new ArrayCollection();
        $this->initExt();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

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

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getLocationId(): ?int
    {
        if (!$this->location) {
            return null;
        }

        return $this->location->getId();
    }

    public function getImage(): ?MediaInterface
    {
        return $this->image;
    }

    /**
     * @return array<string, mixed>|null
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("image")
     */
    public function getImageData(): ?array
    {
        if (!$this->image) {
            return null;
        }

        return [
            'id' => $this->image->getId(),
        ];
    }

    public function setImage(?MediaInterface $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="created")
     */
    public function getCreated(): ?\DateTime
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }

        return $translation->getCreated();
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

    public function setTitle(string $title): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setTitle($title);

        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="teaser")
     */
    public function getTeaser(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }

        return $translation->getTeaser();
    }

    public function setTeaser(string $teaser): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setTeaser($teaser);

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

    public function setDescription(string $description): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setDescription($description);

        return $this;
    }

    public function getEventSeo(): EventSeo
    {
        if (!$this->eventSeo instanceof EventSeo) {
            $this->eventSeo = new EventSeo();
            $this->eventSeo->setEvent($this);
        }

        return $this->eventSeo;
    }

    public function setEventSeo(?EventSeo $eventSeo): self
    {
        $this->eventSeo = $eventSeo;

        return $this;
    }

    public function getExt(): array
    {
        return $this->ext;
    }

    public function setExt(array $ext): self
    {
        $this->ext = $ext;

        return $this;
    }

    public function addExt(string $key, $value): self
    {
        $this->ext[$key] = $value;

        return $this;
    }

    public function hasExt(string $key): bool
    {
        return \array_key_exists($key, $this->ext);
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        $this->propagateLocale($locale);

        return $this;
    }

    /**
     * @return EventTranslation[]
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    protected function getTranslation(string $locale): ?EventTranslation
    {
        if (!$this->translations->containsKey($locale)) {
            return null;
        }

        return $this->translations->get($locale);
    }

    protected function createTranslation(string $locale): EventTranslation
    {
        $translation = new EventTranslation($this, $locale);
        $this->translations->set($locale, $translation);

        return $translation;
    }

    private function propagateLocale(string $locale): self
    {
        $eventSeo = $this->getEventSeo();
        $eventSeo->setLocale($locale);
        $this->initExt();

        return $this;
    }

    private function initExt(): self
    {
        if (!$this->hasExt('seo')) {
            $this->addExt('seo', $this->getEventSeo());
        }

        return $this;
    }
}
