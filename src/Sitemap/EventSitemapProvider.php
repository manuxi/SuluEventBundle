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
            $locale = $eventData['locale'];
            $slug = $eventData['slug'];
            $lastModified = $eventData['lastModified'];

            $sitemapUrl = new SitemapUrl(
                $scheme.'://'.$host.$slug,
                $locale,
                $locale,
                $lastModified,
            );

            // Add alternate links for other locales
            if (isset($alternateRoutes[$eventId])) {
                foreach ($alternateRoutes[$eventId] as $alternateLocale => $alternateSlug) {
                    $sitemapUrl->addAlternateLink(
                        new SitemapAlternateLink(
                            $scheme.'://'.$host.$alternateSlug,
                            $alternateLocale,
                        )
                    );
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
        $portalInformation = $this->webspaceManager->findPortalInformationsByHostIncludingSubdomains(
            $host,
            $this->environment
        );

        if (0 === \count($portalInformation)) {
            return null;
        }

        return reset($portalInformation)->getLocale();
    }

    /**
     * @return array<array{id: int, locale: string, slug: string, lastModified: \DateTimeInterface}>
     */
    private function findEvents(string $locale, int $limit, int $offset): array
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('event');

        $queryBuilder->andWhere('1 = 1');

        // Join localized dimension content
        $queryBuilder->distinct()->leftJoin('event.dimensionContents', 'dimensionContent', 'WITH', '
            dimensionContent.locale = :locale
            AND dimensionContent.stage = :stage
            AND dimensionContent.version = :version
            AND dimensionContent.seoHideInSitemap = :hide
            AND dimensionContent.workflowPlace = :published
        ')
            ->leftJoin('dimensionContent.route', 'route')
            ->setParameter('locale', $locale)
            ->setParameter('stage', DimensionContentInterface::STAGE_LIVE)
            ->setParameter('version', DimensionContentInterface::CURRENT_VERSION)
            ->setParameter('hide', false)
            ->setParameter('published', WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);

        // Join unlocalized dimension content for lastModified
        $queryBuilder->leftJoin(
            'event.dimensionContents',
            'unlocalizedDimensionContent',
            'WITH',
            'unlocalizedDimensionContent.locale IS NULL 
             AND unlocalizedDimensionContent.stage = :stage 
             AND unlocalizedDimensionContent.version = :version'
        );

        $queryBuilder->select('dimensionContent.locale');
        $queryBuilder->addSelect('route.slug');
        $queryBuilder->addSelect('event.id');
        $queryBuilder->addSelect('dimensionContent.changed as lastModified');

        $queryBuilder->orderBy('route.slug', 'ASC');
        $queryBuilder->setFirstResult($offset);
        $queryBuilder->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function getAlternateRoutes(string $locale): array
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('event');

        $queryBuilder->andWhere('1 = 1');

        $queryBuilder->distinct()->leftJoin('event.dimensionContents', 'dimensionContent', 'WITH', '
            dimensionContent.locale != :locale
            AND dimensionContent.locale IS NOT NULL
            AND dimensionContent.stage = :stage
            AND dimensionContent.version = :version
            AND dimensionContent.seoHideInSitemap = :hide
            AND dimensionContent.workflowPlace = :published
        ')
            ->leftJoin('dimensionContent.route', 'route')
            ->setParameter('locale', $locale)
            ->setParameter('stage', DimensionContentInterface::STAGE_LIVE)
            ->setParameter('version', DimensionContentInterface::CURRENT_VERSION)
            ->setParameter('hide', false)
            ->setParameter('published', WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);

        $queryBuilder->select('dimensionContent.locale');
        $queryBuilder->addSelect('route.slug');
        $queryBuilder->addSelect('event.id');

        $result = [];
        foreach ($queryBuilder->getQuery()->getResult() as $alternateRoute) {
            $eventId = (string) $alternateRoute['id'];
            $locale = $alternateRoute['locale'];
            $slug = $alternateRoute['slug'];

            if (!isset($result[$eventId])) {
                $result[$eventId] = [];
            }

            $result[$eventId][$locale] = $slug;
        }

        return $result;
    }

    private function countEvents(string $locale): int
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('event');

        $queryBuilder->select('COUNT(DISTINCT event.id)');

        $queryBuilder->distinct()->leftJoin('event.dimensionContents', 'dimensionContent', 'WITH', '
            dimensionContent.locale = :locale
            AND dimensionContent.stage = :stage
            AND dimensionContent.version = :version
            AND dimensionContent.seoHideInSitemap = :hide
            AND dimensionContent.workflowPlace = :published
        ')
            ->setParameter('locale', $locale)
            ->setParameter('stage', DimensionContentInterface::STAGE_LIVE)
            ->setParameter('version', DimensionContentInterface::CURRENT_VERSION)
            ->setParameter('hide', false)
            ->setParameter('published', WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }
}