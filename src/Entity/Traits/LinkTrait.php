<?php

namespace Manuxi\SuluEventBundle\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait LinkTrait
{

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $link = null;

    public function getLink(): ?array
    {
        return $this->link;
    }

    public function setLink(?array $link): self
    {
        $this->link = $link;
        return $this;
    }
}
