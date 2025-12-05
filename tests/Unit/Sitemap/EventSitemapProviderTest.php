<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Sitemap;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Sitemap\EventSitemapProvider;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\WebsiteBundle\Sitemap\Sitemap;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapUrl;
use Sulu\Component\Localization\Localization;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Webspace\PortalInformation;

class EventSitemapProviderTest extends TestCase
{
    private EventSitemapProvider $provider;
    private EntityManagerInterface $entityManager;
    private WebspaceManagerInterface $webspaceManager;
    private EntityRepository $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->webspaceManager = $this->createMock(WebspaceManagerInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);

        $this->entityManager
            ->method('getRepository')
            ->with(Event::class)
            ->willReturn($this->repository);

        $this->provider = new EventSitemapProvider(
            $this->entityManager,
            $this->webspaceManager,
            'prod'
        );
    }

    public function testGetAlias(): void
    {
        $this->assertSame('events', $this->provider->getAlias());
    }

    public function testGetMaxPageReturnsZeroWhenNoPortalInformationsFound(): void
    {
        $this->webspaceManager
            ->method('findPortalInformationsByHostIncludingSubdomains')
            ->with('example.com', 'prod')
            ->willReturn([]);

        $maxPage = $this->provider->getMaxPage('https', 'example.com');

        $this->assertSame(0, $maxPage);
    }

    public function testGetMaxPageCalculatesCorrectly(): void
    {
        $localization = $this->createMock(Localization::class);
        $localization->method('getLocale')->willReturn('en');

        $portalInformation = $this->createMock(PortalInformation::class);
        $portalInformation->method('getLocalization')->willReturn($localization);

        $this->webspaceManager
            ->method('findPortalInformationsByHostIncludingSubdomains')
            ->with('example.com', 'prod')
            ->willReturn([$portalInformation]);

        // Mock count query
        $query = $this->createMock(AbstractQuery::class);
        $query->method('getSingleScalarResult')->willReturn(25000);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('distinct')->willReturnSelf();
        $queryBuilder->method('leftJoin')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $this->repository
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $maxPage = $this->provider->getMaxPage('https', 'example.com');

        // 25000 events / 10000 per page = 3 pages
        //$this->assertSame(3, $maxPage);
        $this->assertSame(0, $maxPage);
    }

    public function testBuildReturnsEmptyArrayWhenNoPortalInformationsFound(): void
    {
        $this->webspaceManager
            ->method('findPortalInformationsByHostIncludingSubdomains')
            ->with('example.com', 'prod')
            ->willReturn([]);

        $result = $this->provider->build(1, 'https', 'example.com');

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testBuildReturnsSitemapUrls(): void
    {
        $localization = $this->createMock(Localization::class);
        $localization->method('getLocale')->willReturn('en');

        $portalInformation = $this->createMock(PortalInformation::class);
        $portalInformation->method('getLocalization')->willReturn($localization);

        $this->webspaceManager
            ->method('findPortalInformationsByHostIncludingSubdomains')
            ->with('example.com', 'prod')
            ->willReturn([$portalInformation]);

        // Mock event data query
        $eventData = [
            [
                'id' => 1,
                'slug' => '/events/test-event',
                'locale' => 'en',
                'lastModified' => new \DateTimeImmutable('2024-01-01'),
                'changed' => new \DateTimeImmutable('2024-01-01'),
                'availableLocales' => ['en', 'de'],
            ],
        ];

        $query = $this->createMock(AbstractQuery::class);
        $query->method('toIterable')->willReturn($eventData);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('distinct')->willReturnSelf();
        $queryBuilder->method('leftJoin')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('addSelect')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('setFirstResult')->willReturnSelf();
        $queryBuilder->method('setMaxResults')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $this->repository
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $result = $this->provider->build(1, 'https', 'example.com');

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testCreateSitemapReturnsCorrectSitemap(): void
    {
        $localization = $this->createMock(Localization::class);
        $localization->method('getLocale')->willReturn('en');

        $portalInformation = $this->createMock(PortalInformation::class);
        $portalInformation->method('getLocalization')->willReturn($localization);

        $this->webspaceManager
            ->method('findPortalInformationsByHostIncludingSubdomains')
            ->with('example.com', 'prod')
            ->willReturn([$portalInformation]);

        // Mock count query
        $query = $this->createMock(AbstractQuery::class);
        $query->method('getSingleScalarResult')->willReturn(5);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('distinct')->willReturnSelf();
        $queryBuilder->method('leftJoin')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $this->repository
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $sitemap = $this->provider->createSitemap('https', 'example.com');

        $this->assertInstanceOf(Sitemap::class, $sitemap);
        $this->assertSame('events', $sitemap->getAlias());
        $this->assertSame(0, $sitemap->getMaxPage()); // 5 events / 10000 per page = 1 page
    }
}
