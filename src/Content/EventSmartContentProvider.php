<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Manuxi\SuluEventBundle\Admin\EventAdmin;
use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Bundle\AdminBundle\SmartContent\Configuration\Builder;
use Sulu\Bundle\AdminBundle\SmartContent\Configuration\BuilderInterface;
use Sulu\Bundle\AdminBundle\SmartContent\Configuration\ProviderConfigurationInterface;
use Sulu\Bundle\AdminBundle\SmartContent\SmartContentProviderInterface;
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
     * @param array<string, array{name: string, color: string}> $eventTypes
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        protected TranslatorInterface $translator,
        private array $eventTypes = [],
    ) {
        $this->entityRepository = $entityManager->getRepository(Event::class);
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
            ->enableView(EventAdmin::EDIT_FORM_VIEW, ['id' => 'id']);
    }

    protected function getTypes(): array
    {
        $types = [
            ['type' => 'pending', 'title' => $this->translator->trans('sulu_event.filter.pending', [], 'admin')],
            ['type' => 'expired', 'title' => $this->translator->trans('sulu_event.filter.expired', [], 'admin')],
        ];

        // Add configurable event types from config
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
            ['column' => 'published', 'title' => 'sulu_event.published'],
        ];
    }

    /**
     * @param EventSmartContentCountFilters $filters
     * @param array<string, mixed>          $params
     */
    public function countBy(array $filters, array $params = []): int
    {
        $qb = $this->createBaseQueryBuilder($filters);
        $qb->select('COUNT(DISTINCT event.id)');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param EventSmartContentFilters $filters
     * @param array<string, string>    $sortBys
     * @param array<string, mixed>     $params
     *
     * @return array<array{id: string, title: string}>
     */
    public function findFlatBy(array $filters, array $sortBys, array $params = []): array
    {
        $qb = $this->createBaseQueryBuilder($filters);

        // Select required fields
        $qb->select('DISTINCT CAST(event.id AS string) as id')
            ->addSelect('translation.title');

        // Apply sorting
        $this->applySorting($qb, $sortBys);

        // Apply pagination
        if (isset($filters['limit']) && $filters['limit'] > 0) {
            $qb->setMaxResults($filters['limit']);
        }

        if (isset($filters['offset']) && $filters['offset'] > 0) {
            $qb->setFirstResult($filters['offset']);
        }

        /** @var array<array{id: string, title: string}> $result */
        $result = $qb->getQuery()->getArrayResult();

        return $result;
    }

    protected function createBaseQueryBuilder(array $filters): QueryBuilder
    {
        $locale = $filters['locale'];

        $qb = $this->entityRepository->createQueryBuilder('event')
            ->leftJoin('event.translations', 'translation')
            ->where('translation.locale = :locale')
            ->andWhere('translation.published = :published')
            ->setParameter('locale', $locale)
            ->setParameter('published', true);

        // Type filter (pending/expired)
        if (!empty($filters['types'])) {
            $this->applyTypeFilter($qb, $filters['types']);
        }

        // Category filter
        if (!empty($filters['categories'])) {
            $operator = $filters['categoryOperator'] ?? 'OR';
            if ('AND' === $operator) {
                foreach ($filters['categories'] as $i => $categoryId) {
                    $qb->innerJoin('translation.categories', 'category'.$i)
                        ->andWhere('category'.$i.'.id = :category'.$i)
                        ->setParameter('category'.$i, $categoryId);
                }
            } else {
                $qb->innerJoin('translation.categories', 'category')
                    ->andWhere('category.id IN (:categories)')
                    ->setParameter('categories', $filters['categories']);
            }
        }

        // Tag filter
        if (!empty($filters['tags'])) {
            $operator = $filters['tagOperator'] ?? 'OR';
            if ('AND' === $operator) {
                foreach ($filters['tags'] as $i => $tagName) {
                    $qb->innerJoin('translation.tags', 'tag'.$i)
                        ->andWhere('tag'.$i.'.name = :tag'.$i)
                        ->setParameter('tag'.$i, $tagName);
                }
            } else {
                $qb->innerJoin('translation.tags', 'tag')
                    ->andWhere('tag.name IN (:tags)')
                    ->setParameter('tags', $filters['tags']);
            }
        }

        // Website category filter (excerpt categories)
        if (!empty($filters['websiteCategories'])) {
            $operator = $filters['websiteCategoryOperator'] ?? 'OR';
            if ('AND' === $operator) {
                foreach ($filters['websiteCategories'] as $i => $categoryId) {
                    $qb->innerJoin('translation.categories', 'websiteCategory'.$i)
                        ->andWhere('websiteCategory'.$i.'.id = :websiteCategory'.$i)
                        ->setParameter('websiteCategory'.$i, $categoryId);
                }
            } else {
                $qb->innerJoin('translation.categories', 'websiteCategory')
                    ->andWhere('websiteCategory.id IN (:websiteCategories)')
                    ->setParameter('websiteCategories', $filters['websiteCategories']);
            }
        }

        // Website tag filter (excerpt tags)
        if (!empty($filters['websiteTags'])) {
            $operator = $filters['websiteTagOperator'] ?? 'OR';
            if ('AND' === $operator) {
                foreach ($filters['websiteTags'] as $i => $tagName) {
                    $qb->innerJoin('translation.tags', 'websiteTag'.$i)
                        ->andWhere('websiteTag'.$i.'.name = :websiteTag'.$i)
                        ->setParameter('websiteTag'.$i, $tagName);
                }
            } else {
                $qb->innerJoin('translation.tags', 'websiteTag')
                    ->andWhere('websiteTag.name IN (:websiteTags)')
                    ->setParameter('websiteTags', $filters['websiteTags']);
            }
        }

        return $qb;
    }

    /**
     * @param array<string, string> $sortBys
     */
    protected function applySorting(QueryBuilder $qb, array $sortBys): void
    {
        if (empty($sortBys)) {
            $qb->orderBy('event.startDate', 'ASC');

            return;
        }

        $sortMap = [
            'startDate' => 'event.startDate',
            'endDate' => 'event.endDate',
            'title' => 'translation.title',
            'published' => 'translation.publishedAt',
            'workflowPublished' => 'translation.publishedAt',
            'authored' => 'translation.authored',
            'created' => 'event.created',
            'changed' => 'event.changed',
        ];

        foreach ($sortBys as $sortBy => $direction) {
            $field = $sortMap[$sortBy] ?? 'event.startDate';
            $qb->addOrderBy($field, strtoupper($direction));
        }
    }

    protected function applyTypeFilter(QueryBuilder $qb, array $types): void
    {
        $hasPending = in_array('pending', $types, true);
        $hasExpired = in_array('expired', $types, true);

        // Collect configurable event type keys
        $configurableTypes = array_intersect($types, array_keys($this->eventTypes));

        // Filter by configurable event types (if any selected)
        if (!empty($configurableTypes)) {
            $qb->andWhere('event.type IN (:eventTypes)')
                ->setParameter('eventTypes', $configurableTypes);
        }

        // Temporal filters (pending/expired)
        if ($hasPending && $hasExpired) {
            return; // All events (no temporal filter)
        }

        $now = new \DateTime();
        $todayStart = (clone $now)->setTime(0, 0, 0);

        if ($hasPending) {
            $qb->andWhere(
                '(event.endDate IS NOT NULL AND event.endDate >= :now) OR '.
                '(event.endDate IS NULL AND event.startDate >= :todayStart)'
            );
            $qb->setParameter('now', $now);
            $qb->setParameter('todayStart', $todayStart);
        } elseif ($hasExpired) {
            $qb->andWhere(
                '(event.endDate IS NOT NULL AND event.endDate < :now) OR '.
                '(event.endDate IS NULL AND event.startDate < :todayStart)'
            );
            $qb->setParameter('now', $now);
            $qb->setParameter('todayStart', $todayStart);
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
