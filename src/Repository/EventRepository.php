<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluEventBundle\Entity\AttendanceMode;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Sulu\Content\Domain\Model\DimensionContentInterface;
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

    public function findAll(): array
    {
        $queryBuilder = $this->createQueryBuilder('event')
            ->leftJoin('event.dimensionContents', 'dimensionContent')
            ->addSelect('dimensionContent');

        return $queryBuilder->getQuery()->getResult();
    }

    public function findByUuid(string $uuid): ?Event
    {
        $queryBuilder = $this->createQueryBuilder('event')
            ->leftJoin('event.dimensionContents', 'dimensionContent')
            ->addSelect('dimensionContent')
            ->where('event.uuid = :uuid')
            ->setParameter('uuid', $uuid);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string[] $uuids
     * @return Event[]
     */
    public function findByUuids(array $uuids, string $locale, string $stage = DimensionContentInterface::STAGE_LIVE): array
    {
        $filters = ['uuids' => $uuids, 'locale' => $locale, 'stage' => $stage];

        $qb = $this->buildQueryBuilder(
            $filters,
            [],
            [self::GROUP_SELECT_EVENT_WEBSITE => true]
        );

        return $qb->getQuery()->getResult();
    }

    public function findAllByLocale(string $locale, string $stage = DimensionContentInterface::STAGE_LIVE): array
    {
        $qb = $this->buildQueryBuilder(
            ['locale' => $locale, 'stage' => $stage],
            [],
            [self::GROUP_SELECT_EVENT_WEBSITE => true]
        );

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array{
     *     uuid?: string,
     *     uuids?: string[],
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
     *     uuid?: string,
     *     uuids?: string[],
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

        $queryBuilder->select('COUNT(DISTINCT event.uuid)');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.uuid)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPublished(string $locale): int
    {
        // workflowPlace is a draft-side concept and is never set on the live-stage
        // dimension content, so having a live-stage row for the locale is itself
        // the "published" signal (matches EventWebsiteSearchProvider::provide()).
        $qb = $this->createQueryBuilder('event');

        $qb->select('COUNT(DISTINCT event.uuid)')
            ->leftJoin('event.dimensionContents', 'dc')
            ->where('dc.locale = :locale')
            ->andWhere('dc.stage = :stage')
            ->setParameter('locale', $locale)
            ->setParameter('stage', DimensionContentInterface::STAGE_LIVE);

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
     * Events for the FullCalendar JSON API: live events of the locale that overlap the requested range, optionally
     * narrowed down like the smart content provider does it (see applyLiveFeedFilters()).
     *
     * @param array{
     *     locale: string,
     *     start?: string|null,
     *     end?: string|null,
     *     categories?: array<int|string>,
     *     categoryOperator?: 'AND'|'OR',
     *     tags?: string[],
     *     tagOperator?: 'AND'|'OR',
     *     location?: int|string|null,
     *     types?: string[],
     *     attendanceModes?: string[],
     *     countries?: string[],
     *     free?: bool,
     * } $filters
     *
     * @return Event[]
     */
    public function findForCalendar(array $filters): array
    {
        Assert::keyExists($filters, 'locale', 'locale is required for findForCalendar');

        $qb = $this->createLiveFeedQueryBuilder($filters['locale']);
        $this->applyLiveFeedFilters($qb, $filters);

        // overlap with the requested range instead of "starts inside": multi day events that began before the
        // range still have to show up
        if (!empty($filters['start'])) {
            $qb->andWhere('COALESCE(dimensionContent.endDate, dimensionContent.startDate) >= :start')
                ->setParameter('start', new \DateTime($filters['start']));
        }

        if (!empty($filters['end'])) {
            $qb->andWhere('dimensionContent.startDate <= :end')
                ->setParameter('end', new \DateTime($filters['end']));
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Events for the iCal feed: live events of the locale, optionally narrowed down by categories, tags and
     * location (see applyLiveFeedFilters()).
     *
     * @param array{
     *     locale: string,
     *     categories?: array<int|string>,
     *     categoryOperator?: 'AND'|'OR',
     *     tags?: string[],
     *     tagOperator?: 'AND'|'OR',
     *     location?: int|string|null,
     *     types?: string[],
     *     attendanceModes?: string[],
     *     countries?: string[],
     *     free?: bool,
     * } $filters
     *
     * @return Event[]
     */
    public function findForIcal(array $filters): array
    {
        Assert::keyExists($filters, 'locale', 'locale is required for findForIcal');

        $qb = $this->createLiveFeedQueryBuilder($filters['locale']);
        $this->applyLiveFeedFilters($qb, $filters);

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
     * Only events that have a live dimension content in the locale (i.e. are published there), ordered by start.
     */
    private function createLiveFeedQueryBuilder(string $locale): QueryBuilder
    {
        $qb = $this->createQueryBuilder('event');

        $qb->innerJoin(
            'event.dimensionContents',
            'dimensionContent',
            'WITH',
            'dimensionContent.locale = :locale AND dimensionContent.stage = :stage AND dimensionContent.version = :version'
        );
        $qb->setParameter('locale', $locale);
        $qb->setParameter('stage', DimensionContentInterface::STAGE_LIVE);
        $qb->setParameter('version', DimensionContentInterface::CURRENT_VERSION);

        // The content aggregator needs the complete collection of the dimension contents (the unlocalized one next
        // to the localized one), and in debug mode it insists on it being loaded already. The join above only
        // filters, so fetch all of them separately.
        $qb->leftJoin('event.dimensionContents', 'allDimensionContents')
            ->addSelect('allDimensionContents');

        $qb->orderBy('dimensionContent.startDate', 'ASC');

        return $qb;
    }

    /**
     * Categories (by id), tags (by name) and location (by id or exact name) as the smart content provider evaluates
     * them. The operator per kind is "OR" (any of them) or "AND" (all of them). A filter that was passed but holds no
     * usable value matches nothing, so a typo in a subscription link does not silently return every event.
     *
     * @param array<string, mixed> $filters
     */
    private function applyLiveFeedFilters(QueryBuilder $qb, array $filters): void
    {
        $categoryIds = [];
        foreach ((array) ($filters['categories'] ?? []) as $categoryId) {
            if (\is_int($categoryId) || (\is_string($categoryId) && 1 === preg_match('/^\d+$/', $categoryId))) {
                $categoryIds[] = (int) $categoryId;
            }
        }
        $this->applyTaxonomyFilter(
            $qb,
            'dimensionContent.excerptCategories',
            'feedCategory',
            'id',
            array_values(array_unique($categoryIds)),
            [] !== (array) ($filters['categories'] ?? []),
            $filters['categoryOperator'] ?? 'OR',
        );

        $tagNames = [];
        foreach ((array) ($filters['tags'] ?? []) as $tagName) {
            if (\is_string($tagName) && '' !== trim($tagName)) {
                $tagNames[] = trim($tagName);
            }
        }
        $this->applyTaxonomyFilter(
            $qb,
            'dimensionContent.excerptTags',
            'feedTag',
            'name',
            array_values(array_unique($tagNames)),
            [] !== (array) ($filters['tags'] ?? []),
            $filters['tagOperator'] ?? 'OR',
        );

        $location = $filters['location'] ?? null;
        if (\is_int($location) || (\is_string($location) && '' !== trim($location))) {
            if (\is_int($location) || 1 === preg_match('/^\d+$/', trim($location))) {
                $qb->andWhere('IDENTITY(dimensionContent.location) = :feedLocationId')
                    ->setParameter('feedLocationId', (int) $location);
            } else {
                $qb->innerJoin('dimensionContent.location', 'feedLocation')
                    ->andWhere('feedLocation.name = :feedLocationName')
                    ->setParameter('feedLocationName', trim($location));
            }
        }

        $this->applyDetailFilters($qb, $filters);
    }

    /**
     * Event types (keys), ways to take part, country codes of the location and "free of charge only". Several values
     * of one kind match with OR, the kinds with AND. Like categories and tags, a list that was passed without any
     * usable value matches nothing.
     *
     * @param array<string, mixed> $filters
     */
    private function applyDetailFilters(QueryBuilder $qb, array $filters): void
    {
        $types = array_values(array_unique(array_filter(
            array_map(static fn ($type): string => \is_string($type) ? trim($type) : '', (array) ($filters['types'] ?? [])),
            static fn (string $type): bool => '' !== $type
        )));
        if ([] !== (array) ($filters['types'] ?? [])) {
            $this->applyValueList($qb, 'dimensionContent.type', 'feedType', $types);
        }

        $modes = array_values(array_unique(array_filter(
            array_map(static fn ($mode): string => \is_string($mode) ? trim($mode) : '', (array) ($filters['attendanceModes'] ?? [])),
            static fn (string $mode): bool => \in_array($mode, AttendanceMode::values(), true)
        )));
        if ([] !== (array) ($filters['attendanceModes'] ?? [])) {
            $this->applyValueList($qb, 'dimensionContent.attendanceMode', 'feedMode', $modes);
        }

        $countries = array_values(array_unique(array_filter(
            array_map(static fn ($country): string => \is_string($country) ? strtoupper(trim($country)) : '', (array) ($filters['countries'] ?? [])),
            static fn (string $country): bool => 1 === preg_match('/^[A-Z]{2}$/', $country)
        )));
        if ([] !== (array) ($filters['countries'] ?? [])) {
            if ([] === $countries) {
                $qb->andWhere('1 = 0');
            } else {
                $qb->innerJoin('dimensionContent.location', 'feedCountryLocation')
                    ->andWhere('UPPER(feedCountryLocation.countryCode) IN (:feedCountries)')
                    ->setParameter('feedCountries', $countries);
            }
        }

        if (true === ($filters['free'] ?? false)) {
            $qb->andWhere('dimensionContent.isFree = :feedFree')
                ->setParameter('feedFree', true);
        }
    }

    /**
     * @param list<string> $values
     */
    private function applyValueList(QueryBuilder $qb, string $field, string $parameter, array $values): void
    {
        if ([] === $values) {
            $qb->andWhere('1 = 0');

            return;
        }

        $qb->andWhere($field . ' IN (:' . $parameter . ')')
            ->setParameter($parameter, $values);
    }

    /**
     * @param list<int|string> $values
     */
    private function applyTaxonomyFilter(
        QueryBuilder $qb,
        string $join,
        string $alias,
        string $field,
        array $values,
        bool $wasRequested,
        string $operator,
    ): void {
        if ([] === $values) {
            if ($wasRequested) {
                $qb->andWhere('1 = 0');
            }

            return;
        }

        if ('AND' === $operator) {
            // one join per value: the event has to carry every one of them
            foreach ($values as $index => $value) {
                $qb->innerJoin($join, $alias . $index)
                    ->andWhere($alias . $index . '.' . $field . ' = :' . $alias . $index)
                    ->setParameter($alias . $index, $value);
            }

            return;
        }

        $qb->innerJoin($join, $alias)
            ->andWhere($alias . '.' . $field . ' IN (:' . $alias . 'Values)')
            ->setParameter($alias . 'Values', $values);
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

        $queryBuilder->leftJoin('event.dimensionContents', 'dimensionContent');

        $normalizedSelects = $this->normalizeSelects($selects);
        if (!empty($normalizedSelects)) {
            $this->dimensionContentQueryEnhancer->addSelects(
                $queryBuilder,
                EventDimensionContent::class,
                ['locale' => $locale, 'stage' => $stage],
                $normalizedSelects
            );
        } else {
            $queryBuilder->addSelect('dimensionContent');
        }
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function applyFilters(QueryBuilder $queryBuilder, array $filters): void
    {
        if (isset($filters['uuid'])) {
            $queryBuilder->andWhere('event.uuid = :uuid');
            $queryBuilder->setParameter('uuid', $filters['uuid']);
        }

        if (isset($filters['uuids'])) {
            $queryBuilder->andWhere('event.uuid IN (:uuids)');
            $queryBuilder->setParameter('uuids', $filters['uuids']);
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
     *     uuid?: 'asc'|'desc',
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
                case 'uuid':
                    $queryBuilder->addOrderBy('event.uuid', $direction);
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
