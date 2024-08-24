<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

use DateTime;
use JMS\Serializer\Annotation as Serializer;

trait UrlTranslatableTrait
{

    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    #[Serializer\VirtualProperty(name: "url")]
    public function getUrl(): ?string
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getUrl();
    }

    public function setUrl(?string $url): self
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            $translation = $this->createTranslation($this->getLocale());
        }

        $translation->setUrl($url);
        return $this;
    }
}
