<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Repository;

use Datetime;
use Doctrine\Common\Collections\Criteria;
use Manuxi\SuluEventBundle\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Sulu\Component\Security\Authentication\UserInterface;
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

    public function findById(int $id, string $locale): ?Event
    {
        $event = $this->find($id);

        if (!$event) {
            return null;
        }

        $event->setLocale($locale);

        return $event;
    }

    public function findAllForSitemap(int $page, int $limit): array
    {
        $offset = ($page * $limit) - $limit;
        $criteria = [
            'enabled' => true,
        ];
        return $this->findBy($criteria, [], $limit, $offset);
    }

    public function countForSitemap()
    {
        $query = $this->createQueryBuilder('e')
            ->select('count(e)');
        return $query->getQuery()->getSingleScalarResult();
    }

    public static function createEnabledCriteria(): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('enabled', true))
            ;
    }

    public function findAllScheduledEvents(int $limit)
    {
        $now = new \DateTimeImmutable();
        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder
            ->where('e.enabled = :enabled')
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
            ->setParameter('enabled', 1)
            ->setParameter('now', $now->format('Y-m-d'));

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Returns filtered entities.
     * When pagination is active the result count is pageSize + 1 to determine has next page.
     *
     * @param array $filters array of filters: tags, tagOperator
     * @param int $page
     * @param int $pageSize
     * @param int $limit
     * @param string $locale
     * @param mixed[] $options
     * @param UserInterface|null $user
     * @param null $entityClass
     * @param null $entityAlias
     * @param null $permission
     * @return object[]
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection PhpMissingParamTypeInspection
     */
    /*    public function findByFilters(
            $filters,
            $page,
            $pageSize,
            $limit,
            $locale,
            $options = [],
            ?UserInterface $user = null,
            $entityClass = null,
            $entityAlias = null,
            $permission = null
        ) {
            $entities = $this->parentFindByFilters($filters, $page, $pageSize, $limit, $locale, $options);

            return \array_map(
                function (Event $entity) use ($locale) {
                    return $entity->setLocale($locale);
                },
                $entities
            );
        }*/

    protected function appendJoins(QueryBuilder $queryBuilder, $alias, $locale): void
    {

    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $locale
     * @param mixed[] $options
     *
     * @return string[]
     */
    protected function append(QueryBuilder $queryBuilder, string $alias, string $locale, $options = []): array
    {

        $queryBuilder->andWhere($alias . '.enabled = true');
        /*        $queryBuilder->andWhere('('. $alias .'.startDate >= :now OR ('. $alias .'.endDate IS NOT NULL AND '. $alias .'.endDate >= :now))');
                $queryBuilder->setParameter("now", (new Datetime())->format("Y-m-d H:i:s"));
                $queryBuilder->orderBy($alias . ".startDate", "ASC");*/

        return [];
    }

    public function appendCategoriesRelation(QueryBuilder $queryBuilder, $alias)
    {
        return $alias . '.category';
        //$queryBuilder->addSelect($alias.'.category');
    }

    protected function appendSortByJoins(QueryBuilder $queryBuilder, string $alias, string $locale): void
    {
        $queryBuilder->innerJoin($alias . '.translations', 'translation', Join::WITH, 'translation.locale = :locale');
        $queryBuilder->setParameter('locale', $locale);
    }

    public function findByFilters($filters, $page, $pageSize, $limit, $locale, $options = []): array
    {
        $entities = $this->getActiveEvents($filters, $locale, $page, $pageSize, $limit, $options);

        return \array_map(
            function (Event $entity) use ($locale) {
                return $entity->setLocale($locale);
            },
            $entities
        );
    }

    public function getActiveEvents(array $filters, string $locale, ?int $page, $pageSize, $limit = null, array $options): array
    {
        // Determine the current page
        $pageCurrent = array_key_exists('page', $options) ? (int) $options['page'] : 0;

        // Initialize the query builder
        $queryBuilder = $this->createQueryBuilder('event')
            ->leftJoin('event.translations', 'translation')
            ->where('event.enabled = :enabled')
            ->andWhere('translation.locale = :locale')
            ->setParameter('enabled', 1)
            ->setParameter('locale', $locale)
            ->orderBy('event.startDate', 'DESC');

        // Apply limit and pagination
        if ($limit !== null) {
            $queryBuilder->setMaxResults($limit);
        }
        if ($pageCurrent !== null && $limit !== null) {
            $queryBuilder->setFirstResult($pageCurrent * $limit);
        }

        // Apply additional filters
        $this->prepareFilter($queryBuilder, $filters);

        // Execute the query and return results
        $events = $queryBuilder->getQuery()->getResult();

        return $events ?: [];
    }

    private function prepareFilter(QueryBuilder $queryBuilder, array $filters): void
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

    private function prepareTagsFilter(QueryBuilder $queryBuilder, array $filters):void
    {
        if (!empty($filters['tags'])) {

            $queryBuilder->leftJoin('excerpt_translation.tags', 'tags');

            $i = 0;
            if ($filters['tagOperator'] === "and") {
                $andWhere = "";
                foreach ($filters['tags'] as $tag) {
                    if ($i === 0) {
                        $andWhere .= "tags = :tag" . $i;
                    } else {
                        $andWhere .= " AND tags = :tag" . $i;
                    }
                    $queryBuilder->setParameter("tag" . $i, $tag);
                    $i++;
                }
                $queryBuilder->andWhere($andWhere);
            } else if ($filters['tagOperator'] === "or") {
                $orWhere = "";
                foreach ($filters['tags'] as $tag) {
                    if ($i === 0) {
                        $orWhere .= "tags = :tag" . $i;
                    } else {
                        $orWhere .= " OR tags = :tag" . $i;
                    }
                    $queryBuilder->setParameter("tag" . $i, $tag);
                    $i++;
                }
                $queryBuilder->andWhere($orWhere);
            }
        }
    }

    private function prepareCategoriesFilter(QueryBuilder $queryBuilder, array $filters):void
    {
        if (!empty($filters['categories'])) {

            $queryBuilder->leftJoin('excerpt_translation.categories', 'categories');

            $i = 0;
            if ($filters['categoryOperator'] === "and") {
                $andWhere = "";
                foreach ($filters['categories'] as $category) {
                    if ($i === 0) {
                        $andWhere .= "categories = :category" . $i;
                    } else {
                        $andWhere .= " AND categories = :category" . $i;
                    }
                    $queryBuilder->setParameter("category" . $i, $category);
                    $i++;
                }
                $queryBuilder->andWhere($andWhere);
            } else if ($filters['categoryOperator'] === "or") {
                $orWhere = "";
                foreach ($filters['categories'] as $category) {
                    if ($i === 0) {
                        $orWhere .= "categories = :category" . $i;
                    } else {
                        $orWhere .= " OR categories = :category" . $i;
                    }
                    $queryBuilder->setParameter("category" . $i, $category);
                    $i++;
                }
                $queryBuilder->andWhere($orWhere);
            }
        }
    }

    private function prepareTypesFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        if(!empty($filters['types'])) {

            //if pending and expired both are selected, we dont need them.
            if (in_array("pending", $filters['types']) and in_array("expired", $filters['types'])) {
                return;
            }

            foreach($filters['types'] as $index => $type) {
                if ($type == "pending") {
                    $queryBuilder->andWhere('(event.startDate >= :now OR (event.endDate IS NOT NULL AND event.endDate >= :now))');
                    $queryBuilder->setParameter("now", (new Datetime())->format("Y-m-d H:i:s"));
                } elseif ($type == "expired") {
                    $queryBuilder->andWhere('(event.startDate < :now and (event.endDate IS NULL OR event.endDate < :now))');
                    $queryBuilder->setParameter("now", (new Datetime())->format("Y-m-d H:i:s"));
                }
            }
        }
    }

}
