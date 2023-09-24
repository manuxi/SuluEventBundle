<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

use DateTime;
use JMS\Serializer\Annotation as Serializer;

trait AuthoredTranslatableTrait
{
    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    /**
     * @Serializer\VirtualProperty(name="authored")
     */
    public function getAuthored(): ?DateTime
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getAuthored();
    }

    public function setAuthored(DateTime $authored): self
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            $translation = $this->createTranslation($this->getLocale());
        }

        $translation->setAuthored($authored);
        return $this;
    }
}
