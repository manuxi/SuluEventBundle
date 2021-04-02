<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

use Sulu\Component\Security\Authentication\UserInterface;

interface AuthorTranslationInterface
{
    public function getAuthor(): ?int;
    public function setAuthor(UserInterface $author);
}
