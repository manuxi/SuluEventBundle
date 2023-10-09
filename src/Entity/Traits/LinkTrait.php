<?php

namespace Manuxi\SuluEventBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

trait LinkTrait
{

    /**
     * @ORM\Column(type="array", nullable=true)
     */
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
