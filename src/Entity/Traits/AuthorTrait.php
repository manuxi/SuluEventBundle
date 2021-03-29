<?php

namespace Manuxi\SuluEventBundle\Entity\Traits;

use JMS\Serializer\Annotation as Serializer;
use Sulu\Component\Security\Authentication\UserInterface;

trait AuthorTrait
{
    /**
     * @var UserInterface|null
     */
    protected $author;

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
