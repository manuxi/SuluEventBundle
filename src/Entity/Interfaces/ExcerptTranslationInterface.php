<?php

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

use Doctrine\Common\Collections\Collection;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

interface ExcerptTranslationInterface
{
    public function getId(): ?int;
    public function getLocale(): string;
    public function setLocale(?string $locale);
    public function getTitle(): ?string;
    public function setTitle(?string $title);
    public function getMore(): ?string;
    public function setMore(?string $more);
    public function getDescription(): ?string;
    public function setDescription(?string $description);
    public function addCategory(CategoryInterface $category);
    public function removeCategory(CategoryInterface $category): void;
    public function removeCategories();
    public function getCategories(): Collection;
    public function addTag(TagInterface $tag);
    public function removeTag(TagInterface $tag): void;
    public function removeTags();
    public function getTags(): Collection;
    public function getTagNameArray(): array;
    public function addIcon(MediaInterface $media);
    public function removeIcon(MediaInterface $media);
    public function getIcons(): Collection;
    public function removeIcons();
    public function addImage(MediaInterface $media);
    public function removeImage(MediaInterface $media);
    public function getImages(): Collection;
    public function removeImages();
}
