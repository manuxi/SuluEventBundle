<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

use Doctrine\Common\Collections\Collection;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

interface ExcerptTranslationInterface
{
    public function getId(): ?int;
    public function getLocale(): string;
    public function setLocale(string $locale);
    public function getTitle(): ?string;
    public function setTitle(?string $title);
    public function getMore(): ?string;
    public function setMore(?string $more);
    public function getDescription(): ?string;
    public function setDescription(?string $description);
    public function addCategory(CategoryInterface $category);
    public function removeCategory(CategoryInterface $category);
    public function removeCategories();
    public function getCategories(): ?Collection;
    public function getCategoryIds(): array;
    public function addTag(TagInterface $tag);
    public function removeTag(TagInterface $tag);
    public function removeTags();
    public function getTags(): ?Collection;
    public function getTagNames(): array;
    public function addIcon(MediaInterface $media);
    public function removeIcon(MediaInterface $media);
    public function getIcons(): ?Collection;
    public function getIconIds(): array;
    public function removeIcons();
    public function addImage(MediaInterface $media);
    public function removeImage(MediaInterface $media);
    public function getImages(): ?Collection;
    public function getImageIds(): array;
    public function removeImages();
}
