<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait SeoTrait
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id = null;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
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

}
