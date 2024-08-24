<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

use JMS\Serializer\Annotation as Serializer;

trait RouteTranslatableTrait
{
    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    #[Serializer\VirtualProperty(name: "route_path")]
    public function getRoutePath(): ?string
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getRoutePath();
    }

    public function setRoutePath(string $routePath): self
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            $translation = $this->createTranslation($this->getLocale());
        }

        $translation->setRoutePath($routePath);
        return $this;
    }
}
