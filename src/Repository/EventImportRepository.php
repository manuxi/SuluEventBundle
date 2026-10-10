<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluEventBundle\Entity\EventImport;

/**
 * @extends ServiceEntityRepository<EventImport>
 */
class EventImportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventImport::class);
    }

    public function findBySourceAndExternalId(string $source, string $externalId): ?EventImport
    {
        return $this->findOneBy(['source' => $source, 'externalId' => $externalId]);
    }

    /**
     * Entries of other sources (or other candidates) for the same event, found by the key that joins series and year
     * ("ces-2027").
     *
     * @return EventImport[]
     */
    public function findLinkedByMatchKey(string $matchKey): array
    {
        return $this->createQueryBuilder('import')
            ->where('import.matchKey = :matchKey')
            ->andWhere('import.event IS NOT NULL')
            ->andWhere('import.state != :rejected')
            ->setParameter('matchKey', $matchKey)
            ->setParameter('rejected', EventImport::STATE_REJECTED)
            ->orderBy('import.priority', 'DESC')
            ->addOrderBy('import.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Entries with this match key whose event was deleted in the admin: the same event is not created again from
     * another source.
     *
     * @return EventImport[]
     */
    public function findRejectedByMatchKey(string $matchKey): array
    {
        return $this->findBy(['matchKey' => $matchKey, 'state' => EventImport::STATE_REJECTED]);
    }

    /**
     * @return EventImport[]
     */
    public function findByEventUuid(string $uuid): array
    {
        return $this->createQueryBuilder('import')
            ->where('IDENTITY(import.event) = :uuid')
            ->setParameter('uuid', $uuid)
            ->orderBy('import.priority', 'DESC')
            ->addOrderBy('import.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return EventImport[]
     */
    public function findBySource(string $source): array
    {
        return $this->findBy(['source' => $source]);
    }

    /**
     * The entries that belonged to an event with this uuid before it was deleted (the foreign key is empty then).
     *
     * @return EventImport[]
     */
    public function findByLastEventUuid(string $uuid): array
    {
        return $this->findBy(['lastEventUuid' => $uuid]);
    }

    public function add(EventImport $import): void
    {
        $this->getEntityManager()->persist($import);
    }
}
