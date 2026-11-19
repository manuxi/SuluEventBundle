<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Dashboard;

use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use Sulu\Content\Domain\Model\DimensionContentInterface;

/**
 * Read-only queries for the tiles of manuxi/sulu-dashboard-bundle (see the classes in this namespace).
 *
 * Recurring events: the bundle only stores real entries for the occurrences (sulu:events:generate-recurring), the
 * calendar and the lists do not expand the rule on the fly. So the "next date" of a series is simply the next
 * generated occurrence, found the same way as the "pending" filter of the repository.
 */
class EventDashboardReader
{
    public function __construct(
        private readonly EventRepository $events,
        private readonly LocationRepository $locations,
    ) {
    }

    /**
     * The next published events: running or starting later today or in the future.
     *
     * @return list<array{id: string, title: ?string, startDate: ?\DateTimeInterface, endDate: ?\DateTimeInterface, location: ?string}>
     */
    public function upcoming(string $locale, \DateTimeImmutable $now, int $limit): array
    {
        $rows = $this->events->createQueryBuilder('event')
            ->select('event.uuid AS id', 'dc.title AS title', 'dc.startDate AS startDate', 'dc.endDate AS endDate', 'l.name AS locationName', 'l.city AS locationCity')
            ->innerJoin(
                'event.dimensionContents',
                'dc',
                'WITH',
                'dc.locale = :locale AND dc.stage = :stage AND dc.version = :version'
            )
            ->leftJoin('dc.location', 'l')
            ->where('(dc.endDate IS NOT NULL AND dc.endDate >= :now) OR (dc.endDate IS NULL AND dc.startDate >= :today)')
            ->orderBy('dc.startDate', 'ASC')
            ->addOrderBy('event.uuid', 'ASC')
            ->setMaxResults($limit)
            ->setParameter('locale', $locale)
            ->setParameter('stage', DimensionContentInterface::STAGE_LIVE)
            ->setParameter('version', DimensionContentInterface::CURRENT_VERSION)
            ->setParameter('now', $now)
            ->setParameter('today', $now->setTime(0, 0))
            ->getQuery()
            ->getArrayResult();

        $result = [];
        foreach ($rows as $row) {
            $place = array_filter(
                [$row['locationName'] ?? null, $row['locationCity'] ?? null],
                static fn ($part): bool => \is_string($part) && '' !== trim($part)
            );

            $result[] = [
                'id' => (string) $row['id'],
                'title' => $row['title'] ?? null,
                'startDate' => $row['startDate'] ?? null,
                'endDate' => $row['endDate'] ?? null,
                'location' => [] === $place ? null : implode(', ', array_unique($place)),
            ];
        }

        return $result;
    }

    /**
     * Events that exist as a draft in the locale but have no live version in it (never published or unpublished).
     */
    public function countUnpublished(string $locale): int
    {
        $queryBuilder = $this->events->createQueryBuilder('event');

        $queryBuilder
            ->select('COUNT(DISTINCT event.uuid)')
            ->innerJoin(
                'event.dimensionContents',
                'draft',
                'WITH',
                'draft.locale = :locale AND draft.stage = :draftStage AND draft.version = :version'
            )
            ->where(
                'NOT EXISTS (SELECT live.id FROM ' . EventDimensionContent::class . ' live'
                . ' WHERE live.event = event AND live.locale = :locale AND live.stage = :liveStage AND live.version = :version)'
            )
            ->setParameter('locale', $locale)
            ->setParameter('draftStage', DimensionContentInterface::STAGE_DRAFT)
            ->setParameter('liveStage', DimensionContentInterface::STAGE_LIVE)
            ->setParameter('version', DimensionContentInterface::CURRENT_VERSION);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @return list<Location>
     */
    public function locations(): array
    {
        return array_values($this->locations->findBy([], ['name' => 'ASC']));
    }
}
