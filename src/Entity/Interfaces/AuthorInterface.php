<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

use Sulu\Component\Security\Authentication\UserInterface;

interface AuthorInterface
{
    public function getAuthor(): ?UserInterface;
    public function setAuthor(?UserInterface $author);
}
