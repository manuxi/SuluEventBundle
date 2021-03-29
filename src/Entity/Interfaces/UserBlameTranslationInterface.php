<?php

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

use Sulu\Component\Security\Authentication\UserInterface;

interface UserBlameTranslationInterface
{
    /**
     * Returns the user id from the translation object which created it.
     */
    public function getCreator(): ?int;

    /**
     * Alias for getCreator().
     */
    public function getAuthor(): ?int;

    /**
     * Returns the user id from the translation object that changed it the last time.
     */
    public function getChanger(): ?int;
}
