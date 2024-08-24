<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait AuthoredTrait
{

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTime $authored = null;

    public function getAuthored(): ?DateTime
    {
        return $this->authored;
    }

    public function setAuthored(?DateTime $authored): self
    {
        $this->authored = $authored;
        return $this;
    }


}
