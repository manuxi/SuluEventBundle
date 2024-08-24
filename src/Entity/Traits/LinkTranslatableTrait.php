<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

use JMS\Serializer\Annotation as Serializer;

trait LinkTranslatableTrait
{

    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    #[Serializer\VirtualProperty(name: 'link')]
    public function getLink(): ?array
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getLink();
    }

    public function setLink(?array $link): self
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            $translation = $this->createTranslation($this->getLocale());
        }

        $translation->setLink($link);
        return $this;
    }
}
