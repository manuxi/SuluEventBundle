<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluEventBundle\Entity\EventSeo;

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

    public function save(EventSeo $eventSeo): EventSeo
    {
        $this->getEntityManager()->persist($eventSeo);
        $this->getEntityManager()->flush();

        return $eventSeo;
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
}
