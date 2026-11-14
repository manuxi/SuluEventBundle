<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluEventBundle\Entity\EventImportRun;

/**
 * @extends ServiceEntityRepository<EventImportRun>
 */
class EventImportRunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventImportRun::class);
    }

    /**
     * The newest finished run of the import (not of the automatic publishing), dry runs excluded unless asked for.
     */
    public function findLast(bool $includeDryRuns = false): ?EventImportRun
    {
        $queryBuilder = $this->createQueryBuilder('run')
            ->where('run.finishedAt IS NOT NULL')
            ->andWhere('run.source IS NULL OR run.source != :autoPublish')
            ->setParameter('autoPublish', EventImportRun::SOURCE_AUTO_PUBLISH)
            ->orderBy('run.startedAt', 'DESC')
            ->addOrderBy('run.id', 'DESC')
            ->setMaxResults(1);

        if (!$includeDryRuns) {
            $queryBuilder->andWhere('run.dryRun = :dryRun')->setParameter('dryRun', false);
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * The newest finished run of the automatic publishing, dry runs excluded unless asked for.
     */
    public function findLastAutoPublish(bool $includeDryRuns = false): ?EventImportRun
    {
        $queryBuilder = $this->createQueryBuilder('run')
            ->where('run.finishedAt IS NOT NULL')
            ->andWhere('run.source = :autoPublish')
            ->setParameter('autoPublish', EventImportRun::SOURCE_AUTO_PUBLISH)
            ->orderBy('run.startedAt', 'DESC')
            ->addOrderBy('run.id', 'DESC')
            ->setMaxResults(1);

        if (!$includeDryRuns) {
            $queryBuilder->andWhere('run.dryRun = :dryRun')->setParameter('dryRun', false);
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function add(EventImportRun $run): void
    {
        $this->getEntityManager()->persist($run);
    }

    /**
     * Keeps the newest runs and removes the older ones, so the table does not grow without end.
     */
    public function prune(int $keep = 100): int
    {
        $ids = $this->createQueryBuilder('run')
            ->select('run.id')
            ->orderBy('run.startedAt', 'DESC')
            ->addOrderBy('run.id', 'DESC')
            ->setFirstResult($keep)
            ->getQuery()
            ->getSingleColumnResult();

        if ([] === $ids) {
            return 0;
        }

        return (int) $this->createQueryBuilder('run')
            ->delete()
            ->where('run.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
}
