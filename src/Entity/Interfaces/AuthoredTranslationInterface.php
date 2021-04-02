<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

interface AuthoredTranslationInterface
{
    public function getAuthored(): ?\DateTime;
    public function setAuthored(\DateTime $authored);
}
