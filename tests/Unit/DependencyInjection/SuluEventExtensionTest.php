<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\DependencyInjection;

use Manuxi\SuluEventBundle\DependencyInjection\SuluEventExtension;
use Manuxi\SuluEventBundle\Repository\EventDimensionContentRepository;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SuluEventExtensionTest extends TestCase
{
    /**
     * @dataProvider provideGeneratedRepositoryServiceIds
     */
    public function testLoadAliasesGeneratedRepositoryServiceToFqcnRepository(string $serviceId, string $repositoryClass): void
    {
        $container = new ContainerBuilder();
        $extension = new SuluEventExtension();

        $extension->load([], $container);

        // Sulu's PersistenceExtensionTrait::configurePersistence() generates a Definition
        // for this service id with a (EntityManager, ClassMetadata) constructor signature,
        // which does not match our ServiceEntityRepository-based repositories and would
        // fail `bin/console lint:container`. It must be replaced by an alias instead.
        self::assertFalse(
            $container->hasDefinition($serviceId),
            \sprintf('Service "%s" must not keep the broken generated Definition.', $serviceId)
        );

        self::assertTrue($container->hasAlias($serviceId));

        $alias = $container->getAlias($serviceId);
        self::assertSame($repositoryClass, (string) $alias);
        self::assertTrue($alias->isPublic());
    }

    /**
     * @return iterable<string, array{0: string, 1: class-string}>
     */
    public static function provideGeneratedRepositoryServiceIds(): iterable
    {
        yield 'event' => ['sulu.repository.event', EventRepository::class];
        yield 'event_dimension_content' => ['sulu.repository.event_dimension_content', EventDimensionContentRepository::class];
        yield 'location' => ['sulu.repository.location', LocationRepository::class];
    }
}
