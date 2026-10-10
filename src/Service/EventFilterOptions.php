<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\AttendanceMode;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The values a filter bar for events can offer: topics (categories), types, countries, ways to take part and "free of
 * charge". Only values with at least one upcoming, published event of the locale are listed, each with the number of
 * those events. The result is cached per locale and cleared when an event changes (see FilterOptionsCacheListener).
 */
class EventFilterOptions
{
    public const CACHE_TAG = 'sulu_event.filter_options';

    private const CACHE_KEY = 'sulu_event.filter_options.';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventTypeSelect $eventTypeSelect,
        private readonly TranslatorInterface $translator,
        private readonly ?string $categoryRoot = null,
        private readonly ?TagAwareCacheInterface $cache = null,
        private readonly int $cacheLifetime = 3600,
    ) {
    }

    /**
     * @return array{
     *     topics: list<array{id: int, key: string|null, name: string, parent: int|null, depth: int, count: int}>,
     *     types: list<array{key: string, name: string, color: string, count: int}>,
     *     countries: list<array{code: string, name: string, count: int}>,
     *     attendanceModes: list<array{key: string, name: string, count: int}>,
     *     free: array{count: int}|null,
     * }
     */
    public function getOptions(string $locale): array
    {
        if (null === $this->cache) {
            return $this->build($locale);
        }

        return $this->cache->get(
            self::CACHE_KEY . $locale,
            function (ItemInterface $item) use ($locale): array {
                $item->tag(self::CACHE_TAG);
                $item->expiresAfter($this->cacheLifetime);

                return $this->build($locale);
            }
        );
    }

    /**
     * Forgets the cached options of all locales.
     */
    public function clear(): void
    {
        $this->cache?->invalidateTags([self::CACHE_TAG]);
    }

    /**
     * @return array<string, mixed>
     */
    private function build(string $locale): array
    {
        $rows = $this->createUpcomingQuery($locale)
            ->select('dimensionContent.type AS eventType, dimensionContent.attendanceMode AS eventMode, dimensionContent.isFree AS freeFlag, location.countryCode AS countryCode')
            ->leftJoin('dimensionContent.location', 'location')
            ->getQuery()
            ->getArrayResult();

        $types     = [];
        $modes     = [];
        $countries = [];
        $freeCount = 0;
        foreach ($rows as $row) {
            $type         = \is_string($row['eventType'] ?? null) && '' !== $row['eventType'] ? $row['eventType'] : 'default';
            $types[$type] = ($types[$type] ?? 0) + 1;

            $mode         = \is_string($row['eventMode'] ?? null) ? $row['eventMode'] : AttendanceMode::DEFAULT->value;
            $modes[$mode] = ($modes[$mode] ?? 0) + 1;

            $country = \is_string($row['countryCode'] ?? null) ? mb_strtoupper(trim($row['countryCode'])) : '';
            if (1 === preg_match('/^[A-Z]{2}$/', $country)) {
                $countries[$country] = ($countries[$country] ?? 0) + 1;
            }

            if (true === ($row['freeFlag'] ?? null)) {
                ++$freeCount;
            }
        }

        return [
            'topics'          => $this->buildTopics($locale),
            'types'           => $this->buildTypes($types, $locale),
            'countries'       => $this->buildCountries($countries, $locale),
            'attendanceModes' => $this->buildAttendanceModes($modes, $locale),
            'free'            => $freeCount > 0 ? ['count' => $freeCount] : null,
        ];
    }

    private function createUpcomingQuery(string $locale): \Doctrine\ORM\QueryBuilder
    {
        $now = new \DateTimeImmutable();

        return $this->entityManager->createQueryBuilder()
            ->from(EventDimensionContent::class, 'dimensionContent')
            ->where('dimensionContent.locale = :locale')
            ->andWhere('dimensionContent.stage = :stage')
            ->andWhere('dimensionContent.version = :version')
            ->andWhere('dimensionContent.startDate IS NOT NULL')
            ->andWhere('(dimensionContent.endDate IS NOT NULL AND dimensionContent.endDate >= :now) OR (dimensionContent.endDate IS NULL AND dimensionContent.startDate >= :todayStart)')
            ->setParameter('locale', $locale)
            ->setParameter('stage', 'live')
            ->setParameter('version', 0)
            ->setParameter('now', $now)
            ->setParameter('todayStart', $now->setTime(0, 0, 0));
    }

    /**
     * @return list<array{id: int, key: string|null, name: string, parent: int|null, depth: int, count: int}>
     */
    private function buildTopics(string $locale): array
    {
        $counts = [];
        $rows   = $this->createUpcomingQuery($locale)
            ->select('category.id AS id, COUNT(DISTINCT dimensionContent.id) AS eventCount')
            ->innerJoin('dimensionContent.excerptCategories', 'category')
            ->groupBy('category.id')
            ->getQuery()
            ->getArrayResult();
        foreach ($rows as $row) {
            $counts[(int) $row['id']] = (int) $row['eventCount'];
        }

        if ([] === $counts) {
            return [];
        }

        $categoryClass = $this->entityManager->getClassMetadata(EventDimensionContent::class)->getAssociationTargetClass('excerptCategories');
        $queryBuilder  = $this->entityManager->createQueryBuilder()
            ->select('category')
            ->from($categoryClass, 'category')
            ->where('category.id IN (:ids)')
            ->setParameter('ids', array_keys($counts))
            ->orderBy('category.lft', 'ASC');

        $rootDepth = -1;
        if (null !== $this->categoryRoot && '' !== $this->categoryRoot) {
            $root = $this->entityManager->createQueryBuilder()
                ->select('root')
                ->from($categoryClass, 'root')
                ->where('root.key = :key')
                ->setParameter('key', $this->categoryRoot)
                ->getQuery()
                ->getOneOrNullResult();
            if (null === $root) {
                // a root that does not exist lists no topics instead of all of them
                return [];
            }

            $rootDepth = $root->getDepth();
            $queryBuilder->andWhere('category.lft > :rootLft AND category.rgt < :rootRgt')
                ->setParameter('rootLft', $root->getLft())
                ->setParameter('rootRgt', $root->getRgt());
        }

        $topics = [];
        foreach ($queryBuilder->getQuery()->getResult() as $category) {
            $translation = $category->findTranslationByLocale($locale) ?? $category->findTranslationByLocale($category->getDefaultLocale());
            $name        = null !== $translation ? $translation->getTranslation() : '';

            $topics[] = [
                'id'     => (int) $category->getId(),
                'key'    => $category->getKey(),
                'name'   => '' !== $name ? $name : (string) ($category->getKey() ?? $category->getId()),
                'parent' => $category->getParent()?->getId(),
                'depth'  => max(0, $category->getDepth() - $rootDepth - 1),
                'count'  => $counts[(int) $category->getId()],
            ];
        }

        return $topics;
    }

    /**
     * @param array<string, int> $counts
     *
     * @return list<array{key: string, name: string, color: string, count: int}>
     */
    private function buildTypes(array $counts, string $locale): array
    {
        $types = [];
        foreach ($this->eventTypeSelect->getTypes() as $key => $config) {
            if (isset($counts[$key])) {
                $types[] = [
                    'key'   => $key,
                    'name'  => $this->eventTypeSelect->getTypeName($key, $locale),
                    'color' => $this->eventTypeSelect->getColor($key),
                    'count' => $counts[$key],
                ];
            }
        }

        return $types;
    }

    /**
     * @param array<string, int> $counts
     *
     * @return list<array{code: string, name: string, count: int}>
     */
    private function buildCountries(array $counts, string $locale): array
    {
        $countries = [];
        foreach ($counts as $code => $count) {
            try {
                $name = Countries::getName($code, $locale);
            } catch (MissingResourceException) {
                $name = $code;
            }

            $countries[] = ['code' => $code, 'name' => $name, 'count' => $count];
        }

        usort($countries, static fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return $countries;
    }

    /**
     * @param array<string, int> $counts
     *
     * @return list<array{key: string, name: string, count: int}>
     */
    private function buildAttendanceModes(array $counts, string $locale): array
    {
        $modes = [];
        foreach (AttendanceMode::values() as $mode) {
            if (isset($counts[$mode])) {
                $modes[] = [
                    'key'   => $mode,
                    'name'  => $this->translator->trans('sulu_event.attendance_mode.' . $mode, [], 'admin', $locale),
                    'count' => $counts[$mode],
                ];
            }
        }

        return $modes;
    }
}
