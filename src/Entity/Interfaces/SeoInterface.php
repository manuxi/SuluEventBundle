<?php

namespace Manuxi\SuluEventBundle\Entity\Interfaces;

interface SeoInterface
{
    public function getId(): ?int;
    public function setId(?int $id);
    public function getHideInSitemap(): bool;
    public function setHideInSitemap(bool $hideInSitemap);
    public function getNoFollow(): bool;
    public function setNoFollow(bool $noFollow);
    public function getNoIndex(): bool;
    public function setNoIndex(bool $noIndex);
}
