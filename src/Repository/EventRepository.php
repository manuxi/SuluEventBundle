<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Repository;

use Datetime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
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
        findByFilters as protected parentFindByFilters;
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
            ->where('translation.published = :published')->setParameter('published', 1)
            ->andWhere('translation.locale = :locale')->setParameter('locale', $locale)
            ->orderBy('translation.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $this->prepareFilter($queryBuilder, []);

        $abbreviations = $queryBuilder->getQuery()->getResult();
        if (!$abbreviations) {
            return [];
        }

        return $abbreviations;
    }

    public function countForSitemap()
    {
        $query = $this->createQueryBuilder('e')
            ->select('count(e)')
            ->where('e.published = :published')
            ->setParameter('published', 1);

        return $query->getQuery()->getSingleScalarResult();
    }

/*    public static function createEnabledCriteria(): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('enabled', true))
        ;
    }*/

    public function findAllScheduledEvents(int $limit)
    {
        $now = new \DateTimeImmutable();
        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder
            ->where('e.published = :published')
            ->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->gte('e.startDate', ':now'),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->isNotNull('e.endDate'),
                        $queryBuilder->expr()->gte('e.endDate', ':now')
                    )
                )
            )
            ->orderBy('e.startDate', 'ASC')
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
        //$queryBuilder->andWhere($alias.'.published = true');
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
            ->where('event.published = :published')
            ->setParameter('published', 1)
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
            ->where('translation.published = :published')->setParameter('published', 1)
            ->andWhere('translation.locale = :locale')->setParameter('locale', $locale)
            ->orderBy('translation.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($pageCurrent * $limit);

        $this->prepareFilter($queryBuilder, $filters);

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

    public function getActiveEvents(array $filters, string $locale, ?int $page, $pageSize, $limit = null, array $options = []): array
    {
        // Initialize the query builder
        $queryBuilder = $this->createQueryBuilder('event')
            ->leftJoin('event.translations', 'translation')
            ->where('event.published = :published')
            ->andWhere('translation.locale = :locale')
            ->setParameter('published', 1)
            ->setParameter('locale', $locale)
            ->orderBy('event.startDate', 'DESC');

        // Apply additional filters
        $this->prepareFilters($queryBuilder, $filters);

        // Apply offset/max results
        if (!$this->setOffsetResults($queryBuilder, $page, $pageSize, $limit)) {
            return [];
        }

        // Execute the query and return results
        $events = $queryBuilder->getQuery()->getResult();

        return $events ?: [];
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

    private function prepareTagsFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        if (!empty($filters['tags'])) {
            $queryBuilder->leftJoin('excerpt_translation.tags', 'tags');

            $i = 0;
            if ('and' === $filters['tagOperator']) {
                $andWhere = '';
                foreach ($filters['tags'] as $tag) {
                    if (0 === $i) {
                        $andWhere .= 'tags = :tag'.$i;
                    } else {
                        $andWhere .= ' AND tags = :tag'.$i;
                    }
                    $queryBuilder->setParameter('tag'.$i, $tag);
                    ++$i;
                }
                $queryBuilder->andWhere($andWhere);
            } elseif ('or' === $filters['tagOperator']) {
                $orWhere = '';
                foreach ($filters['tags'] as $tag) {
                    if (0 === $i) {
                        $orWhere .= 'tags = :tag'.$i;
                    } else {
                        $orWhere .= ' OR tags = :tag'.$i;
                    }
                    $queryBuilder->setParameter('tag'.$i, $tag);
                    ++$i;
                }
                $queryBuilder->andWhere($orWhere);
            }
        }
    }

    private function prepareCategoriesFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        if (!empty($filters['categories'])) {
            $queryBuilder->leftJoin('excerpt_translation.categories', 'categories');

            $i = 0;
            if ('and' === $filters['categoryOperator']) {
                $andWhere = '';
                foreach ($filters['categories'] as $category) {
                    if (0 === $i) {
                        $andWhere .= 'categories = :category'.$i;
                    } else {
                        $andWhere .= ' AND categories = :category'.$i;
                    }
                    $queryBuilder->setParameter('category'.$i, $category);
                    ++$i;
                }
                $queryBuilder->andWhere($andWhere);
            } elseif ('or' === $filters['categoryOperator']) {
                $orWhere = '';
                foreach ($filters['categories'] as $category) {
                    if (0 === $i) {
                        $orWhere .= 'categories = :category'.$i;
                    } else {
                        $orWhere .= ' OR categories = :category'.$i;
                    }
                    $queryBuilder->setParameter('category'.$i, $category);
                    ++$i;
                }
                $queryBuilder->andWhere($orWhere);
            }
        }
    }

    private function prepareTypesFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        if (!empty($filters['types'])) {
            // if pending and expired both are selected, we dont need them.
            if (in_array('pending', $filters['types']) and in_array('expired', $filters['types'])) {
                return;
            }

            foreach ($filters['types'] as $index => $type) {
                if ('pending' == $type) {
                    $queryBuilder->andWhere('(event.startDate >= :now OR (event.endDate IS NOT NULL AND event.endDate >= :now))');
                    $queryBuilder->setParameter('now', (new \Datetime())->format('Y-m-d H:i:s'));
                } elseif ('expired' == $type) {
                    $queryBuilder->andWhere('(event.startDate < :now and (event.endDate IS NULL OR event.endDate < :now))');
                    $queryBuilder->setParameter('now', (new \Datetime())->format('Y-m-d H:i:s'));
                }
            }
        }
    }
}
