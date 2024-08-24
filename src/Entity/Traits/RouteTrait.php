<?php

namespace Manuxi\SuluEventBundle\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait RouteTrait
{

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $routePath;

    public function getRoutePath(): string
    {
        return $this->routePath ?? '';
    }

    public function setRoutePath(string $routePath): self
    {
        $this->routePath = $routePath;
        return $this;
    }

}
