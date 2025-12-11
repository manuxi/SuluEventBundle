<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Sitemap;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Bundle\WebsiteBundle\Sitemap\Sitemap;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapAlternateLink;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapProviderInterface;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapUrl;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;

class EventSitemapProvider implements SitemapProviderInterface
{
    public const PAGE_SIZE = 10000;

    private EntityRepository $entityRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private WebspaceManagerInterface $webspaceManager,
        private string $environment,
    ) {
        $this->entityRepository = $this->entityManager->getRepository(Event::class);
    }

    public function build($page, $scheme, $host): array
    {
        $locale = $this->getLocaleFromHost($host);

        if (!$locale) {
            return [];
        }

        $offset = ($page - 1) * self::PAGE_SIZE;
        $events = $this->findEvents($locale, self::PAGE_SIZE, $offset);

        $alternateRoutes = $this->getAlternateRoutes($locale);

        $result = [];
        foreach ($events as $eventData) {
            $eventId = (string) $eventData['id'];
            $eventLocale = $eventData['locale'];
            $slug = $eventData['slug'];
            $lastModified = $eventData['lastModified'];

            if (empty($slug)) {
                continue;
            }

            $sitemapUrl = new SitemapUrl(
                $scheme . '://' . $host . $slug,
                $eventLocale,
                $eventLocale,
                $lastModified,
            );

            // Add alternate links for other locales
            if (isset($alternateRoutes[$eventId])) {
                foreach ($alternateRoutes[$eventId] as $alternateLocale => $alternateSlug) {
                    if ($alternateLocale !== $eventLocale && !empty($alternateSlug)) {
                        $sitemapUrl->addAlternateLink(
                            new SitemapAlternateLink(
                                $scheme . '://' . $host . $alternateSlug,
                                $alternateLocale,
                            )
                        );
                    }
                }
            }

            $result[] = $sitemapUrl;
        }

        return $result;
    }

    public function createSitemap($scheme, $host): Sitemap
    {
        return new Sitemap(
            $this->getAlias(),
            $this->getMaxPage($scheme, $host)
        );
    }

    public function getAlias(): string
    {
        return 'events';
    }

    public function getMaxPage($scheme, $host): int
    {
        $locale = $this->getLocaleFromHost($host);

        if (!$locale) {
            return 0;
        }

        $count = $this->countEvents($locale);

        return (int) ceil($count / self::PAGE_SIZE);
    }

    private function getLocaleFromHost(string $host): ?string
    {
        $portalInformations = $this->webspaceManager->findPortalInformationsByHostIncludingSubdomains(
            $host,
            $this->environment
        );

        if (0 === \count($portalInformations)) {
            return null;
        }

        return \reset($portalInformations)->getLocale();
    }

    /**
     * Find published events for sitemap.
     *
     * @return array<array{id: int, locale: string, slug: string, lastModified: \DateTimeInterface|null}>
     */
    private function findEvents(string $locale, int $limit, int $offset): array
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('event');

        // Join localized dimension content (all fields are here in Sulu 3 Standard)
        $queryBuilder->leftJoin(
            'event.dimensionContents',
            'dimensionContent',
            'WITH',
            'dimensionContent.locale = :locale
             AND dimensionContent.stage = :stage
             AND dimensionContent.version = :version
             AND (dimensionContent.seoHideInSitemap = :hide OR dimensionContent.seoHideInSitemap IS NULL)
             AND dimensionContent.workflowPlace = :published'
        );

        // Join route for slug
        $queryBuilder->leftJoin('dimensionContent.route', 'route');

        $queryBuilder->setParameter('locale', $locale);
        $queryBuilder->setParameter('stage', DimensionContentInterface::STAGE_LIVE);
        $queryBuilder->setParameter('version', DimensionContentInterface::CURRENT_VERSION);
        $queryBuilder->setParameter('hide', false);
        $queryBuilder->setParameter('published', WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);

        // Only get events that have dimension content (INNER JOIN behavior)
        $queryBuilder->andWhere('dimensionContent.id IS NOT NULL');

        // Select fields
        $queryBuilder->select([
            'event.id AS id',
            'dimensionContent.locale AS locale',
            'route.slug AS slug',
            'dimensionContent.changed AS lastModified',
        ]);

        $queryBuilder->orderBy('route.slug', 'ASC');
        $queryBuilder->setFirstResult($offset);
        $queryBuilder->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Get alternate routes for all events in other locales.
     *
     * @return array<string, array<string, string>>
     */
    private function getAlternateRoutes(string $currentLocale): array
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('event');

        // Get routes for ALL locales (not just the current one)
        $queryBuilder->leftJoin(
            'event.dimensionContents',
            'dimensionContent',
            'WITH',
            'dimensionContent.locale IS NOT NULL
             AND dimensionContent.stage = :stage
             AND dimensionContent.version = :version
             AND (dimensionContent.seoHideInSitemap = :hide OR dimensionContent.seoHideInSitemap IS NULL)
             AND dimensionContent.workflowPlace = :published'
        );

        $queryBuilder->leftJoin('dimensionContent.route', 'route');

        $queryBuilder->setParameter('stage', DimensionContentInterface::STAGE_LIVE);
        $queryBuilder->setParameter('version', DimensionContentInterface::CURRENT_VERSION);
        $queryBuilder->setParameter('hide', false);
        $queryBuilder->setParameter('published', WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);

        // Only events with routes
        $queryBuilder->andWhere('route.slug IS NOT NULL');

        $queryBuilder->select([
            'event.id AS id',
            'dimensionContent.locale AS locale',
            'route.slug AS slug',
        ]);

        $result = [];
        foreach ($queryBuilder->getQuery()->getResult() as $row) {
            $eventId = (string) $row['id'];
            $rowLocale = $row['locale'];
            $slug = $row['slug'];

            if (!isset($result[$eventId])) {
                $result[$eventId] = [];
            }

            $result[$eventId][$rowLocale] = $slug;
        }

        return $result;
    }

    private function countEvents(string $locale): int
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('event');

        $queryBuilder->select('COUNT(DISTINCT event.id)');

        $queryBuilder->leftJoin(
            'event.dimensionContents',
            'dimensionContent',
            'WITH',
            'dimensionContent.locale = :locale
             AND dimensionContent.stage = :stage
             AND dimensionContent.version = :version
             AND (dimensionContent.seoHideInSitemap = :hide OR dimensionContent.seoHideInSitemap IS NULL)
             AND dimensionContent.workflowPlace = :published'
        );

        $queryBuilder->setParameter('locale', $locale);
        $queryBuilder->setParameter('stage', DimensionContentInterface::STAGE_LIVE);
        $queryBuilder->setParameter('version', DimensionContentInterface::CURRENT_VERSION);
        $queryBuilder->setParameter('hide', false);
        $queryBuilder->setParameter('published', WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);

        $queryBuilder->andWhere('dimensionContent.id IS NOT NULL');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }
}