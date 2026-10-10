<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\MediaBundle\Entity\Media;

/**
 * Checks that a media (an image of our own) exists before an event is linked to it: a wrong id would otherwise fail
 * the whole event on saving.
 */
class MediaResolver
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function exists(int $id): bool
    {
        return null !== $this->entityManager->find(Media::class, $id);
    }
}
