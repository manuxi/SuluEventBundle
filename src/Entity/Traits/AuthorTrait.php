<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

use Sulu\Component\Security\Authentication\UserInterface;

trait AuthorTrait
{

    protected ?UserInterface $author = null;

    public function getAuthor(): ?UserInterface
    {
        return $this->author;
    }

    public function setAuthor(?UserInterface $author): self
    {
        $this->author = $author;
        return $this;
    }

}
