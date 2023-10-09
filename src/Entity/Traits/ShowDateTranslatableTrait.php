<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

use JMS\Serializer\Annotation as Serializer;

trait ShowDateTranslatableTrait
{
    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    /**
     * @Serializer\VirtualProperty(name="show_date")
     */
    public function getShowDate(): ?bool
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getShowDate();
    }

    public function setShowDate(bool $showDate): self
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            $translation = $this->createTranslation($this->getLocale());
        }

        $translation->setShowDate($showDate);
        return $this;
    }
}
