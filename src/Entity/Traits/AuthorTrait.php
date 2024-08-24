<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;

trait AuthorTrait
{

    #[ORM\ManyToOne(targetEntity: ContactInterface::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    protected ?ContactInterface $author = null;

    public function getAuthor(): ?ContactInterface
    {
        return $this->author;
    }

    public function setAuthor(?ContactInterface $author): self
    {
        $this->author = $author;
        return $this;
    }

}
