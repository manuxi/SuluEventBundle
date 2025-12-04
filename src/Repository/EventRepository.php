<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Sulu\Content\Infrastructure\Doctrine\DimensionContentQueryEnhancer;
use Webmozart\Assert\Assert;

class EventRepository
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

    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository<Event>
     */
    private EntityRepository $entityRepository;

    private DimensionContentQueryEnhancer $dimensionContentQueryEnhancer;

    public function __construct(
        EntityManagerInterface $entityManager,
        DimensionContentQueryEnhancer $dimensionContentQueryEnhancer
    ) {
        $this->entityRepository = $entityManager->getRepository(Event::class);
        $this->entityManager = $entityManager;
        $this->dimensionContentQueryEnhancer = $dimensionContentQueryEnhancer;
    }

    public function findById(int $id): ?Event
    {
        return $this->entityRepository->find($id);
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
     * @param array{
     *     event_admin?: bool,
     *     event_website?: bool,
     *     with-event-content?: bool|array<string, mixed>,
     * } $selects
     */
    public function findOneBy(array $filters = [], array $selects = []): ?Event
    {
        $filters = $this->normalizeFindByFilters($filters);
        $selects = $this->normalizeSelects($selects);
        $queryBuilder = $this->buildQueryBuilder($filters, [], $selects);

        try {
            return $queryBuilder->getQuery()->getSingleResult();
        } catch (NoResultException) {
            return null;
        }
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
     *     page?: int,
     *     limit?: int,
     *     startDate?: \DateTimeInterface,
     *     endDate?: \DateTimeInterface,
     *     locationId?: int,
     *     pending?: bool,
     *     expired?: bool,
     * } $filters
     * @param array{
     *     id?: 'asc'|'desc',
     *     title?: 'asc'|'desc',
     *     startDate?: 'asc'|'desc',
     *     created?: 'asc'|'desc',
     *     changed?: 'asc'|'desc',
     * } $sortBys
     * @param array{
     *     event_admin?: bool,
     *     event_website?: bool,
     *     with-event-content?: bool|array<string, mixed>,
     * } $selects
     *
     * @return \Generator<Event>
     */
    public function findBy(array $filters = [], array $sortBys = [], array $selects = []): \Generator
    {
        $filters = $this->normalizeFindByFilters($filters);
        $selects = $this->normalizeSelects($selects);
        $queryBuilder = $this->buildQueryBuilder($filters, $sortBys, $selects);

        /** @var iterable<Event> $events */
        $events = $queryBuilder->getQuery()->getResult();

        foreach ($events as $event) {
            yield $event;
        }
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

    public function add(Event $event): void
    {
        $this->entityManager->persist($event);
    }

    public function remove(Event $event): void
    {
        $this->entityManager->remove($event);
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
        $queryBuilder = $this->entityRepository->createQueryBuilder('event');

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
        $filters['stage'] = $filters['stage'] ?? 'draft';

        return $filters;
    }

    /**
     * @param array<string, mixed> $selects
     *
     * @return array<string, mixed>
     */
    private function normalizeSelects(array $selects): array
    {
        foreach ($selects as $selectGroup => $value) {
            if (!$value) {
                continue;
            }

            if (isset(self::SELECTS[$selectGroup])) {
                $selects = \array_replace_recursive($selects, self::SELECTS[$selectGroup]);
            }
        }

        return $selects;
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
        if ((
                \array_key_exists('locale', $filters)
                && \array_key_exists('stage', $filters)
            )
            || ([] === $filters && [] !== $sortBys)
        ) {
            $this->dimensionContentQueryEnhancer->addFilters(
                $queryBuilder,
                'event',
                EventDimensionContent::class,
                $filters,
                $sortBys
            );
        }

        if ($selects[self::SELECT_EVENT_CONTENT] ?? null) {
            /** @var array<string, bool> $contentSelects */
            $contentSelects = $selects[self::SELECT_EVENT_CONTENT];

            $queryBuilder->leftJoin(
                'event.dimensionContents',
                'dimensionContent'
            );

            $this->dimensionContentQueryEnhancer->addSelects(
                $queryBuilder,
                EventDimensionContent::class,
                $filters,
                $contentSelects
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
            $queryBuilder->andWhere('event.type IN (:types)');
            $queryBuilder->setParameter('types', $filters['types']);
        }

        if (isset($filters['locationId'])) {
            $queryBuilder->andWhere('event.location = :locationId');
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
                    $queryBuilder->expr()->between('event.startDate', ':filterStartDate', ':filterEndDate'),
                    $queryBuilder->expr()->between('event.endDate', ':filterStartDate', ':filterEndDate'),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->lte('event.startDate', ':filterStartDate'),
                        $queryBuilder->expr()->gte('event.endDate', ':filterEndDate')
                    )
                )
            );
            $queryBuilder->setParameter('filterStartDate', $filters['startDate']);
            $queryBuilder->setParameter('filterEndDate', $filters['endDate']);
        } elseif (isset($filters['startDate'])) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->gte('event.endDate', ':filterStartDate'),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->isNull('event.endDate'),
                        $queryBuilder->expr()->gte('event.startDate', ':filterStartDate')
                    )
                )
            );
            $queryBuilder->setParameter('filterStartDate', $filters['startDate']);
        } elseif (isset($filters['endDate'])) {
            $queryBuilder->andWhere('event.startDate <= :filterEndDate');
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
                '(event.endDate IS NOT NULL AND event.endDate >= :now) OR '.
                '(event.endDate IS NULL AND event.startDate >= :todayStart)'
            );
            $queryBuilder->setParameter('now', $now);
            $queryBuilder->setParameter('todayStart', $todayStart);
        } elseif ($hasExpired) {
            $queryBuilder->andWhere(
                '(event.endDate IS NOT NULL AND event.endDate < :now) OR '.
                '(event.endDate IS NULL AND event.startDate < :todayStart)'
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
            Assert::inArray($direction, ['asc', 'desc']);

            switch ($field) {
                case 'id':
                    $queryBuilder->addOrderBy('event.id', $direction);
                    break;
                case 'title':
                    $queryBuilder->addOrderBy('dimensionContent.title', $direction);
                    break;
                case 'startDate':
                    $queryBuilder->addOrderBy('event.startDate', $direction);
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
     * @param array{page?: int, limit?: int} $filters
     */
    private function applyPagination(QueryBuilder $queryBuilder, array $filters): void
    {
        $page = $filters['page'] ?? null;
        $limit = $filters['limit'] ?? null;

        if (null !== $limit) {
            $queryBuilder->setMaxResults($limit);
        }

        if (null !== $page && null !== $limit) {
            $offset = ($page - 1) * $limit;
            $queryBuilder->setFirstResult($offset);
        }
    }

    public function countAll(): int
    {
        return (int) $this->entityRepository
            ->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPublished(string $locale): int
    {
        $qb = $this->entityRepository->createQueryBuilder('event');

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

    public function findForCalendar(array $filters): array
    {
        $qb = $this->entityRepository->createQueryBuilder('event');

        if (isset($filters['start'])) {
            $qb->andWhere('event.startDate >= :start')
                ->setParameter('start', new \DateTime($filters['start']));
        }

        if (isset($filters['end'])) {
            $qb->andWhere('event.startDate <= :end')
                ->setParameter('end', new \DateTime($filters['end']));
        }

        $qb->orderBy('event.startDate', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findForIcal(array $filters): array
    {
        $qb = $this->entityRepository->createQueryBuilder('event');

        $qb->orderBy('event.startDate', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findRecurringEvents(): array
    {
        return $this->entityRepository->createQueryBuilder('event')
            ->where('event.recurrence IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    /*
    public function findRecurringEvents(): array
    {
        return $this->entityRepository->createQueryBuilder('event')
            ->innerJoin('event.recurrence', 'recurrence')
            ->where('recurrence.isRecurring = :recurring')
            ->setParameter('recurring', true)
            ->getQuery()
            ->getResult();
    }
    */
}