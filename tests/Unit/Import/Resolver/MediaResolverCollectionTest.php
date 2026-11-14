<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Import\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Import\Resolver\MediaResolver;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\MediaBundle\Entity\Collection;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Component\Media\SystemCollections\SystemCollectionManagerInterface;

/**
 * Placeholder folders: a media is a placeholder if it lies in a system collection of the list or below one.
 */
class MediaResolverCollectionTest extends TestCase
{
    public function testMediaInTheFolderOrBelowIt(): void
    {
        $resolver = $this->resolver(['events.cards' => 10, 'events.other' => 11]);

        $this->assertTrue($resolver->isInSystemCollections(1, ['events.cards']), 'directly in the folder');
        $this->assertTrue($resolver->isInSystemCollections(2, ['events.cards']), 'in a folder below it');
        $this->assertFalse($resolver->isInSystemCollections(3, ['events.cards']), 'in another folder');
        $this->assertTrue($resolver->isInSystemCollections(3, ['events.cards', 'events.other']));
        $this->assertFalse($resolver->isInSystemCollections(4, ['events.cards']), 'no such media');
    }

    public function testUnknownKeyOrNothingConfiguredIsNoPlaceholder(): void
    {
        $resolver = $this->resolver(['events.cards' => 10]);

        $this->assertFalse($resolver->isInSystemCollections(1, ['does.not.exist']));
        $this->assertFalse($resolver->isInSystemCollections(1, []));
        $this->assertFalse((new MediaResolver($this->createStub(EntityManagerInterface::class)))->isInSystemCollections(1, ['events.cards']), 'without the manager of Sulu');
    }

    /**
     * @param array<string, int> $systemCollections key => id
     */
    private function resolver(array $systemCollections): MediaResolver
    {
        // media 1 in folder 10, media 2 in folder 20 below 10, media 3 in folder 11
        $top   = $this->collection(10, null);
        $below = $this->collection(20, $top);
        $other = $this->collection(11, null);

        $media = [1 => $this->media($top), 2 => $this->media($below), 3 => $this->media($other)];

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('find')->willReturnCallback(static fn (string $class, int $id): ?Media => $media[$id] ?? null);

        $manager = $this->createStub(SystemCollectionManagerInterface::class);
        $manager->method('getSystemCollection')->willReturnCallback(static fn (string $key): int => $systemCollections[$key] ?? throw new \RuntimeException('unknown'));

        return new MediaResolver($entityManager, $manager);
    }

    private function collection(int $id, ?Collection $parent): Collection
    {
        $collection = $this->createStub(Collection::class);
        $collection->method('getId')->willReturn($id);
        $collection->method('getParent')->willReturn($parent);

        return $collection;
    }

    private function media(Collection $collection): Media
    {
        $media = $this->createStub(Media::class);
        $media->method('getCollection')->willReturn($collection);

        return $media;
    }
}
