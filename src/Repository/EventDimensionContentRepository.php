<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;

/**
 * @extends ServiceEntityRepository<EventDimensionContent>
 */
class EventDimensionContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventDimensionContent::class);
    }
    public function findMissingLocaleByIds(array $ids, string $locale, int $localesCount = 0): array
    {
        if (empty($ids)) {
            return [];
        }

        $qb = $this->createQueryBuilder('dimensionContent');
        $qb->select('identity(dimensionContent.event) as event')
            ->where($qb->expr()->in('dimensionContent.event', $ids))
            ->andWhere('dimensionContent.locale = :locale')
            ->setParameter('locale', $locale);

        return $qb->getQuery()->getArrayResult();
    }
}