<?php

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

interface AuthoredTranslationInterface
{
    public function getAuthored(): ?\DateTime;
    public function setAuthored(\DateTime $authored);
}
