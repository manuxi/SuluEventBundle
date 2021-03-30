<?php

namespace Manuxi\SuluEventBundle\Entity\Traits;

use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

trait ExcerptTranslatableTrait
{
    private $locale = 'en';

    abstract protected function getTranslation(string $locale);
    abstract protected function createTranslation(string $locale);

    /**
     * @Serializer\VirtualProperty(name="title")
     */
    public function getTitle(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getTitle();
    }

    public function setTitle(?string $title): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setTitle($title);
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="more")
     */
    public function getMore(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getMore();
    }

    public function setMore(?string $more): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setMore($more);
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="description")
     */
    public function getDescription(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getDescription();
    }

    public function setDescription(?string $description): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setDescription($description);
        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return CategoryInterface[]
     * @Serializer\VirtualProperty(name="categories")
     */
    public function getCategories(): ?array
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getCategories();
    }

    public function addCategory(CategoryInterface $category): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->addCategory($category);
        return $this;
    }

    /**
     * @return TagInterface[]
     * @Serializer\VirtualProperty(name="tags")
     */
    public function getTags(): ?array
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getTags();
    }

    public function addTag(TagInterface $tag)
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->addTag($tag);
        return $this;
    }
}
