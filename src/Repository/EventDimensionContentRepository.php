<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Sulu\Content\Domain\Model\DimensionContentInterface;

/**
 * @extends ServiceEntityRepository<EventDimensionContent>
 */
class EventDimensionContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventDimensionContent::class);
    }

    /**
     * Returns the current draft dimensions of the given events, one row per event and locale: the
     * unlocalized one (locale null) with its ghostLocale and the localized ones with the requested fields.
     *
     * A field is either a field of the dimension content ("title"), an association of it ("image", returns
     * the id) or a field of an associated entity ("location.name"). Fields that do not exist are left out.
     *
     * @param string[] $ids
     * @param string[] $fields
     *
     * @return array<array<string, mixed>> rows with the keys event, locale, ghostLocale and the given fields
     */
    public function findDraftContentsByIds(array $ids, array $fields = []): array
    {
        if ([] === $ids) {
            return [];
        }

        $qb = $this->createQueryBuilder('dimensionContent');
        $qb->select(
            'IDENTITY(dimensionContent.event) AS event',
            'dimensionContent.locale AS locale',
            'dimensionContent.ghostLocale AS ghostLocale',
        )
            ->where('dimensionContent.event IN (:ids)')
            ->andWhere('dimensionContent.stage = :stage')
            ->andWhere('dimensionContent.version = 0')
            ->setParameter('ids', $ids)
            ->setParameter('stage', DimensionContentInterface::STAGE_DRAFT);

        $metadata = $this->getClassMetadata();
        $aliases  = [];
        $joined   = [];
        foreach (array_values(array_unique($fields)) as $index => $field) {
            $alias  = 'field' . $index;
            $parts  = explode('.', $field);
            $select = null;

            if (1 === \count($parts) && $metadata->hasField($field)) {
                $select = 'dimensionContent.' . $field;
            } elseif (1 === \count($parts) && $metadata->isSingleValuedAssociation($field)) {
                $select = 'IDENTITY(dimensionContent.' . $field . ')';
            } elseif (2 === \count($parts) && $metadata->isSingleValuedAssociation($parts[0])) {
                [$association, $targetField] = $parts;
                $targetMetadata              = $this->getEntityManager()->getClassMetadata($metadata->getAssociationTargetClass($association));
                if ([$targetField] === $targetMetadata->getIdentifierFieldNames()) {
                    $select = 'IDENTITY(dimensionContent.' . $association . ')';
                } elseif ($targetMetadata->hasField($targetField)) {
                    $joinAlias = 'ghost_' . $association;
                    if (!isset($joined[$joinAlias])) {
                        $qb->leftJoin('dimensionContent.' . $association, $joinAlias);
                        $joined[$joinAlias] = true;
                    }
                    $select = $joinAlias . '.' . $targetField;
                }
            }

            if (null !== $select) {
                $qb->addSelect($select . ' AS ' . $alias);
                $aliases[$alias] = $field;
            }
        }

        $rows = [];
        foreach ($qb->getQuery()->getArrayResult() as $result) {
            $row = [
                'event'       => $result['event'],
                'locale'      => $result['locale'],
                'ghostLocale' => $result['ghostLocale'],
            ];
            foreach ($aliases as $alias => $field) {
                $row[$field] = $result[$alias];
            }
            $rows[] = $row;
        }

        return $rows;
    }
}
