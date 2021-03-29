<?php

namespace Manuxi\SuluEventBundle\Entity\Traits;

use JMS\Serializer\Annotation as Serializer;

trait UserBlameTranslationTrait
{
    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    /**
     * @Serializer\VirtualProperty(name="creator")
     */
    public function getCreator(): ?int
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getCreator()->getId();
    }

    /**
     * @Serializer\VirtualProperty(name="author")
     */
    public function getAuthor(): ?int
    {
        return $this->getCreator();
    }

    /**
     * @Serializer\VirtualProperty(name="changer")
     */
    public function getChanger(): ?int
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getChanger()->getId();
    }
}
