<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait ShowAuthorTrait
{

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $showAuthor = null;

    public function getShowAuthor(): bool
    {
        return $this->showAuthor ?? false;
    }

    public function setShowAuthor(bool $showAuthor): self
    {
        $this->showAuthor = $showAuthor;
        return $this;
    }

}
