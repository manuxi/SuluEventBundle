<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Repository;

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
        $query = $this->createQueryBuilder('e')
            ->where('e.enabled = 1 AND (e.startDate >= :now OR (e.endDate IS NOT NULL AND e.endDate >= :now))')
            ->orderBy("e.startDate", "ASC")
            ->setMaxResults($limit)
            ->setParameter("now", (new \DateTimeImmutable())->format("Y-m-d"));
        return $query->getQuery()->getResult();
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
    public function findByFilters(
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
    }

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

}
