<?php

namespace Manuxi\SuluEventBundle\Entity\Traits;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
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
    public function getCategories(): ?Collection
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

    public function removeCategories(): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->removeCategories();
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="tags")
     */
    public function getTags(): array
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        return $translation->getTagNameArray();
    }

    public function addTag(TagInterface $tag): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->addTag($tag);
        return $this;
    }

    public function removeTags(): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->removeTags();
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="icon")
     */
    public function getIcons(): ?array
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getIconIdsArray();
    }

    public function addIcon(MediaInterface $icon): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->addIcon($icon);
        return $this;
    }

    public function removeIcons(): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->removeIcons();
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="images")
     */
    public function getImages(): ?array
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getImageIdsArray();
    }

    public function addImage(MediaInterface $image): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->addImage($image);
        return $this;
    }

    public function removeImages(): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->removeImages();
        return $this;
    }

}
