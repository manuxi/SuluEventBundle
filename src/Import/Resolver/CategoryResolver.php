<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\Resolver;

use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\CategoryBundle\Entity\CategoryRepositoryInterface;

/**
 * Finds the Sulu categories by their key. Categories are never created (they belong to the editors); an unknown key
 * is returned so the report can name it.
 */
class CategoryResolver
{
    public function __construct(private readonly CategoryRepositoryInterface $categoryRepository)
    {
    }

    /**
     * @param string[] $keys
     *
     * @return array{0: list<CategoryInterface>, 1: list<string>} the categories and the keys that do not exist
     */
    public function resolve(array $keys): array
    {
        $categories = [];
        $unknown    = [];
        foreach (array_unique(array_filter(array_map('trim', $keys), static fn (string $key): bool => '' !== $key)) as $key) {
            $category = $this->categoryRepository->findCategoryByKey($key);
            if ($category instanceof CategoryInterface) {
                $categories[] = $category;
            } else {
                $unknown[] = $key;
            }
        }

        return [$categories, $unknown];
    }
}
