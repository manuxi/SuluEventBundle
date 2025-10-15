<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluEventBundle\Entity\EventExcerptTranslation;

/**
 * @method EventExcerptTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventExcerptTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventExcerptTranslation[]    findAll()
 * @method EventExcerptTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<EventTranslation>
 */
class EventExcerptTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventExcerptTranslation::class);
    }
}
