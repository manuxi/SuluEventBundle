<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Repository;

use Manuxi\SuluEventBundle\Entity\EventSeoTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EventSeoTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventSeoTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventSeoTranslation[]    findAll()
 * @method EventSeoTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<EventTranslation>
 */
class EventSeoTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventSeoTranslation::class);
    }
}
