<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

trait PdfTranslatableTrait
{

    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    /**
     * @Serializer\VirtualProperty(name="pdf")
     */
    public function getPdf(): ?MediaInterface
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getPdf();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("pdf")
     */
    public function getPdfData(): ?array
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getPdfData();
    }

    public function setPdf(?MediaInterface $pdf): self
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            $translation = $this->createTranslation($this->getLocale());
        }

        $translation->setPdf($pdf);
        return $this;
    }
}
