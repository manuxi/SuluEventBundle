<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Component\Media\SystemCollections\SystemCollectionManagerInterface;

/**
 * Finds the media (an image of our own) for an id or a file name and checks that it exists before an event is linked
 * to it: a wrong id would otherwise fail the whole event on saving. A file name keeps the configuration portable: the
 * ids of the media differ between a test platform and the live site, the names do not.
 */
class MediaResolver
{
    /** @var array<string, int|null> */
    private array $resolved = [];

    /** @var array<string, int|null> */
    private array $collections = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ?SystemCollectionManagerInterface $systemCollectionManager = null,
    ) {
    }

    public function exists(int $id): bool
    {
        return null !== $this->entityManager->find(Media::class, $id);
    }

    /**
     * @param int|string $reference the id of a media or the name of its file ("event-topic-ai.jpg")
     *
     * @return int|null the id of the media; null if there is none
     */
    public function resolve(int|string $reference): ?int
    {
        $key = (string) $reference;
        if (\array_key_exists($key, $this->resolved)) {
            return $this->resolved[$key];
        }

        if (\is_int($reference) || ctype_digit($key)) {
            return $this->resolved[$key] = $this->exists((int) $reference) ? (int) $reference : null;
        }

        $id = $this->entityManager->createQuery(
            'SELECT media.id FROM ' . Media::class . ' media JOIN media.files file JOIN file.fileVersions version WITH version.version = file.version WHERE version.name = :name ORDER BY media.id ASC'
        )->setParameter('name', trim($key))->setMaxResults(1)->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_SINGLE_SCALAR);

        return $this->resolved[$key] = null !== $id ? (int) $id : null;
    }

    /**
     * Whether a media lies in one of the system collections (or in a folder below one), for example the folder of the
     * generated cards.
     *
     * @param string[] $systemCollectionKeys keys such as "events.cards"; a key that does not exist counts as no folder
     */
    public function isInSystemCollections(int $mediaId, array $systemCollectionKeys): bool
    {
        $ids = [];
        foreach ($systemCollectionKeys as $key) {
            $id = $this->systemCollectionId($key);
            if (null !== $id) {
                $ids[] = $id;
            }
        }
        if ([] === $ids) {
            return false;
        }

        $media  = $this->entityManager->find(Media::class, $mediaId);
        $folder = $media?->getCollection();
        for ($depth = 0; null !== $folder && $depth < 20; ++$depth) {
            if (\in_array((int) $folder->getId(), $ids, true)) {
                return true;
            }
            $folder = $folder->getParent();
        }

        return false;
    }

    private function systemCollectionId(string $key): ?int
    {
        if (null === $this->systemCollectionManager) {
            return null;
        }
        if (\array_key_exists($key, $this->collections)) {
            return $this->collections[$key];
        }

        try {
            return $this->collections[$key] = $this->systemCollectionManager->getSystemCollection($key);
        } catch (\Throwable) {
            return $this->collections[$key] = null;
        }
    }
}
