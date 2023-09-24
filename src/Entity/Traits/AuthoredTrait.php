<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

use DateTime;

trait AuthoredTrait
{

    protected DateTime $authored;

    public function getAuthored(): ?DateTime
    {
        return $this->authored;
    }

    public function setAuthored(DateTime $authored): self
    {
        $this->authored = $authored;
        return $this;
    }


}
