<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Dashboard;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Manuxi\SuluEventBundle\Dashboard\EventDashboardReader;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use PHPUnit\Framework\TestCase;

/**
 * The reader does not need the dashboard bundle. The queries themselves are checked against the real mapping on a
 * running installation; here the shape of the DQL and the handling of the result are checked.
 */
class EventDashboardReaderTest extends TestCase
{
    private ?string $dql = null;
    private Query $query;

    private function reader(?LocationRepository $locations = null): EventDashboardReader
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $this->query = $this->createMock(Query::class);
        $this->query->method('setParameters')->willReturnSelf();
        $this->query->method('setFirstResult')->willReturnSelf();
        $this->query->method('setMaxResults')->willReturnSelf();
        $entityManager->method('createQuery')->willReturnCallback(function (string $dql): Query {
            $this->dql = $dql;

            return $this->query;
        });

        $events = $this->createMock(EventRepository::class);
        $events->method('createQueryBuilder')->willReturnCallback(
            static fn (string $alias): QueryBuilder => (new QueryBuilder($entityManager))->select($alias)->from('Event', $alias)
        );

        return new EventDashboardReader($events, $locations ?? $this->createMock(LocationRepository::class));
    }

    public function testUpcomingReadsLivePublishedEventsOfTheLocaleInDateOrder(): void
    {
        $start = new \DateTimeImmutable('2026-12-03 18:30:00');
        $reader = $this->reader();
        $this->query->method('getArrayResult')->willReturn([
            ['id' => 'a', 'title' => 'One', 'startDate' => $start, 'endDate' => null, 'locationName' => 'Hall', 'locationCity' => 'Berlin'],
            ['id' => 'b', 'title' => null, 'startDate' => $start, 'endDate' => null, 'locationName' => 'Berlin', 'locationCity' => 'Berlin'],
            ['id' => 'c', 'title' => 'Three', 'startDate' => $start, 'endDate' => null, 'locationName' => null, 'locationCity' => null],
        ]);

        $rows = $reader->upcoming('de', new \DateTimeImmutable('2026-11-14 10:00:00'), 5);

        $this->assertStringContainsString('dc.locale = :locale AND dc.stage = :stage AND dc.version = :version', (string) $this->dql);
        $this->assertStringContainsString('ORDER BY dc.startDate ASC', (string) $this->dql);
        // running or later: the end date counts, without one the start date from today on
        $this->assertStringContainsString('dc.endDate >= :now', (string) $this->dql);
        $this->assertStringContainsString('dc.endDate IS NULL AND dc.startDate >= :today', (string) $this->dql);

        $this->assertSame('Hall, Berlin', $rows[0]['location']);
        $this->assertSame('Berlin', $rows[1]['location'], 'same name and city only once');
        $this->assertNull($rows[2]['location']);
        $this->assertSame('a', $rows[0]['id']);
    }

    public function testCountUnpublishedLooksForDraftsWithoutLiveVersion(): void
    {
        $reader = $this->reader();
        $this->query->method('getSingleScalarResult')->willReturn('4');

        $this->assertSame(4, $reader->countUnpublished('de'));
        $this->assertStringContainsString('COUNT(DISTINCT event.uuid)', (string) $this->dql);
        $this->assertStringContainsString('draft.stage = :draftStage', (string) $this->dql);
        $this->assertStringContainsString('NOT EXISTS (SELECT live.id FROM', (string) $this->dql);
        $this->assertStringContainsString('live.stage = :liveStage', (string) $this->dql);
    }

    public function testLocationsComeSortedByName(): void
    {
        $location = new Location();
        $repository = $this->createMock(LocationRepository::class);
        $repository->expects($this->once())->method('findBy')->with([], ['name' => 'ASC'])->willReturn([5 => $location]);

        $this->assertSame([$location], $this->reader($repository)->locations());
    }
}
