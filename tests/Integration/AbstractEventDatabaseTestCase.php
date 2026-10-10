<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\EventImport;
use Manuxi\SuluEventBundle\Entity\EventImportRun;
use Manuxi\SuluEventBundle\Entity\EventRecurrence;
use Manuxi\SuluEventBundle\Entity\EventSocialSettings;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Tests\Integration\Stub\StubCategory;
use Manuxi\SuluEventBundle\Tests\Integration\Stub\StubEntity;
use Manuxi\SuluEventBundle\Tests\Integration\Stub\StubTag;
use PHPUnit\Framework\TestCase;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Infrastructure\Doctrine\DimensionContentQueryEnhancer;

/**
 * Runs the real mapping of the bundle against an in-memory SQLite database. The Sulu entities the mapping only
 * references (user, media, contact, route, category, tag) are replaced by small stubs. Provides a few categories,
 * tags and locations and a helper to create published events.
 */
abstract class AbstractEventDatabaseTestCase extends TestCase
{
    protected EntityManager $entityManager;
    protected EventRepository $repository;

    /** @var array<string, StubCategory> */
    protected array $categories = [];

    /** @var array<string, StubTag> */
    protected array $tags = [];

    /** @var array<string, Location> */
    protected array $locations = [];

    protected function setUp(): void
    {
        $this->entityManager = $this->createEntityManager();

        $registry = $this->createStub(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($this->entityManager);

        $this->repository = new EventRepository($registry, $this->createStub(DimensionContentQueryEnhancer::class));

        foreach (['ai', 'games', 'web'] as $key) {
            $this->categories[$key] = new StubCategory();
            $this->entityManager->persist($this->categories[$key]);
        }

        foreach (['symfony', 'llm'] as $name) {
            $this->tags[$name] = new StubTag($name);
            $this->entityManager->persist($this->tags[$name]);
        }

        foreach (['Messe Berlin' => 'DE', 'Online' => null, 'Convention Center' => 'us'] as $name => $countryCode) {
            $location = new Location();
            $location->setName($name);
            $location->setCountryCode($countryCode);
            $this->entityManager->persist($location);
            $this->locations[$name] = $location;
        }

        $this->entityManager->flush();
    }

    /**
     * @param string[] $categories
     * @param string[] $tags
     */
    protected function createEvent(
        string $uuid,
        string $locale,
        string $start,
        ?string $end = null,
        array $categories = [],
        array $tags = [],
        ?string $location = null,
        string $stage = DimensionContentInterface::STAGE_LIVE,
        ?string $type = null,
        string $attendanceMode = 'offline',
        ?bool $isFree = null,
    ): void {
        $event = new Event($uuid);

        $content = new EventDimensionContent($event);
        $content->setStage($stage);
        $content->setLocale($locale);
        $content->setTitle('Event ' . $uuid);
        $content->setStartDate(new \DateTimeImmutable($start));
        $content->setEndDate(null === $end ? null : new \DateTimeImmutable($end));
        $content->setType($type);
        $content->setAttendanceMode($attendanceMode);
        $content->setIsFree($isFree);
        $content->setExcerptCategories(array_map(fn (string $key) => $this->categories[$key], $categories));
        $content->setExcerptTags(array_map(fn (string $name) => $this->tags[$name], $tags));
        if (null !== $location) {
            $content->setLocation($this->locations[$location]);
        }

        $event->addDimensionContent($content);

        $this->entityManager->persist($event);
        $this->entityManager->flush();
    }

    /**
     * @param Event[] $events
     *
     * @return string[]
     */
    protected function uuids(array $events): array
    {
        return array_map(static fn (Event $event) => $event->getUuid(), $events);
    }

    protected function createEntityManager(): EntityManager
    {
        $xmlDriver  = new SimplifiedXmlDriver([__DIR__ . '/../../src/Resources/config/doctrine' => 'Manuxi\SuluEventBundle\Entity']);
        $stubDriver = new AttributeDriver([__DIR__ . '/Stub']);

        $driverChain = new MappingDriverChain();
        $driverChain->addDriver($xmlDriver, 'Manuxi\SuluEventBundle\Entity');
        $driverChain->addDriver($stubDriver, 'Manuxi\SuluEventBundle\Tests\Integration\Stub');

        $config = ORMSetup::createConfiguration(true);
        $config->setMetadataDriverImpl($driverChain);

        $resolver = new ResolveTargetEntityListener();
        foreach ([
            'Sulu\Bundle\SecurityBundle\Entity\User',
            'Sulu\Component\Security\Authentication\UserInterface',
            'Sulu\Bundle\MediaBundle\Entity\Media',
            'Sulu\Bundle\MediaBundle\Entity\MediaInterface',
            'Sulu\Bundle\ContactBundle\Entity\ContactInterface',
            'Sulu\Route\Domain\Model\Route',
        ] as $referenced) {
            $resolver->addResolveTargetEntity($referenced, StubEntity::class, []);
        }
        $resolver->addResolveTargetEntity('Sulu\Bundle\CategoryBundle\Entity\CategoryInterface', StubCategory::class, []);
        $resolver->addResolveTargetEntity('Sulu\Bundle\TagBundle\Tag\TagInterface', StubTag::class, []);

        $eventManager = new EventManager();
        $eventManager->addEventListener(Events::loadClassMetadata, $resolver);

        $connection    = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $config);
        $entityManager = new EntityManager($connection, $config, $eventManager);

        $metadata = array_map(
            [$entityManager->getMetadataFactory(), 'getMetadataFor'],
            [
                Event::class,
                EventDimensionContent::class,
                Location::class,
                EventSocialSettings::class,
                EventRecurrence::class,
                EventImport::class,
                EventImportRun::class,
                StubEntity::class,
                StubCategory::class,
                StubTag::class,
            ]
        );
        (new SchemaTool($entityManager))->createSchema($metadata);

        return $entityManager;
    }
}
