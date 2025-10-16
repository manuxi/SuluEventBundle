<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluSharedToolsBundle\Entity\Interfaces\AuditableTranslatableInterface;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\AuditableTranslatableTrait;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\ImageTranslatableTrait;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\LinkTranslatableTrait;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\PdfTranslatableTrait;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\PublishedTranslatableTrait;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\RoutePathTranslatableTrait;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\ShowAuthorTranslatableTrait;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\ShowDateTranslatableTrait;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'app_event')]
class Event implements AuditableTranslatableInterface
{
    use AuditableTranslatableTrait;
    use ImageTranslatableTrait;
    use LinkTranslatableTrait;
    use PdfTranslatableTrait;
    use PublishedTranslatableTrait;
    use RoutePathTranslatableTrait;
    use ShowAuthorTranslatableTrait;
    use ShowDateTranslatableTrait;

    public const RESOURCE_KEY = 'events';
    public const FORM_KEY = 'event_details';
    public const LIST_KEY = 'events';
    public const SECURITY_CONTEXT = 'sulu.events.events';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[Serializer\Exclude]
    #[ORM\OneToOne(mappedBy: 'event', targetEntity: EventSeo::class, cascade: ['persist', 'remove'])]
    private ?EventSeo $eventSeo = null;

    #[Serializer\Exclude]
    #[ORM\OneToOne(mappedBy: 'event', targetEntity: EventExcerpt::class, cascade: ['persist', 'remove'])]
    private ?EventExcerpt $eventExcerpt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $images = null;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Location $location = null;

    #[Serializer\Exclude]
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: EventTranslation::class, cascade: ['all'], fetch: 'EXTRA_LAZY', indexBy: 'locale')]
    private Collection $translations;

    private string $locale = 'de';

    private array $ext = [];

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->initExt();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getIdentifier(): string
    {
        // return hash('sha256', $this->id.'|'.$this->locale);
        return $this->id.'-'.$this->locale;
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

    #[Serializer\VirtualProperty]
    public function getLocationId(): ?int
    {
        if (!$this->location) {
            return null;
        }

        return $this->location->getId();
    }

    #[Serializer\VirtualProperty(name: 'title')]
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

    #[Serializer\VirtualProperty(name: 'subtitle')]
    public function getSubtitle(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }

        return $translation->getSubtitle();
    }

    public function setSubtitle(?string $subtitle): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setSubtitle($subtitle);

        return $this;
    }

    #[Serializer\VirtualProperty(name: 'summary')]
    public function getSummary(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }

        return $translation->getSummary();
    }

    public function setSummary(?string $summary): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setSummary($summary);

        return $this;
    }

    #[Serializer\VirtualProperty(name: 'text')]
    public function getText(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }

        return $translation->getText();
    }

    public function setText(string $text): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setText($text);

        return $this;
    }

    #[Serializer\VirtualProperty(name: 'footer')]
    public function getFooter(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }

        return $translation->getFooter();
    }

    public function setFooter(?string $footer): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setFooter($footer);

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

    public function getEventExcerpt(): EventExcerpt
    {
        if (!$this->eventExcerpt instanceof EventExcerpt) {
            $this->eventExcerpt = new EventExcerpt();
            $this->eventExcerpt->setEvent($this);
        }

        return $this->eventExcerpt;
    }

    public function setEventExcerpt(?EventExcerpt $eventExcerpt): self
    {
        $this->eventExcerpt = $eventExcerpt;

        return $this;
    }

    #[Serializer\VirtualProperty(name: 'ext')]
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
        $eventExcerpt = $this->getEventExcerpt();
        $eventExcerpt->setLocale($locale);
        $this->initExt();

        return $this;
    }

    private function initExt(): self
    {
        if (!$this->hasExt('seo')) {
            $this->addExt('seo', $this->getEventSeo());
        }
        if (!$this->hasExt('excerpt')) {
            $this->addExt('excerpt', $this->getEventExcerpt());
        }

        return $this;
    }

    #[Serializer\VirtualProperty(name: 'availableLocales')]
    public function getAvailableLocales(): array
    {
        return \array_values($this->translations->getKeys());
    }

    public function copyToLocale(string $locale): self
    {
        if ($currentTranslation = $this->getTranslation($this->getLocale())) {
            $newTranslation = clone $currentTranslation;
            $newTranslation->setLocale($locale);
            $this->translations->set($locale, $newTranslation);

            // copy ext also...
            foreach ($this->ext as $translatable) {
                $translatable->copyToLocale($locale);
            }

            $this->setLocale($locale);
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getImages(): ?array
    {
        return $this->images ?? [];
    }

    public function setImages(?array $images): void
    {
        $this->images = $images;
    }
}
