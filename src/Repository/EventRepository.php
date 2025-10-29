<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Repository;

use Datetime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryTrait;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository implements DataProviderRepositoryInterface
{
    use DataProviderRepositoryTrait {
        DataProviderRepositoryTrait::findByFilters as protected parentFindByFilters;
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function create(string $locale): Event
    {
        $event = new Event();
        $event->setLocale($locale);

        return $event;
    }

    public function remove(int $id): void
    {
        /** @var object $event */
        $event = $this->getEntityManager()->getReference(
            $this->getClassName(),
            $id
        );

        $this->getEntityManager()->remove($event);
        $this->getEntityManager()->flush();
    }

    public function save(Event $event): Event
    {
        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush();

        return $event;
    }

    public function publish(Event $entity): Event
    {
        $entity->setPublished(true);

        return $this->save($entity);
    }

    public function unpublish(Event $entity): Event
    {
        $entity->setPublished(false);

        return $this->save($entity);
    }

    public function findById(int $id, string $locale): ?Event
    {
        $event = $this->find($id);

        if (!$event) {
            return null;
        }

        $event->setLocale($locale);

        return $event;
    }

    public function findByDateRange(
        string $locale,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        bool $publishedOnly = true,
    ): array {
        $queryBuilder = $this->createQueryBuilder('event')
            ->leftJoin('event.translations', 'translation')
            ->andWhere('translation.locale = :locale')
            ->setParameter('locale', $locale);

        if ($publishedOnly) {
            $queryBuilder
                ->andWhere('translation.published = :published')
                ->setParameter('published', true);
        }

        // Events that start OR end within the range
        $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->orX(
                    // Event starts within range
                    $queryBuilder->expr()->between('event.startDate', ':startDate', ':endDate'),
                    // Event ends within range
                    $queryBuilder->expr()->between('event.endDate', ':startDate', ':endDate'),
                    // Event spans the entire range
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->lte('event.startDate', ':startDate'),
                        $queryBuilder->expr()->gte('event.endDate', ':endDate')
                    )
                )
            )
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('event.startDate', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Find all recurring events (with EventRecurrence relationship).
     *
     * @return Event[]
     */
    public function findRecurringEvents(): array
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.eventRecurrence', 'r')
            ->where('r.isRecurring = :recurring')
            ->setParameter('recurring', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find events for calendar display with optional filters
     * Supports date range, locale, categories, tags, and location filters.
     *
     * @param array $filters {
     *
     * @var string|null $locale       Filter by locale (e.g., 'de', 'en')
     * @var string|null $start        Start date (ISO format)
     * @var string|null $end          End date (ISO format)
     * @var array|null  $categories   Category IDs to filter
     * @var array|null  $tags         Tag IDs to filter
     * @var string|null $location     Location name filter
     * @var int|null    $dataId       Folder/page ID filter
     * @var bool        $includeSubFolders Include subfolders in dataId filter
     * @var string      $sortBy       Sort field (default: 'startDate')
     * @var string      $sortMethod   Sort direction (default: 'asc')
     *                  }
     *
     * @return Event[]
     */
    public function findForCalendar(array $filters): array
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.location', 'loc')
            ->where('e.published = :published')
            ->setParameter('published', true);

        // Date range filters
        if (!empty($filters['start'])) {
            try {
                $startDate = new \Datetime($filters['start']);
                $qb->andWhere('e.endDate >= :start OR (e.endDate IS NULL AND e.startDate >= :start)')
                    ->setParameter('start', $startDate);
            } catch (\Exception $e) {
                // Invalid date format - skip filter
            }
        }

        if (!empty($filters['end'])) {
            try {
                $endDate = new \Datetime($filters['end']);
                $qb->andWhere('e.startDate <= :end')
                    ->setParameter('end', $endDate);
            } catch (\Exception $e) {
                // Invalid date format - skip filter
            }
        }

        // Locale filter
        if (!empty($filters['locale'])) {
            $qb->andWhere('e.locale = :locale')
                ->setParameter('locale', $filters['locale']);
        }

        // Category filter
        if (!empty($filters['categories']) && is_array($filters['categories'])) {
            $qb->innerJoin('e.categories', 'c')
                ->andWhere('c.id IN (:categories)')
                ->setParameter('categories', $filters['categories']);
        }

        // Tag filter
        if (!empty($filters['tags']) && is_array($filters['tags'])) {
            $qb->innerJoin('e.tags', 't')
                ->andWhere('t.id IN (:tags)')
                ->setParameter('tags', $filters['tags']);
        }

        // Location filter
        if (!empty($filters['location'])) {
            $qb->andWhere('loc.name LIKE :location')
                ->setParameter('location', '%'.$filters['location'].'%');
        }

        // Folder/dataId filter (from content block)
        if (!empty($filters['dataId'])) {
            if (!empty($filters['includeSubFolders'])) {
                // Include subfolders - use LIKE for path matching
                $qb->andWhere('e.route LIKE :dataPath')
                    ->setParameter('dataPath', '%/'.$filters['dataId'].'/%');
            } else {
                // Exact folder only
                $qb->andWhere('e.parent = :dataId')
                    ->setParameter('dataId', $filters['dataId']);
            }
        }

        // Sorting
        $sortBy = $filters['sortBy'] ?? 'startDate';
        $sortMethod = strtoupper($filters['sortMethod'] ?? 'ASC');

        // Map sort fields to actual entity properties
        $sortFieldMap = [
            'startDate' => 'e.startDate',
            'title' => 'e.title',
            'created' => 'e.created',
            'changed' => 'e.changed',
        ];

        $sortField = $sortFieldMap[$sortBy] ?? 'e.startDate';
        $qb->orderBy($sortField, 'DESC' === $sortMethod ? 'DESC' : 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Find events for iCal export
     * Uses same filtering as calendar view.
     *
     * @param array $filters Same as findForCalendar()
     *
     * @return Event[]
     */
    public function findForIcal(array $filters): array
    {
        return $this->findForCalendar($filters);
    }

    /**
     * Find published events for RSS/Atom feeds.
     *
     * @param int $limit Maximum number of events to return
     *
     * @return Event[]
     */
    public function findForFeed(int $limit = 50): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.published = :published')
            ->setParameter('published', true)
            ->orderBy('e.startDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findAllForSitemapOld(int $page, int $limit): array
    {
        $offset = ($page * $limit) - $limit;
        $criteria = [
            'enabled' => true,
        ];

        return $this->findBy($criteria, [], $limit, $offset);
    }

    public function findAllForSitemap(string $locale, ?int $limit = null, ?int $offset = null): array
    {
        $queryBuilder = $this->createQueryBuilder('event')
            ->leftJoin('event.translations', 'translation')
            ->where('translation.published = :published')
            ->setParameter('published', true)
            ->andWhere('translation.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('translation.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $this->prepareFilters($queryBuilder, []);

        $abbreviations = $queryBuilder->getQuery()->getResult();
        if (!$abbreviations) {
            return [];
        }

        return $abbreviations;
    }

    public function countForSitemap(string $locale)
    {
        $query = $this->createQueryBuilder('event')
            ->select('count(event)')
            ->leftJoin('event.translations', 'translation')
            ->where('translation.published = :published')
            ->setParameter('published', true)
            ->andWhere('translation.locale = :locale')
            ->setParameter('locale', $locale);

        return $query->getQuery()->getSingleScalarResult();
    }

    public function findAllScheduledEvents(int $limit)
    {
        $now = new \DateTimeImmutable();
        $queryBuilder = $this->createQueryBuilder('event');
        $queryBuilder
            ->leftJoin('event.translations', 'translation')
            ->where('translation.published = :published')
            ->setParameter('published', true)
            ->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->gte('event.startDate', ':now'),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->isNotNull('event.endDate'),
                        $queryBuilder->expr()->gte('event.endDate', ':now')
                    )
                )
            )
            ->orderBy('event.startDate', 'ASC')
            ->setMaxResults($limit)
            ->setParameter('published', 1)
            ->setParameter('now', $now->format('Y-m-d'));

        return $queryBuilder->getQuery()->getResult();
    }

    protected function appendJoins(QueryBuilder $queryBuilder, $alias, $locale): void
    {
    }

    /**
     * @param mixed[] $options
     *
     * @return string[]
     */
    protected function append(QueryBuilder $queryBuilder, string $alias, string $locale, $options = []): array
    {
        $queryBuilder->innerJoin($alias.'.translations', 'translation', Join::WITH, 'translation.locale = :locale');
        $queryBuilder->setParameter('locale', $locale);
        $queryBuilder->andWhere('translation.published = :published');
        $queryBuilder->setParameter('published', true);
        // $queryBuilder->andWhere($alias.'.published = true');
        /*        $queryBuilder->andWhere('('. $alias .'.startDate >= :now OR ('. $alias .'.endDate IS NOT NULL AND '. $alias .'.endDate >= :now))');
                $queryBuilder->setParameter("now", (new Datetime())->format("Y-m-d H:i:s"));
                $queryBuilder->orderBy($alias . ".startDate", "ASC");*/

        return [];
    }

    public function appendCategoriesRelation(QueryBuilder $queryBuilder, $alias)
    {
        return $alias.'.category';
        // $queryBuilder->addSelect($alias.'.category');
    }

    protected function appendSortByJoins(QueryBuilder $queryBuilder, string $alias, string $locale): void
    {
        $queryBuilder->innerJoin($alias.'.translations', 'translation', Join::WITH, 'translation.locale = :locale');
        $queryBuilder->setParameter('locale', $locale);
    }

    public function hasNextPage(array $filters, ?int $page, ?int $pageSize, ?int $limit, string $locale, array $options = []): bool
    {
        // $pageCurrent = (key_exists('page', $options)) ? (int)$options['page'] : 0;

        $queryBuilder = $this->createQueryBuilder('event')
            ->select('count(event.id)')
            ->leftJoin('event.translations', 'translation')
            ->where('translation.published = :published')
            ->setParameter('published', true)
            ->andWhere('translation.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('event.startDate', 'DESC');

        $this->prepareFilters($queryBuilder, $filters);

        $eventsCount = $queryBuilder->getQuery()->getSingleScalarResult();

        $pos = (int) ($pageSize * $page);
        if (null !== $limit && $limit <= $pos) {
            return false;
        } elseif ($pos < (int) $eventsCount) {
            return true;
        }

        return false;
    }

    public function findByFilters($filters, $page, $pageSize, $limit, $locale, $options = []): array
    {
        $entities = $this->getPublishedEvents($filters, $locale, $page, $pageSize, $limit, $options);

        return \array_map(
            function (Event $entity) use ($locale) {
                return $entity->setLocale($locale);
            },
            $entities
        );
    }

    public function getPublishedEvents(array $filters, string $locale, ?int $page, $pageSize, $limit = null, array $options = []): array
    {
        $pageCurrent = (key_exists('page', $options)) ? (int) $options['page'] : 0;

        $queryBuilder = $this->createQueryBuilder('event')
            ->leftJoin('event.translations', 'translation')
            ->where('translation.published = :published')
            ->setParameter('published', true)
            ->andWhere('translation.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('translation.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($pageCurrent * $limit);

        $this->prepareFilters($queryBuilder, $filters);

        // Apply offset/max results
        if (!$this->setOffsetResults($queryBuilder, $page, $pageSize, $limit)) {
            return [];
        }

        $events = $queryBuilder->getQuery()->getResult();
        if (!$events) {
            return [];
        }

        return $events;
    }

    private function setOffsetResults(QueryBuilder $queryBuilder, $page, $pageSize, $limit = null): bool
    {
        if (null !== $page && $pageSize > 0) {
            $pageOffset = ($page - 1) * $pageSize;
            $restLimit = $limit - $pageOffset;

            $maxResults = (null !== $limit && $pageSize > $restLimit ? $restLimit : $pageSize);

            if ($maxResults <= 0) {
                return false;
            }

            $queryBuilder->setMaxResults($maxResults);
            $queryBuilder->setFirstResult($pageOffset);
        } elseif (null !== $limit) {
            $queryBuilder->setMaxResults($limit);
        }

        return true;
    }

    private function prepareFilters(QueryBuilder $queryBuilder, array $filters): void
    {
        if (isset($filters['sortBy'])) {
            $queryBuilder->orderBy($filters['sortBy'], $filters['sortMethod']);
        }

        if (!empty($filters['tags']) || !empty($filters['categories'])) {
            $queryBuilder->leftJoin('event.eventExcerpt', 'excerpt')
                ->leftJoin('excerpt.translations', 'excerpt_translation');
        }
        $this->prepareTypesFilter($queryBuilder, $filters);
        $this->prepareTagsFilter($queryBuilder, $filters);
        $this->prepareCategoriesFilter($queryBuilder, $filters);
    }

    private function prepareTypesFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        if (empty($filters['types'])) {
            return;
        }

        $hasPending = in_array('pending', $filters['types'], true);
        $hasExpired = in_array('expired', $filters['types'], true);

        // if pending and expired both are selected, we don't need them.
        if ($hasPending && $hasExpired) {
            return;
        }

        $now = new \Datetime();
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

    private function prepareTagsFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        if (empty($filters['tags'])) {
            return;
        }

        $operator = $filters['tagOperator'] ?? 'or';

        if ('and' === $operator) {
            // AND: Entity must have ALL tags (multiple JOINs necessary)
            foreach ($filters['tags'] as $i => $tag) {
                $alias = 'tag'.$i;
                $queryBuilder
                    ->innerJoin('excerpt_translation.tags', $alias)
                    ->andWhere($queryBuilder->expr()->eq($alias.'.id', ':tag'.$i))
                    ->setParameter('tag'.$i, $tag);
            }
        } else {
            // OR: Entity must at least have one of the tags
            $queryBuilder
                ->leftJoin('excerpt_translation.tags', 'tags')
                ->andWhere($queryBuilder->expr()->in('tags.id', ':tags'))
                ->setParameter('tags', $filters['tags']);
        }
    }

    private function prepareCategoriesFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        if (empty($filters['categories'])) {
            return;
        }

        $operator = $filters['categoryOperator'] ?? 'or';

        if ('and' === $operator) {
            // AND: Entity must have ALL categories (multiple JOINs necessary)
            $queryBuilder->leftJoin('excerpt_translation.categories', 'categories');

            foreach ($filters['categories'] as $i => $category) {
                $alias = 'category'.$i;
                $queryBuilder
                    ->innerJoin('excerpt_translation.categories', $alias)
                    ->andWhere($queryBuilder->expr()->eq($alias.'.id', ':category'.$i))
                    ->setParameter('category'.$i, $category);
            }
        } else {
            // OR: Entity must at least have one of the categories
            $queryBuilder
                ->leftJoin('excerpt_translation.categories', 'categories')
                ->andWhere($queryBuilder->expr()->in('categories.id', ':categories'))
                ->setParameter('categories', $filters['categories']);
        }
    }
}
