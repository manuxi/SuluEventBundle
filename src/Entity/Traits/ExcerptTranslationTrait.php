<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

trait ExcerptTranslationTrait
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $locale = 'en';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $more;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="Sulu\Bundle\CategoryBundle\Entity\Category")
     * @JoinTable(name="app_event_excerpt_categories",
     *      joinColumns={@ORM\JoinColumn(name="excerpt_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")}
     *      )
     */
    private $categories;

    /**
     * @ORM\ManyToMany(targetEntity="Sulu\Bundle\TagBundle\Tag\TagInterface")
     * @JoinTable(name="app_event_excerpt_tags",
     *      joinColumns={@ORM\JoinColumn(name="excerpt_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     *      )
     */
    private $tags;

    /**
     * @TODO
     */
    private $segments;

    /**
     * @ORM\ManyToMany(targetEntity="Sulu\Bundle\MediaBundle\Entity\MediaInterface")
     * @JoinTable(name="app_event_excerpt_icons",
     *      joinColumns={@ORM\JoinColumn(name="excerpt_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="icon_id", referencedColumnName="id")}
     *      )
     */
    private $icons;

    /**
     * @ORM\ManyToMany(targetEntity="Sulu\Bundle\MediaBundle\Entity\MediaInterface")
     * @JoinTable(name="app_event_excerpt_images",
     *      joinColumns={@ORM\JoinColumn(name="excerpt_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="image_id", referencedColumnName="id")}
     *      )
     */
    private $images;

    private function initExcerptTranslationTrait(): void
    {
        $this->prepareCategories();
        $this->prepareTags();
        $this->prepareIcons();
        $this->prepareImages();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getMore(): ?string
    {
        return $this->more;
    }

    public function setMore(?string $more): self
    {
        $this->more = $more;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    // categories

    private function prepareCategories(): void
    {
        if (null === $this->categories) {
            $this->categories = new ArrayCollection();;
        }
    }

    public function addCategory(CategoryInterface $category): self
    {
        $this->prepareCategories();
        $this->categories[] = $category;
        return $this;
    }

    public function removeCategory(CategoryInterface $category): self
    {
        $this->categories->removeElement($category);
        return $this;
    }

    public function removeCategories(): self
    {
        $this->categories->clear();
        return $this;
    }

    public function getCategories(): ?Collection
    {
        return $this->categories;
    }

    public function getCategoryIds(): array
    {
        $categories = [];

        if (null !== $this->getCategories()) {
            foreach ($this->getCategories() as $category) {
                $categories[] = $category->getId();
            }
        }

        return $categories;
    }

    // tags

    private function prepareTags(): void
    {
        if (null === $this->tags) {
            $this->tags = new ArrayCollection();;
        }
    }

    public function addTag(TagInterface $tag): self
    {
        $this->prepareTags();
        $this->tags[] = $tag;
        return $this;
    }

    public function removeTag(TagInterface $tag): self
    {
        $this->tags->removeElement($tag);
        return $this;
    }

    public function removeTags(): self
    {
        $this->tags->clear();
        return $this;
    }

    public function getTags(): ?Collection
    {
        return $this->tags;
    }

    public function getTagNames(): array
    {
        $tags = [];

        if (null !== $this->getTags()) {
            foreach ($this->getTags() as $tag) {
                $tags[] = $tag->getName();
            }
        }

        return $tags;
    }

    // icons

    private function prepareIcons(): void
    {
        if (null === $this->icons) {
            $this->icons = new ArrayCollection();;
        }
    }

    public function addIcon(MediaInterface $media): self
    {
        $this->prepareIcons();
        $this->icons[] = $media;
        return $this;
    }

    public function removeIcon(MediaInterface $media): self
    {
        $this->icons->removeElement($media);
        return $this;
    }

    public function getIcons(): ?Collection
    {
        return $this->icons;
    }

    /**
     * @Serializer\VirtualProperty(name="icon")
     */
    public function getIconIds(): array
    {
        $icons = [];
        $icons['ids'] = [];

        if (null !== $this->getIcons()) {
            foreach ($this->getIcons() as $icon) {
                $icons['ids'][] = $icon->getId();
            }
        }
        return $icons;
    }

    public function removeIcons(): self
    {
        $this->icons->clear();
        return $this;
    }

    // images

    private function prepareImages(): void
    {
        if (null === $this->images) {
            $this->images = new ArrayCollection();;
        }
    }

    public function addImage(MediaInterface $media): self
    {
        $this->prepareImages();
        $this->images[] = $media;
        return $this;
    }

    public function removeImage(MediaInterface $media): self
    {
        $this->images->removeElement($media);
        return $this;
    }

    public function getImages(): ?Collection
    {
        return $this->images;
    }

    public function getImageIds(): array
    {
        $images = [];
        $images['ids'] = [];

        if (null !== $this->getImages()) {
            foreach ($this->getImages() as $image) {
                $images['ids'][] = $image->getId();
            }
        }
        return $images;
    }

    public function removeImages(): self
    {
        $this->images->clear();
        return $this;
    }
}
