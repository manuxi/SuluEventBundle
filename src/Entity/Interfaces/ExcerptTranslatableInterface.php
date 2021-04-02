<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

interface ExcerptTranslatableInterface
{
    public function getLocale(): string;
    public function setLocale(string $locale);
    public function getTitle(): ?string;
    public function setTitle(?string $title);
    public function getMore(): ?string;
    public function setMore(?string $more);
    public function getDescription(): ?string;
    public function setDescription(?string $description);
    public function getCategories(): ?array;
    public function addCategory(CategoryInterface $category);
    public function removeCategories();
    public function getTags(): ?array;
    public function addTag(TagInterface $tag);
    public function removeTags();
    public function getIcon(): ?array;
    public function addIcon(MediaInterface $icon);
    public function removeIcons();
    public function getImages(): ?array;
    public function addImage(MediaInterface $image);
    public function removeImages();
}
