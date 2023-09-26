<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

trait ImageTranslatableTrait
{

    /**
     * @Serializer\VirtualProperty(name="image")
     */
    public function getImage(): ?MediaInterface
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getImage();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("image")
     */
    public function getImageData(): ?array
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getImageData();
    }

    public function setImage(?MediaInterface $image): self
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            $translation = $this->createTranslation($this->getLocale());
        }

        $translation->setImage($image);
        return $this;
    }
}
