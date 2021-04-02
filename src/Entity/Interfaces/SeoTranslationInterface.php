<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

interface SeoTranslationInterface
{
    public function getId(): ?int;
    public function getLocale(): string;
    public function setLocale(?string $locale);
    public function getTitle(): ?string;
    public function setTitle(?string $title);
    public function getCanonicalUrl(): ?string;
    public function setCanonicalUrl(?string $canonicalUrl);
    public function getKeywords(): ?string;
    public function setKeywords(?string $keywords);
    public function getDescription(): ?string;
    public function setDescription(?string $description);
}
