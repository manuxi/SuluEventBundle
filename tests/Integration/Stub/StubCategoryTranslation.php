<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration\Stub;

/**
 * The name of a category in one locale (not an entity).
 */
final class StubCategoryTranslation
{
    public function __construct(private readonly string $translation)
    {
    }

    public function getTranslation(): string
    {
        return $this->translation;
    }
}
