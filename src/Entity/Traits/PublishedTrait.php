<?php

namespace Manuxi\SuluEventBundle\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait PublishedTrait
{
    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $published = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $publishedAt = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $publishedState = null;

    public function isPublished(): ?bool
    {
        return $this->published ?? false;
    }

    public function getPublished(): ?bool
    {
        return $this->published ?? false;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;
        $this->publishedState = $published;
        if (true === $published) {
            $this->setPublishedAt(new \DateTime());
        } else {
            $this->setPublishedAt(null);
        }

        return $this;
    }

    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTime $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getPublishedState(): ?int
    {
        return $this->publishedState;
    }

    public function setPublishedState(?int $publishedState): self
    {
        $this->publishedState = $publishedState;

        return $this;
    }
}
