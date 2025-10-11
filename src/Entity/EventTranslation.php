<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluEventBundle\Entity\Interfaces\AuditableInterface;
use Manuxi\SuluEventBundle\Entity\Traits\AuditableTrait;
use Manuxi\SuluEventBundle\Entity\Traits\ImageTrait;
use Manuxi\SuluEventBundle\Entity\Traits\LinkTrait;
use Manuxi\SuluEventBundle\Entity\Traits\PdfTrait;
use Manuxi\SuluEventBundle\Entity\Traits\PublishedTrait;
use Manuxi\SuluEventBundle\Entity\Traits\RouteTrait;
use Manuxi\SuluEventBundle\Entity\Traits\ShowAuthorTrait;
use Manuxi\SuluEventBundle\Entity\Traits\ShowDateTrait;
use Manuxi\SuluEventBundle\Repository\EventTranslationRepository;

#[ORM\Entity(repositoryClass: EventTranslationRepository::class)]
#[ORM\Table(name: 'app_event_translation')]
class EventTranslation implements AuditableInterface
{
    use AuditableTrait;
    use RouteTrait;
    use ImageTrait;
    use LinkTrait;
    use PdfTrait;
    use PublishedTrait;
    use ShowAuthorTrait;
    use ShowDateTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $subtitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $footer = null;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'translations')]
        #[ORM\JoinColumn(nullable: false)]
        private Event $event,
        #[ORM\Column(type: Types::STRING, length: 5)]
        private string $locale,
    ) {
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
}
