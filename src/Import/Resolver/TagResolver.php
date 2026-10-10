<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;
use Sulu\Bundle\TagBundle\Tag\TagRepositoryInterface;

/**
 * Finds the Sulu tags by name and creates the ones that do not exist yet.
 */
class TagResolver
{
    /** @var array<string, TagInterface> */
    private array $created = [];

    public function __construct(
        private readonly TagRepositoryInterface $tagRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Forgets the tags created so far (after the entity manager was cleared).
     */
    public function reset(): void
    {
        $this->created = [];
    }

    /**
     * @param string[] $names
     * @param bool     $persist false for a dry run: nothing is created
     *
     * @return list<TagInterface> the tags; for a dry run only the existing ones
     */
    public function resolve(array $names, bool $persist = true): array
    {
        $tags = [];
        foreach (array_unique(array_filter(array_map('trim', $names), static fn (string $name): bool => '' !== $name)) as $name) {
            $key = mb_strtolower($name);
            $tag = $this->created[$key] ?? $this->tagRepository->findTagByName($name);

            if (!$tag instanceof TagInterface && $persist) {
                $tag = $this->tagRepository->createNew();
                $tag->setName($name);
                $this->entityManager->persist($tag);
                $this->created[$key] = $tag;
            }

            if ($tag instanceof TagInterface) {
                $tags[] = $tag;
            }
        }

        return $tags;
    }
}
