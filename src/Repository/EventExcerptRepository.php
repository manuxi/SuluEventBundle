<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluEventBundle\Entity\EventExcerpt;

/**
 * @method EventExcerpt|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventExcerpt|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventExcerpt[]    findAll()
 * @method EventExcerpt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Event>
 */
class EventExcerptRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventExcerpt::class);
    }

    public function create(string $locale): EventExcerpt
    {
        $eventExcerpt = new EventExcerpt();
        $eventExcerpt->setLocale($locale);

        return $eventExcerpt;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(int $id): void
    {
        /** @var object $eventExcerpt */
        $eventExcerpt = $this->getEntityManager()->getReference(
            $this->getClassName(),
            $id
        );

        $this->getEntityManager()->remove($eventExcerpt);
        $this->getEntityManager()->flush();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(EventExcerpt $eventExcerpt): void
    {
        $this->getEntityManager()->persist($eventExcerpt);
        $this->getEntityManager()->flush();
    }

    public function findById(int $id, string $locale): ?EventExcerpt
    {
        $eventExcerpt = $this->find($id);
        if (!$eventExcerpt) {
            return null;
        }

        $eventExcerpt->setLocale($locale);

        return $eventExcerpt;
    }

}
