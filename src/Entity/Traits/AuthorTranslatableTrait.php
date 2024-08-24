<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;

trait AuthorTranslatableTrait
{
    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    #[Serializer\VirtualProperty(name: "author")]
    public function getAuthor(): ?int
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getAuthor() ? $translation->getAuthor()->getId() : null;
    }

    public function setAuthor(?ContactInterface $author): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setAuthor($author);
        return $this;
    }
}
