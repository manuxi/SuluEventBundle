<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration\Repository;

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
 * references (user, media, contact, route, category, tag) are replaced by small stubs.
 */
class EventRepositoryFeedFilterTest extends TestCase
{
    private EntityManager $entityManager;
    private EventRepository $repository;

    /** @var array<string, StubCategory> */
    private array $categories = [];

    /** @var array<string, StubTag> */
    private array $tags = [];

    /** @var array<string, Location> */
    private array $locations = [];

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

        foreach (['Messe Berlin', 'Online'] as $name) {
            $location = new Location();
            $location->setName($name);
            $this->entityManager->persist($location);
            $this->locations[$name] = $location;
        }

        $this->entityManager->flush();
    }

    public function testIcalWithoutFiltersReturnsEveryLiveEventOfTheLocale(): void
    {
        $this->createEvent('a', 'de', '2027-03-01 10:00:00');
        $this->createEvent('b', 'de', '2027-02-01 10:00:00');
        $this->createEvent('c', 'en', '2027-01-01 10:00:00');
        $this->createEvent('draft', 'de', '2027-01-15 10:00:00', stage: DimensionContentInterface::STAGE_DRAFT);

        $events = $this->repository->findForIcal(['locale' => 'de']);

        // ordered by start date; the draft-only event and the English one are not part of the German feed
        $this->assertSame(['b', 'a'], $this->uuids($events));
    }

    public function testIcalFiltersByCategoryIdWithAnyOperator(): void
    {
        $this->createEvent('ai', 'de', '2027-03-01 10:00:00', categories: ['ai']);
        $this->createEvent('games', 'de', '2027-03-02 10:00:00', categories: ['games']);
        $this->createEvent('both', 'de', '2027-03-03 10:00:00', categories: ['ai', 'games']);
        $this->createEvent('none', 'de', '2027-03-04 10:00:00');

        $events = $this->repository->findForIcal(['locale' => 'de', 'categories' => [$this->categories['ai']->getId()]]);
        $this->assertSame(['ai', 'both'], $this->uuids($events));

        // a query string delivers the ids as strings; an event with two matching categories must not show up twice
        $events = $this->repository->findForIcal([
            'locale'     => 'de',
            'categories' => [(string) $this->categories['ai']->getId(), (string) $this->categories['games']->getId()],
        ]);
        $this->assertSame(['ai', 'games', 'both'], $this->uuids($events));
    }

    public function testIcalFiltersByCategoryWithAllOperator(): void
    {
        $this->createEvent('ai', 'de', '2027-03-01 10:00:00', categories: ['ai']);
        $this->createEvent('both', 'de', '2027-03-03 10:00:00', categories: ['ai', 'games']);

        $events = $this->repository->findForIcal([
            'locale'           => 'de',
            'categories'       => [$this->categories['ai']->getId(), $this->categories['games']->getId()],
            'categoryOperator' => 'AND',
        ]);

        $this->assertSame(['both'], $this->uuids($events));
    }

    public function testIcalFiltersByTagName(): void
    {
        $this->createEvent('symfony', 'de', '2027-03-01 10:00:00', tags: ['symfony']);
        $this->createEvent('llm', 'de', '2027-03-02 10:00:00', tags: ['llm']);
        $this->createEvent('none', 'de', '2027-03-03 10:00:00');

        $events = $this->repository->findForIcal(['locale' => 'de', 'tags' => ['symfony']]);

        $this->assertSame(['symfony'], $this->uuids($events));
    }

    public function testCombinedCategoryAndTagFilterNarrowsDown(): void
    {
        $this->createEvent('ai-llm', 'de', '2027-03-01 10:00:00', categories: ['ai'], tags: ['llm']);
        $this->createEvent('ai-only', 'de', '2027-03-02 10:00:00', categories: ['ai']);
        $this->createEvent('llm-only', 'de', '2027-03-03 10:00:00', tags: ['llm']);

        $events = $this->repository->findForIcal([
            'locale'     => 'de',
            'categories' => [$this->categories['ai']->getId()],
            'tags'       => ['llm'],
        ]);

        $this->assertSame(['ai-llm'], $this->uuids($events));
    }

    public function testFilterWithoutUsableValueMatchesNothing(): void
    {
        $this->createEvent('ai', 'de', '2027-03-01 10:00:00', categories: ['ai']);

        $this->assertSame([], $this->repository->findForIcal(['locale' => 'de', 'categories' => ['abc']]));
        $this->assertSame([], $this->repository->findForIcal(['locale' => 'de', 'tags' => ['']]));
    }

    public function testEmptyFilterListsAreIgnored(): void
    {
        $this->createEvent('ai', 'de', '2027-03-01 10:00:00', categories: ['ai']);

        $events = $this->repository->findForIcal(['locale' => 'de', 'categories' => [], 'tags' => [], 'location' => null]);

        $this->assertSame(['ai'], $this->uuids($events));
    }

    public function testCalendarFiltersByCategoryTagAndLocation(): void
    {
        $this->createEvent('a', 'de', '2027-03-01 10:00:00', categories: ['ai'], tags: ['llm'], location: 'Messe Berlin');
        $this->createEvent('b', 'de', '2027-03-02 10:00:00', categories: ['ai'], location: 'Online');
        $this->createEvent('c', 'de', '2027-03-03 10:00:00', categories: ['games'], location: 'Messe Berlin');

        $byCategory = $this->repository->findForCalendar(['locale' => 'de', 'categories' => [$this->categories['ai']->getId()]]);
        $this->assertSame(['a', 'b'], $this->uuids($byCategory));

        $byTag = $this->repository->findForCalendar(['locale' => 'de', 'tags' => ['llm']]);
        $this->assertSame(['a'], $this->uuids($byTag));

        $byLocationId = $this->repository->findForCalendar(['locale' => 'de', 'location' => $this->locations['Online']->getId()]);
        $this->assertSame(['b'], $this->uuids($byLocationId));

        $byLocationName = $this->repository->findForCalendar(['locale' => 'de', 'location' => 'Messe Berlin']);
        $this->assertSame(['a', 'c'], $this->uuids($byLocationName));

        $combined = $this->repository->findForCalendar([
            'locale'     => 'de',
            'categories' => [$this->categories['ai']->getId()],
            'location'   => 'Messe Berlin',
        ]);
        $this->assertSame(['a'], $this->uuids($combined));
    }

    public function testCalendarReturnsEventsOverlappingTheRange(): void
    {
        // a three day fair that begins before the visible range still has to be shown
        $this->createEvent('fair', 'de', '2027-03-01 09:00:00', '2027-03-03 18:00:00');
        $this->createEvent('before', 'de', '2027-02-10 09:00:00', '2027-02-11 18:00:00');
        $this->createEvent('after', 'de', '2027-03-20 09:00:00', '2027-03-20 18:00:00');
        $this->createEvent('inside', 'de', '2027-03-02 20:00:00');
        $this->createEvent('before-no-end', 'de', '2027-02-28 20:00:00');
        $this->createEvent('draft', 'de', '2027-03-02 09:00:00', stage: DimensionContentInterface::STAGE_DRAFT);

        $events = $this->repository->findForCalendar([
            'locale' => 'de',
            'start'  => '2027-03-02 00:00:00',
            'end'    => '2027-03-09 00:00:00',
        ]);

        $this->assertSame(['fair', 'inside'], $this->uuids($events));
    }

    public function testCalendarWithoutRangeReturnsEveryLiveEvent(): void
    {
        $this->createEvent('a', 'de', '2027-03-01 10:00:00');
        $this->createEvent('b', 'de', '2020-03-01 10:00:00');

        $this->assertSame(['b', 'a'], $this->uuids($this->repository->findForCalendar(['locale' => 'de'])));
    }

    /**
     * @param string[] $categories
     * @param string[] $tags
     */
    private function createEvent(
        string $uuid,
        string $locale,
        string $start,
        ?string $end = null,
        array $categories = [],
        array $tags = [],
        ?string $location = null,
        string $stage = DimensionContentInterface::STAGE_LIVE,
    ): void {
        $event = new Event($uuid);

        $content = new EventDimensionContent($event);
        $content->setStage($stage);
        $content->setLocale($locale);
        $content->setTitle('Event ' . $uuid);
        $content->setStartDate(new \DateTimeImmutable($start));
        $content->setEndDate(null === $end ? null : new \DateTimeImmutable($end));
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
    private function uuids(array $events): array
    {
        return array_map(static fn (Event $event) => $event->getUuid(), $events);
    }

    private function createEntityManager(): EntityManager
    {
        $xmlDriver  = new SimplifiedXmlDriver([__DIR__ . '/../../../src/Resources/config/doctrine' => 'Manuxi\SuluEventBundle\Entity']);
        $stubDriver = new AttributeDriver([__DIR__ . '/../Stub']);

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
                StubEntity::class,
                StubCategory::class,
                StubTag::class,
            ]
        );
        (new SchemaTool($entityManager))->createSchema($metadata);

        return $entityManager;
    }
}
