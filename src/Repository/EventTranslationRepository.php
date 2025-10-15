<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluEventBundle\Entity\EventTranslation;

/**
 * @method EventTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventTranslation[]    findAll()
 * @method EventTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<EventTranslation>
 */
class EventTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventTranslation::class);
    }

    public function findMissingLocaleByIds(array $ids, string $missingLocale, int $countLocales)
    {
        $query = $this->createQueryBuilder('et')
            ->addCriteria($this->createIdsInCriteria($ids))
            ->groupBy('et.event, et.locale')
            ->having('COUNT(et.event) < :countLocales')
            ->setParameter('countLocales', $countLocales)
            ->andHaving('et.locale = :locale')
            ->setParameter('locale', $missingLocale)
            ->select('IDENTITY(et.event) as event, et.locale, count(et.event) as eventCount')
            ->getQuery()
        ;

        return $query->getResult();
    }

    private function createIdsInCriteria(array $ids): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->in('event', $ids))
        ;
    }
}
