<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Repository;

use DateTime;
use Doctrine\ORM\QueryBuilder;

class EventPendingDataProviderRepository extends EventRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $locale
     * @param mixed[] $options
     *
     * @return string[]
     */
    protected function append(QueryBuilder $queryBuilder, string $alias, string $locale, $options = []): array
    {

        $queryBuilder->andWhere($alias . '.enabled = true');
        $queryBuilder->andWhere('('. $alias .'.startDate >= :now OR ('. $alias .'.endDate IS NOT NULL AND '. $alias .'.endDate >= :now))');
        $queryBuilder->setParameter("now", (new Datetime())->format("Y-m-d H:i:s"));
        $queryBuilder->orderBy($alias . ".startDate", "ASC");

        return [];
    }
}