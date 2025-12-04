<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Sulu\Bundle\AdminBundle\SmartContent\Configuration\Builder;
use Sulu\Bundle\AdminBundle\SmartContent\Configuration\BuilderInterface;
use Sulu\Bundle\AdminBundle\SmartContent\Configuration\ProviderConfigurationInterface;
use Sulu\Bundle\AdminBundle\SmartContent\SmartContentProviderInterface;
use Sulu\Bundle\AdminBundle\SmartContent\SmartContentQueryEnhancer;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Infrastructure\Doctrine\DimensionContentQueryEnhancer;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @phpstan-type EventSmartContentFilters array{
 *     categories: int[],
 *     categoryOperator: 'AND'|'OR',
 *     websiteCategories: string[],
 *     websiteCategoryOperator: 'AND'|'OR',
 *     tags: string[],
 *     tagOperator: 'AND'|'OR',
 *     websiteTags: string[],
 *     websiteTagOperator: 'AND'|'OR',
 *     types: string[],
 *     typesOperator: 'OR',
 *     locale: string,
 *     dataSource: string|null,
 *     limit: int|null,
 *     offset: int,
 *     includeSubFolders: bool,
 *     excludeDuplicates: bool,
 * }
 * @phpstan-type EventSmartContentCountFilters array{
 *     categories: int[],
 *     categoryOperator: 'AND'|'OR',
 *     websiteCategories: string[],
 *     websiteCategoryOperator: 'AND'|'OR',
 *     tags: string[],
 *     tagOperator: 'AND'|'OR',
 *     websiteTags: string[],
 *     websiteTagOperator: 'AND'|'OR',
 *     types: string[],
 *     typesOperator: 'OR',
 *     locale: string,
 *     dataSource: string|null,
 *     limit: int|null,
 *     includeSubFolders: bool,
 *     excludeDuplicates: bool,
 * }
 */
readonly class EventSmartContentProvider implements SmartContentProviderInterface
{
    /**
     * @var EntityRepository<Event>
     */
    private EntityRepository $entityRepository;

    /**
     * @var class-string<EventDimensionContent>
     */
    private string $eventDimensionContentClassName;

    /**
     * @param array<string, array{name: string, color: string}> $eventTypes
     */
    public function __construct(
        private DimensionContentQueryEnhancer $dimensionContentQueryEnhancer,
        private SmartContentQueryEnhancer $smartContentQueryEnhancer,
        EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
        private array $eventTypes = [],
    ) {
        $this->entityRepository = $entityManager->getRepository(Event::class);
        $entityDimensionContentRepository = $entityManager->getRepository(EventDimensionContent::class);
        $this->eventDimensionContentClassName = $entityDimensionContentRepository->getClassName();
    }

    public function getConfiguration(): ProviderConfigurationInterface
    {
        return $this->getConfigurationBuilder()->getConfiguration();
    }

    protected function getConfigurationBuilder(): BuilderInterface
    {
        return Builder::create()
            ->enableTags()
            ->enableCategories()
            ->enableLimit()
            ->enablePagination()
            ->enablePresentAs()
            ->enableSorting($this->getSorting())
            ->enableTypes($this->getTypes());
    }

    protected function getTypes(): array
    {
        $types = [
            ['type' => 'pending', 'title' => $this->translator->trans('sulu_event.filter.pending', [], 'admin')],
            ['type' => 'expired', 'title' => $this->translator->trans('sulu_event.filter.expired', [], 'admin')],
        ];

        foreach ($this->eventTypes as $key => $config) {
            $types[] = [
                'type' => $key,
                'title' => $this->translator->trans($config['name'], [], 'admin'),
            ];
        }

        return $types;
    }

    protected function getSorting(): array
    {
        return [
            ['column' => 'startDate', 'title' => 'sulu_event.sorting.start_date'],
            ['column' => 'endDate', 'title' => 'sulu_event.sorting.end_date'],
            ['column' => 'title', 'title' => 'sulu_event.title'],
            ['column' => 'workflowPublished', 'title' => 'sulu_admin.published'],
            ['column' => 'created', 'title' => 'sulu_admin.created'],
            ['column' => 'changed', 'title' => 'sulu_admin.changed'],
        ];
    }

    /**
     * @param EventSmartContentCountFilters $filters
     * @param array<string, mixed>          $params
     */
    public function countBy(array $filters, array $params = []): int
    {
        /** @var EventSmartContentCountFilters $filters */
        $filters = $this->enhanceWithDimensionAttributes($filters);

        $alias = 'event';
        $queryBuilder = $this->entityRepository->createQueryBuilder($alias);

        $filters = $this->mapFilters($filters);
        $this->dimensionContentQueryEnhancer->addFilters(
            $queryBuilder,
            $alias,
            $this->eventDimensionContentClassName,
            $filters,
            [],
        );
        $this->addInternalFilters($queryBuilder, $filters, $alias);

        $queryBuilder->select('COUNT(DISTINCT '.$alias.'.id)');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param EventSmartContentFilters $filters
     * @param array{
     *     title?: 'asc'|'desc',
     *     startDate?: 'asc'|'desc',
     *     endDate?: 'asc'|'desc',
     *     workflowPublished?: 'asc'|'desc',
     *     created?: 'asc'|'desc',
     *     changed?: 'asc'|'desc',
     * } $sortBys
     * @param array<string, mixed> $params
     *
     * @return array<array{id: string, title: string}>
     */
    public function findFlatBy(array $filters, array $sortBys, array $params = []): array
    {
        /** @var EventSmartContentFilters $filters */
        $filters = $this->enhanceWithDimensionAttributes($filters);

        $alias = 'event';
        $queryBuilder = $this->entityRepository->createQueryBuilder($alias);

        $filters = $this->mapFilters($filters);
        $this->dimensionContentQueryEnhancer->addFilters(
            $queryBuilder,
            $alias,
            $this->eventDimensionContentClassName,
            $filters,
            $sortBys,
        );
        $this->addInternalFilters($queryBuilder, $filters, $alias);

        $queryBuilder->select('DISTINCT '.$alias.'.id as id');
        $this->smartContentQueryEnhancer->addOrderBySelects($queryBuilder);
        $this->smartContentQueryEnhancer->addPagination($queryBuilder, $filters['offset'] ?? 0, $filters['limit']);

        /** @var array{id: int|string, title?: string}[] $queryResult */
        $queryResult = $queryBuilder->getQuery()->getArrayResult();

        /** @var array{id: string, title: string}[] $result */
        $result = \array_map(
            static fn (array $item) => [
                'id' => (string) $item['id'],
                'title' => (string) ($item['title'] ?? ''),
            ],
            $queryResult
        );

        return $result;
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    protected function enhanceWithDimensionAttributes(array $filters): array
    {
        $dimensionAttributes = [
            'stage' => $filters['stage'] ?? DimensionContentInterface::STAGE_LIVE,
        ];

        return \array_merge($dimensionAttributes, $filters);
    }

    /**
     * @param EventSmartContentFilters|EventSmartContentCountFilters $filters
     *
     * @return array{
     *         categoryIds?: int[],
     *         categoryOperator: 'AND'|'OR',
     *         websiteCategories: string[],
     *         websiteCategoryOperator: 'AND'|'OR',
     *         tagNames?: string[],
     *         tagOperator: 'AND'|'OR',
     *         websiteTags: string[],
     *         websiteTagOperator: 'AND'|'OR',
     *         templateKeys?: string[],
     *         typesOperator: 'OR',
     *         locale: string,
     *         dataSource: string|null,
     *         limit: int|null,
     *         offset?: int,
     *         includeSubFolders: bool,
     *         excludeDuplicates: bool,
     *         stage?: string,
     *     }
     */
    protected function mapFilters(array $filters): array
    {
        $mappedFilters = [
            'categoryIds' => $filters['categories'] ?? [],
            'categoryOperator' => $filters['categoryOperator'] ?? 'OR',
            'websiteCategories' => $filters['websiteCategories'] ?? [],
            'websiteCategoryOperator' => $filters['websiteCategoryOperator'] ?? 'OR',
            'tagNames' => $filters['tags'] ?? [],
            'tagOperator' => $filters['tagOperator'] ?? 'OR',
            'websiteTags' => $filters['websiteTags'] ?? [],
            'websiteTagOperator' => $filters['websiteTagOperator'] ?? 'OR',
            'templateKeys' => $filters['types'] ?? [],
            'typesOperator' => $filters['typesOperator'] ?? 'OR',
            'locale' => $filters['locale'],
            'dataSource' => $filters['dataSource'] ?? null,
            'limit' => $filters['limit'] ?? null,
            'includeSubFolders' => $filters['includeSubFolders'] ?? false,
            'excludeDuplicates' => $filters['excludeDuplicates'] ?? false,
        ];

        if (isset($filters['offset'])) {
            $mappedFilters['offset'] = $filters['offset'];
        }

        if (isset($filters['stage'])) {
            $mappedFilters['stage'] = $filters['stage'];
        }

        return $mappedFilters;
    }

    /**
     * @param array{
     *     websiteCategories: string[],
     *     websiteCategoryOperator: 'AND'|'OR',
     *     websiteTags: string[],
     *     websiteTagOperator: 'AND'|'OR',
     *     templateKeys: string[],
     *     typesOperator: 'OR',
     * } $filters
     */
    protected function addInternalFilters(QueryBuilder $queryBuilder, array $filters, string $alias): void
    {
        $this->addTypeFilters($queryBuilder, $filters['templateKeys'] ?? [], $alias);
    }

    /**
     * @param string[] $types
     */
    protected function addTypeFilters(QueryBuilder $queryBuilder, array $types, string $alias): void
    {
        if (empty($types)) {
            return;
        }

        $hasPending = \in_array('pending', $types, true);
        $hasExpired = \in_array('expired', $types, true);

        $configurableTypes = \array_intersect($types, \array_keys($this->eventTypes));

        if (!empty($configurableTypes)) {
            $queryBuilder->andWhere($alias.'.type IN (:eventTypes)')
                ->setParameter('eventTypes', $configurableTypes);
        }

        if ($hasPending && $hasExpired) {
            return;
        }

        $now = new \DateTime();
        $todayStart = (clone $now)->setTime(0, 0, 0);

        if ($hasPending) {
            $queryBuilder->andWhere(
                '('.$alias.'.endDate IS NOT NULL AND '.$alias.'.endDate >= :now) OR '.
                '('.$alias.'.endDate IS NULL AND '.$alias.'.startDate >= :todayStart)'
            );
            $queryBuilder->setParameter('now', $now);
            $queryBuilder->setParameter('todayStart', $todayStart);
        } elseif ($hasExpired) {
            $queryBuilder->andWhere(
                '('.$alias.'.endDate IS NOT NULL AND '.$alias.'.endDate < :now) OR '.
                '('.$alias.'.endDate IS NULL AND '.$alias.'.startDate < :todayStart)'
            );
            $queryBuilder->setParameter('now', $now);
            $queryBuilder->setParameter('todayStart', $todayStart);
        }
    }

    public function getType(): string
    {
        return Event::RESOURCE_KEY;
    }

    public function getResourceLoaderKey(): string
    {
        return Event::RESOURCE_KEY;
    }
}
