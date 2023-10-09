<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait ShowDateTrait
{

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $showDate = null;

    public function getShowDate(): bool
    {
        return $this->showDate ?? false;
    }

    public function setShowDate(bool $showDate): self
    {
        $this->showDate = $showDate;
        return $this;
    }

}
