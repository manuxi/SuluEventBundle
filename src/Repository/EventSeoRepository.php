<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Repository;

use Manuxi\SuluEventBundle\Entity\EventSeo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EventSeo|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventSeo|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventSeo[]    findAll()
 * @method EventSeo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Event>
 */
class EventSeoRepository extends ServiceEntityRepository
{
//    use DataProviderRepositoryTrait {
//        findByFilters as parentFindByFilters;
//    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventSeo::class);
    }

    public function create(string $locale): EventSeo
    {
        $eventSeo = new EventSeo();
        $eventSeo->setLocale($locale);

        return $eventSeo;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(int $id): void
    {
        /** @var object $eventSeo */
        $eventSeo = $this->getEntityManager()->getReference(
            $this->getClassName(),
            $id
        );

        $this->getEntityManager()->remove($eventSeo);
        $this->getEntityManager()->flush();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(EventSeo $eventSeo): void
    {
        $this->getEntityManager()->persist($eventSeo);
        $this->getEntityManager()->flush();
    }

    public function findById(int $id, string $locale): ?EventSeo
    {
        $eventSeo = $this->find($id);
        if (!$eventSeo) {
            return null;
        }

        $eventSeo->setLocale($locale);

        return $eventSeo;
    }

//    /**
//     * @param mixed[] $filters
//     */
//    public function findByFilters($filters, $page, $pageSize, $limit, $locale, $options = [])
//    {
//        $entities = $this->parentFindByFilters($filters, $page, $pageSize, $limit, $locale, $options);
//
//        return array_map(
//            function (EventSeo $entity) use ($locale) {
//                return $entity->setLocale($locale);
//            },
//            $entities
//        );
//    }
//
//    protected function appendJoins(QueryBuilder $queryBuilder, string $alias, string $locale): void
//    {
//        $queryBuilder->innerJoin($alias . '.translations', 'translation', Join::WITH, 'translation.locale = :locale');
//        $queryBuilder->setParameter('locale', $locale);
//    }
}
