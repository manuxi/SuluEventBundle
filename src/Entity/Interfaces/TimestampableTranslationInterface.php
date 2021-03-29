<?php

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

interface TimestampableTranslationInterface
{
    /**
     * Returns the created timestamp of the translated object.
     */
    public function getCreated(): ?\DateTime;

    /**
     * Returns the changed timestamp of the translated object.
     */
    public function getChanged(): ?\DateTime;
}
