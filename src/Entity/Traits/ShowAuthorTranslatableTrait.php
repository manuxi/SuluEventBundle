<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

use JMS\Serializer\Annotation as Serializer;

trait ShowAuthorTranslatableTrait
{
    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    /**
     * @Serializer\VirtualProperty(name="show_author")
     */
    public function getShowAuthor(): ?bool
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getShowAuthor();
    }

    public function setShowAuthor(bool $showAuthor): self
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            $translation = $this->createTranslation($this->getLocale());
        }

        $translation->setShowAuthor($showAuthor);
        return $this;
    }
}
