<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Manuxi\SuluEventBundle\Admin\EventAdmin;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
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
 *     includeSubFolders: bool,
 *     excludeDuplicates: bool,
 *     offset?: int,
 *     stage?: string,
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
 *     stage?: string,
 * }
 */
class EventSmartContentProvider implements SmartContentProviderInterface
{
    /**
     * @var class-string<EventDimensionContent>
     */
    private string $eventDimensionContentClassName;

    private ?EventRepository $eventRepository = null;

    /**
     * @param array<string, array{name: string, color: string}> $eventTypes
     */
    public function __construct(
        private DimensionContentQueryEnhancer $dimensionContentQueryEnhancer,
        private SmartContentQueryEnhancer $smartContentQueryEnhancer,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
        private array $eventTypes = [],
    ) {
        $entityDimensionContentRepository = $entityManager->getRepository(EventDimensionContent::class);
        $this->eventDimensionContentClassName = $entityDimensionContentRepository->getClassName();
    }

    private function getEventRepository(): EventRepository
    {
        if (null === $this->eventRepository) {
            $repository = $this->entityManager->getRepository(Event::class);

            if (!$repository instanceof EventRepository) {
                throw new \RuntimeException(sprintf('Expected EventRepository, got %s', get_class($repository)));
            }

            $this->eventRepository = $repository;
        }

        return $this->eventRepository;
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
            ->enableTypes($this->getTypes())
            ->enableView(EventAdmin::EDIT_TABS_VIEW, ['id' => 'id']);
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
            ['column' => 'title', 'title' => $this->translator->trans('sulu_event.title', [], 'admin')],
            ['column' => 'startDate', 'title' => $this->translator->trans('sulu_event.start_date', [], 'admin')],
            ['column' => 'endDate', 'title' => $this->translator->trans('sulu_event.end_date', [], 'admin')],
            ['column' => 'workflowPublished', 'title' => $this->translator->trans('sulu_event.published', [], 'admin')],
            ['column' => 'created', 'title' => $this->translator->trans('sulu_event.created_date', [], 'admin')],
            ['column' => 'changed', 'title' => $this->translator->trans('sulu_event.changed_date', [], 'admin')],
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
        $queryBuilder = $this->getEventRepository()->createQueryBuilder($alias);

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
        $queryBuilder = $this->getEventRepository()->createQueryBuilder($alias);

        $filters = $this->mapFilters($filters);
        $this->dimensionContentQueryEnhancer->addFilters(
            $queryBuilder,
            $alias,
            $this->eventDimensionContentClassName,
            $filters,
            $sortBys,
        );
        $dimensionContentAlias = $this->addInternalFilters($queryBuilder, $filters, $alias);

        $queryBuilder->select('DISTINCT '.$alias.'.id as id');
        $queryBuilder->addSelect($dimensionContentAlias.'.title');
        $queryBuilder->addSelect($dimensionContentAlias . '.workflowPlace');
        $queryBuilder->addSelect($dimensionContentAlias . '.workflowPublished');
        $queryBuilder->addSelect($dimensionContentAlias.'.type');
        $queryBuilder->addSelect($dimensionContentAlias.'.startDate');
        $queryBuilder->addSelect($dimensionContentAlias.'.endDate');

        $this->smartContentQueryEnhancer->addOrderBySelects($queryBuilder);
        $limit = isset($filters['limit']) ? (int) $filters['limit'] : null;
        $offset = isset($filters['offset']) ? (int) $filters['offset'] : 0;
        $this->smartContentQueryEnhancer->addPagination($queryBuilder, $offset, $limit);

        /** @var array{id: int|string, title?: string}[] $queryResult */
        $queryResult = $queryBuilder->getQuery()->getArrayResult();

        /** @var array{id: string, title: string, type: string}[] $result */
        $result = \array_map(
            function (array $item) {

                $dateFormat = $this->translator->trans('sulu_event.date_format', [], 'admin');
                $type = $item['type'] ?? 'default';
                $translationKey = $this->eventTypes[$type]['name'] ?? 'sulu_event.type.default';

                $startDate = $item['startDate'] ?? null;
                $endDate = $item['endDate'] ?? null;
                $dateString = '';

                if ($startDate instanceof \DateTimeInterface) {
                    $startStr = $startDate->format($dateFormat);
                    $dateString = $startStr;

                    if ($endDate instanceof \DateTimeInterface) {
                        $endStr = $endDate->format($dateFormat);
                        if ($startStr !== $endStr) {
                            $dateString .= ' - '.$endStr;
                        }
                    }
                }

                return [
                    'id' => (string) $item['id'],
                    'title' => (string) ($item['title'] ?? ''),
                    'date' => $dateString,
                    'publishedState' => 'published' === ($item['workflowPlace'] ?? ''),
                    'published' => $item['workflowPublished'] ?? null,
                    //'type' => $this->translator->trans($translationKey, [], 'admin'),
                ];
            },
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

        /*return \array_merge($filters, [
            'stage' => DimensionContentInterface::STAGE_LIVE,
        ]);*/
    }

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
            'templateKeys' => [],
            'customTypes' => $filters['types'] ?? [],
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

    protected function addInternalFilters(QueryBuilder $queryBuilder, array $filters, string $alias): string
    {
        $dimensionContentAlias = null;
        $joins = $queryBuilder->getDQLPart('join');

        if (isset($joins[$alias])) {
            foreach ($joins[$alias] as $join) {
                if ($join->getJoin() === $alias.'.dimensionContents') {
                    $dimensionContentAlias = $join->getAlias();
                    break;
                }
            }
        }

        if (!$dimensionContentAlias) {
            $dimensionContentAlias = 'dimensionContent';
            $stage = $filters['stage'] ?? DimensionContentInterface::STAGE_LIVE;
            $locale = $filters['locale'];

            $queryBuilder->innerJoin(
                $alias.'.dimensionContents',
                $dimensionContentAlias,
                'WITH',
                $dimensionContentAlias.'.locale = :locale AND '.$dimensionContentAlias.'.stage = :stage'
            );
            $queryBuilder->setParameter('locale', $locale);
            $queryBuilder->setParameter('stage', $stage);
        }

        $this->addTypeFilters($queryBuilder, $filters['customTypes'] ?? [], $dimensionContentAlias);

        return $dimensionContentAlias;
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
