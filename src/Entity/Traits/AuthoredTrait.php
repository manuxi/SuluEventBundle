<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

trait AuthoredTrait
{
    /**
     * @var \DateTime
     */
    protected $authored;

    public function getAuthored(): \DateTime
    {
        return $this->authored;
    }

    public function setAuthored(\DateTime $authored): self
    {
        $this->authored = $authored;
        return $this;
    }


}
