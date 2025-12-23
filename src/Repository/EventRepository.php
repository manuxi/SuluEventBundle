<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Sulu\Content\Infrastructure\Doctrine\DimensionContentQueryEnhancer;
use Webmozart\Assert\Assert;

class EventRepository extends ServiceEntityRepository
{
    public const GROUP_SELECT_EVENT_ADMIN = 'event_admin';
    public const GROUP_SELECT_EVENT_WEBSITE = 'event_website';

    public const SELECT_EVENT_CONTENT = 'with-event-content';

    private const SELECTS = [
        self::GROUP_SELECT_EVENT_ADMIN => [
            self::SELECT_EVENT_CONTENT => [
                DimensionContentQueryEnhancer::GROUP_SELECT_CONTENT_ADMIN => true,
            ],
        ],
        self::GROUP_SELECT_EVENT_WEBSITE => [
            self::SELECT_EVENT_CONTENT => [
                DimensionContentQueryEnhancer::GROUP_SELECT_CONTENT_WEBSITE => true,
            ],
        ],
    ];

    public function __construct(
        ManagerRegistry $registry,
        private DimensionContentQueryEnhancer $dimensionContentQueryEnhancer,
    ) {
        parent::__construct($registry, Event::class);
    }

    public function findById(int $id): ?Event
    {
        //return $this->find($id);
        $qb = $this->createQueryBuilder('event')
            ->leftJoin('event.dimensionContents', 'dimensionContent')
            ->addSelect('dimensionContent')
            ->where('event.id = :id')
            ->setParameter('id', $id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByIds(array $ids, string $locale, string $stage = DimensionContentInterface::STAGE_LIVE): array
    {
        $qb = $this->buildQueryBuilder(
            ['ids' => $ids, 'locale' => $locale, 'stage' => $stage],
            [], // sort
            [self::GROUP_SELECT_EVENT_WEBSITE => true]
        );

        return $qb->getQuery()->getResult();
    }

    public function findAllByLocale(string $locale, string $stage = DimensionContentInterface::STAGE_LIVE): array
    {
        $qb = $this->buildQueryBuilder(
            ['locale' => $locale, 'stage' => $stage],
            [], // sort
            [self::GROUP_SELECT_EVENT_WEBSITE => true]
        );

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array{
     *     id?: int,
     *     ids?: int[],
     *     locale?: string|null,
     *     stage?: string|null,
     *     categoryIds?: int[],
     *     categoryKeys?: string[],
     *     categoryOperator?: 'AND'|'OR',
     *     tagIds?: int[],
     *     tagNames?: string[],
     *     tagOperator?: 'AND'|'OR',
     *     templateKeys?: string[],
     *     types?: string[],
     *     startDate?: \DateTimeInterface,
     *     endDate?: \DateTimeInterface,
     *     locationId?: int,
     *     pending?: bool,
     *     expired?: bool,
     * } $filters
     * @param array<string, string> $sortBys
     * @param array<string, mixed> $selects
     *
     * @return Event[]
     */
    public function findByFilters(array $filters = [], array $sortBys = [], array $selects = []): array
    {
        $filters = $this->normalizeFindByFilters($filters);
        $selects = $this->normalizeSelects($selects);

        $queryBuilder = $this->buildQueryBuilder($filters, $sortBys, $selects);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param array{
     *     id?: int,
     *     ids?: int[],
     *     locale?: string|null,
     *     stage?: string|null,
     *     categoryIds?: int[],
     *     categoryKeys?: string[],
     *     categoryOperator?: 'AND'|'OR',
     *     tagIds?: int[],
     *     tagNames?: string[],
     *     tagOperator?: 'AND'|'OR',
     *     templateKeys?: string[],
     *     types?: string[],
     *     startDate?: \DateTimeInterface,
     *     endDate?: \DateTimeInterface,
     *     locationId?: int,
     *     pending?: bool,
     *     expired?: bool,
     * } $filters
     */
    public function countBy(array $filters = []): int
    {
        $filters = $this->normalizeFindByFilters($filters);
        $selects = $this->normalizeSelects([]);
        $queryBuilder = $this->buildQueryBuilder($filters, [], $selects);

        $queryBuilder->select('COUNT(DISTINCT event.id)');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPublished(string $locale): int
    {
        $qb = $this->createQueryBuilder('event');

        $qb->select('COUNT(DISTINCT event.id)')
            ->leftJoin('event.dimensionContents', 'dc')
            ->where('dc.locale = :locale')
            ->andWhere('dc.stage = :stage')
            ->andWhere('dc.workflowPlace = :published')
            ->setParameter('locale', $locale)
            ->setParameter('stage', DimensionContentInterface::STAGE_LIVE)
            ->setParameter('published', WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findByDateRange(
        string $locale,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        $filters = [
            'locale' => $locale,
            'stage' => 'live',
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        $selects = [self::GROUP_SELECT_EVENT_WEBSITE => true];

        $queryBuilder = $this->buildQueryBuilder($filters, ['startDate' => 'asc'], $selects);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Find events for calendar display.
     *
     * @param array{locale: string, start?: string, end?: string} $filters
     */
    public function findForCalendar(array $filters): array
    {
        Assert::keyExists($filters, 'locale', 'locale is required for findForCalendar');

        $qb = $this->createQueryBuilder('event');

        $qb->leftJoin(
            'event.dimensionContents',
            'dimensionContent',
            'WITH',
            'dimensionContent.locale = :locale AND dimensionContent.stage = :stage AND dimensionContent.version = :version'
        );
        $qb->setParameter('locale', $filters['locale']);
        $qb->setParameter('stage', DimensionContentInterface::STAGE_LIVE);
        $qb->setParameter('version', DimensionContentInterface::CURRENT_VERSION);

        if (isset($filters['start'])) {
            $qb->andWhere('dimensionContent.startDate >= :start')
                ->setParameter('start', new \DateTime($filters['start']));
        }

        if (isset($filters['end'])) {
            $qb->andWhere('dimensionContent.startDate <= :end')
                ->setParameter('end', new \DateTime($filters['end']));
        }

        $qb->orderBy('dimensionContent.startDate', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findForIcal(array $filters): array
    {
        Assert::keyExists($filters, 'locale', 'locale is required for findForIcal');

        $qb = $this->createQueryBuilder('event');

        $qb->leftJoin(
            'event.dimensionContents',
            'dimensionContent',
            'WITH',
            'dimensionContent.locale = :locale AND dimensionContent.stage = :stage AND dimensionContent.version = :version'
        );
        $qb->setParameter('locale', $filters['locale']);
        $qb->setParameter('stage', DimensionContentInterface::STAGE_LIVE);
        $qb->setParameter('version', DimensionContentInterface::CURRENT_VERSION);

        $qb->orderBy('dimensionContent.startDate', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findRecurringEvents(string $locale): array
    {
        $qb = $this->createQueryBuilder('event');

        $qb->leftJoin(
            'event.dimensionContents',
            'dimensionContent',
            'WITH',
            'dimensionContent.locale = :locale AND dimensionContent.stage = :stage AND dimensionContent.version = :version'
        );
        $qb->setParameter('locale', $locale);
        $qb->setParameter('stage', DimensionContentInterface::STAGE_DRAFT);
        $qb->setParameter('version', DimensionContentInterface::CURRENT_VERSION);

        $qb->where('dimensionContent.recurrence IS NOT NULL');

        return $qb->getQuery()->getResult();
    }

    public function add(Event $event): void
    {
        $this->getEntityManager()->persist($event);
    }

    public function remove(Event $event): void
    {
        $this->getEntityManager()->remove($event);
    }

    /**
     * @param array<string, mixed> $filters
     * @param array<string, string> $sortBys
     * @param array<string, mixed> $selects
     */
    private function buildQueryBuilder(
        array $filters = [],
        array $sortBys = [],
        array $selects = []
    ): QueryBuilder {
        $queryBuilder = $this->createQueryBuilder('event');

        $this->applyContentJoin($queryBuilder, $filters, $sortBys, $selects);
        $this->applyFilters($queryBuilder, $filters);
        $this->applySortBys($queryBuilder, $sortBys);
        $this->applyPagination($queryBuilder, $filters);

        return $queryBuilder;
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    private function normalizeFindByFilters(array $filters): array
    {
        $filters['stage'] = $filters['stage'] ?? DimensionContentInterface::STAGE_DRAFT;

        return $filters;
    }

    /**
     * @param array<string, mixed> $selects
     *
     * @return array<string, mixed>
     */
    private function normalizeSelects(array $selects): array
    {
        $normalizedSelects = [];

        foreach (self::SELECTS as $groupKey => $groupSelects) {
            if (true === ($selects[$groupKey] ?? false)) {
                foreach ($groupSelects as $selectKey => $selectValue) {
                    $normalizedSelects[$selectKey] = $selectValue;
                }
            }
        }

        foreach ($selects as $key => $value) {
            if (\is_string($key) && \is_array($value)) {
                $normalizedSelects[$key] = $value;
            }
        }

        return $normalizedSelects;
    }

    /**
     * @param array<string, mixed> $filters
     * @param array<string, string> $sortBys
     * @param array<string, mixed> $selects
     */
    private function applyContentJoin(
        QueryBuilder $queryBuilder,
        array $filters,
        array $sortBys,
        array $selects
    ): void {
        $locale = $filters['locale'] ?? null;
        $stage = $filters['stage'] ?? DimensionContentInterface::STAGE_DRAFT;
        $version = $filters['version'] ?? DimensionContentInterface::CURRENT_VERSION;

        $queryBuilder->leftJoin(
            'event.dimensionContents',
            'dimensionContent',
            'WITH',
            'dimensionContent.stage = :stage AND dimensionContent.version = :version'
            . ($locale ? ' AND dimensionContent.locale = :locale' : '')
        );

        $queryBuilder->setParameter('stage', $stage);
        $queryBuilder->setParameter('version', $version);

        if ($locale) {
            $queryBuilder->setParameter('locale', $locale);
        }

        $queryBuilder->addSelect('dimensionContent');

        if (!empty($selects)) {
            $this->dimensionContentQueryEnhancer->addSelects(
                $queryBuilder,
                EventDimensionContent::class,
                ['locale' => $locale, 'stage' => $stage],
                $selects
            );
        }
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function applyFilters(QueryBuilder $queryBuilder, array $filters): void
    {
        if (isset($filters['id'])) {
            $queryBuilder->andWhere('event.id = :id');
            $queryBuilder->setParameter('id', $filters['id']);
        }

        if (isset($filters['ids'])) {
            $queryBuilder->andWhere('event.id IN (:ids)');
            $queryBuilder->setParameter('ids', $filters['ids']);
        }

        if (isset($filters['types'])) {
            $queryBuilder->andWhere('dimensionContent.type IN (:types)');
            $queryBuilder->setParameter('types', $filters['types']);
        }

        if (isset($filters['locationId'])) {
            $queryBuilder->andWhere('dimensionContent.location = :locationId');
            $queryBuilder->setParameter('locationId', $filters['locationId']);
        }

        $this->applyDateFilters($queryBuilder, $filters);
        $this->applyPendingExpiredFilters($queryBuilder, $filters);
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function applyDateFilters(QueryBuilder $queryBuilder, array $filters): void
    {
        if (isset($filters['startDate']) && isset($filters['endDate'])) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->between('dimensionContent.startDate', ':filterStartDate', ':filterEndDate'),
                    $queryBuilder->expr()->between('dimensionContent.endDate', ':filterStartDate', ':filterEndDate'),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->lte('dimensionContent.startDate', ':filterStartDate'),
                        $queryBuilder->expr()->gte('dimensionContent.endDate', ':filterEndDate')
                    )
                )
            );
            $queryBuilder->setParameter('filterStartDate', $filters['startDate']);
            $queryBuilder->setParameter('filterEndDate', $filters['endDate']);
        } elseif (isset($filters['startDate'])) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->gte('dimensionContent.endDate', ':filterStartDate'),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->isNull('dimensionContent.endDate'),
                        $queryBuilder->expr()->gte('dimensionContent.startDate', ':filterStartDate')
                    )
                )
            );
            $queryBuilder->setParameter('filterStartDate', $filters['startDate']);
        } elseif (isset($filters['endDate'])) {
            $queryBuilder->andWhere('dimensionContent.startDate <= :filterEndDate');
            $queryBuilder->setParameter('filterEndDate', $filters['endDate']);
        }
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function applyPendingExpiredFilters(QueryBuilder $queryBuilder, array $filters): void
    {
        $hasPending = $filters['pending'] ?? false;
        $hasExpired = $filters['expired'] ?? false;

        if (!$hasPending && !$hasExpired) {
            return;
        }

        if ($hasPending && $hasExpired) {
            return;
        }

        $now = new \DateTime();
        $todayStart = (clone $now)->setTime(0, 0, 0);

        if ($hasPending) {
            $queryBuilder->andWhere(
                '(dimensionContent.endDate IS NOT NULL AND dimensionContent.endDate >= :now) OR ' .
                '(dimensionContent.endDate IS NULL AND dimensionContent.startDate >= :todayStart)'
            );
            $queryBuilder->setParameter('now', $now);
            $queryBuilder->setParameter('todayStart', $todayStart);
        } elseif ($hasExpired) {
            $queryBuilder->andWhere(
                '(dimensionContent.endDate IS NOT NULL AND dimensionContent.endDate < :now) OR ' .
                '(dimensionContent.endDate IS NULL AND dimensionContent.startDate < :todayStart)'
            );
            $queryBuilder->setParameter('now', $now);
            $queryBuilder->setParameter('todayStart', $todayStart);
        }
    }

    /**
     * @param array{
     *     id?: 'asc'|'desc',
     *     title?: 'asc'|'desc',
     *     startDate?: 'asc'|'desc',
     *     created?: 'asc'|'desc',
     *     changed?: 'asc'|'desc',
     * } $sortBys
     */
    private function applySortBys(QueryBuilder $queryBuilder, array $sortBys): void
    {
        foreach ($sortBys as $field => $direction) {
            switch ($field) {
                case 'id':
                    $queryBuilder->addOrderBy('event.id', $direction);
                    break;
                case 'title':
                    $queryBuilder->addOrderBy('dimensionContent.title', $direction);
                    break;
                case 'startDate':
                    $queryBuilder->addOrderBy('dimensionContent.startDate', $direction);
                    break;
                case 'created':
                    $queryBuilder->addOrderBy('dimensionContent.created', $direction);
                    break;
                case 'changed':
                    $queryBuilder->addOrderBy('dimensionContent.changed', $direction);
                    break;
            }
        }
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function applyPagination(QueryBuilder $queryBuilder, array $filters): void
    {
        if (isset($filters['limit'])) {
            $queryBuilder->setMaxResults($filters['limit']);
        }

        if (isset($filters['offset'])) {
            $queryBuilder->setFirstResult($filters['offset']);
        }
    }
}