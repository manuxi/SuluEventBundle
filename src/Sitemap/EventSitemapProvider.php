<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Sitemap;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Bundle\WebsiteBundle\Sitemap\AbstractSitemapProvider;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapAlternateLink;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapUrl;
use Sulu\Component\Localization\Localization;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;

/**
 * @phpstan-type EventData array{
 *     lastModified: \DateTimeImmutable|null,
 *     changed: \DateTimeImmutable,
 *     locale: string,
 *     availableLocales: string[]|null,
 *     slug: string,
 *     id: int
 * }
 * @phpstan-type AlternateRoute array{
 *     locale: string,
 *     slug: string,
 *     id: int
 * }
 */
class EventSitemapProvider extends AbstractSitemapProvider
{
    /**
     * @var EntityRepository<Event>
     */
    protected EntityRepository $entityRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        private readonly WebspaceManagerInterface $webspaceManager,
        private readonly string $environment,
    ) {
        $repository = $entityManager->getRepository(Event::class);
        $this->entityRepository = $repository;
    }

    /**
     * @return SitemapUrl[]
     */
    public function build($page, $scheme, $host): array
    {
        $portalInformations = $this->webspaceManager->findPortalInformationsByHostIncludingSubdomains(
            $host,
            $this->environment
        );

        $result = [];

        foreach ($portalInformations as $portalInformation) {
            /** @var Localization|null $localization */
            $localization = $portalInformation->getLocalization();

            if (!$localization) {
                continue;
            }

            $locale = $localization->getLocale();

            $offset = ($page - 1) * static::PAGE_SIZE;
            $limit = static::PAGE_SIZE;

            $eventDataCollection = $this->getEventData($locale, $offset, $limit);
            $alternateRoutes = $this->getAlternateRoutes($locale);

            foreach ($eventDataCollection as $eventData) {
                $eventId = (string) $eventData['id'];
                $slug = $eventData['slug'];
                $availableLocales = $eventData['availableLocales'] ?? [];

                $sitemapUrl = new SitemapUrl(
                    $this->getUrl($slug, $scheme, $portalInformation),
                    $locale,
                    $locale,
                    $eventData['lastModified'] ?? $eventData['changed']
                );

                $result[] = $sitemapUrl;

                if (!\is_array($availableLocales)) {
                    continue;
                }

                foreach ($availableLocales as $availableLocale) {
                    if ($availableLocale === $locale) {
                        continue;
                    }

                    $alternateSlug = $alternateRoutes[$eventId][$availableLocale] ?? null;

                    if (!$alternateSlug) {
                        continue;
                    }

                    $alternatePortalInformation = $this->findPortalInformationForLocale(
                        $availableLocale,
                        $portalInformation,
                        $portalInformations
                    );

                    if (!$alternatePortalInformation) {
                        continue;
                    }

                    $sitemapUrl->addAlternateLink(
                        new SitemapAlternateLink(
                            $this->getUrl($alternateSlug, $scheme, $alternatePortalInformation),
                            $availableLocale
                        )
                    );
                }
            }
        }

        return $result;
    }

    public function getAlias(): string
    {
        return 'events';
    }

    public function getMaxPage($scheme, $host): ?float
    {
        $portalInformations = $this->webspaceManager->findPortalInformationsByHostIncludingSubdomains(
            $host,
            $this->environment
        );

        $maxPages = [];
        foreach ($portalInformations as $portalInformation) {
            $localization = $portalInformation->getLocalization();

            if (!$localization) {
                continue;
            }

            $locale = $localization->getLocale();
            $maxPages[] = \ceil($this->countEvents($locale) / static::PAGE_SIZE);
        }

        return \max($maxPages) ?: null;
    }

    /**
     * @return iterable<EventData>
     */
    private function getEventData(string $locale, int $offset, int $limit): iterable
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('event');

        $queryBuilder->distinct()->leftJoin('event.dimensionContents', 'dimensionContent', 'WITH', '
            dimensionContent.locale = :locale
            AND dimensionContent.stage = :stage
            AND dimensionContent.version = :version
            AND dimensionContent.seoHideInSitemap = :hide
            AND dimensionContent.workflowPlace = :published
        ')
            ->leftJoin('dimensionContent.route', 'route')
            ->leftJoin('event.dimensionContents', 'unLocalizedDimensionContent', 'WITH', '
                unLocalizedDimensionContent.locale IS NULL
                AND unLocalizedDimensionContent.stage = :stage
                AND unLocalizedDimensionContent.version = :version
            ')
            ->setParameter('locale', $locale)
            ->setParameter('stage', DimensionContentInterface::STAGE_LIVE)
            ->setParameter('version', DimensionContentInterface::CURRENT_VERSION)
            ->setParameter('hide', false)
            ->setParameter('published', WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);

        $queryBuilder->select('dimensionContent.lastModified');
        $queryBuilder->addSelect('dimensionContent.changed');
        $queryBuilder->addSelect('dimensionContent.locale');
        $queryBuilder->addSelect('unLocalizedDimensionContent.availableLocales');
        $queryBuilder->addSelect('route.slug');
        $queryBuilder->addSelect('event.id');

        $queryBuilder->orderBy('route.slug', 'ASC');
        $queryBuilder->setFirstResult($offset);
        $queryBuilder->setMaxResults($limit);

        /**
         * @var iterable<EventData>
         */
        return $queryBuilder->getQuery()->toIterable();
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

        /**
         * @var iterable<AlternateRoute>
         */
        $alternateRoutes = $queryBuilder->getQuery()->toIterable();

        $result = [];
        foreach ($alternateRoutes as $alternateRoute) {
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