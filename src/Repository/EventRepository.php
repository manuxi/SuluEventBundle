<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Repository;

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
    use DataProviderRepositoryTrait;

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

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
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

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Event $event): void
    {
        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush();
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

    /**
     * Returns filtered entities.
     * When pagination is active the result count is pageSize + 1 to determine has next page.
     *
     * @param array   $filters  array of filters: tags, tagOperator
     * @param int     $page
     * @param int     $pageSize
     * @param int     $limit
     * @param string  $locale
     * @param mixed[] $options
     *
     * @return object[]
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection PhpMissingParamTypeInspection
     */
    public function findByFilters($filters, $page, $pageSize, $limit, $locale, $options = [])
    {
        $entities = $this->parentFindByFilters($filters, $page, $pageSize, $limit, $locale, $options);

        return \array_map(
            function (Event $entity) use ($locale) {
                return $entity->setLocale($locale);
            },
            $entities
        );
    }

    protected function appendJoins(QueryBuilder $queryBuilder, string $alias, string $locale): void
    {
        $queryBuilder->innerJoin($alias . '.translations', 'translation', Join::WITH, 'translation.locale = :locale');
        $queryBuilder->setParameter('locale', $locale);

        $queryBuilder->andWhere($alias . '.enabled = true');
    }

    /**
     * Copied temporarily from DataProviderRepositoryTrait since the following code threw
     * ReflectionException 'The parameter specified by its name could not be found':
     * use DataProviderRepositoryTrait {
     *   findByFilters as parentFindByFilters;
     * }
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
     */
    public function parentFindByFilters(
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
        $alias = 'entity';
        $queryBuilder = $this->createQueryBuilder($alias)
            ->addSelect($alias)
            ->where($alias . '.id IN (:ids)')
            ->orderBy($alias . '.id', 'ASC');
        $this->appendJoins($queryBuilder, $alias, $locale);

        if (isset($filters['sortBy'])) {
            $sortMethod = $filters['sortMethod'] ?? 'asc';
            $sortBy = false !== \strpos($filters['sortBy'], '.') ? $filters['sortBy'] : $alias . '.' . $filters['sortBy'];

            $this->appendSortBy($sortBy, $sortMethod, $queryBuilder, $alias, $locale);
        }

        $query = $queryBuilder->getQuery();
        $ids = $this->findByFiltersIds(
            $filters,
            $page,
            $pageSize,
            $limit,
            $locale,
            $options,
            $user,
            $entityClass,
            $entityAlias,
            $permission
        );
        $query->setParameter('ids', $ids);

        return $query->getResult();
    }
}
