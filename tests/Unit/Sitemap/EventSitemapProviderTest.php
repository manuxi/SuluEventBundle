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

        $this->entityManager->method('getRepository')->with(Event::class)->willReturn($this->repository);

        $this->provider = new EventSitemapProvider(
            $this->entityManager,
            $this->webspaceManager,
            'dev'
        );
    }

    public function testBuild(): void
    {
        $portalInfo = $this->createMock(PortalInformation::class);
        $portalInfo->method('getLocale')->willReturn('en');

        $this->webspaceManager->method('findPortalInformationsByHostIncludingSubdomains')
            ->willReturn([$portalInfo]);

        // Mock QueryBuilder for findEvents and getAlternateRoutes
        $queryBuilder1 = $this->createMock(QueryBuilder::class);
        $query1 = $this->createMock(AbstractQuery::class);
        $queryBuilder1->method('leftJoin')->willReturnSelf();
        $queryBuilder1->method('setParameter')->willReturnSelf();
        $queryBuilder1->method('andWhere')->willReturnSelf();
        $queryBuilder1->method('select')->willReturnSelf();
        $queryBuilder1->method('orderBy')->willReturnSelf();
        $queryBuilder1->method('setFirstResult')->willReturnSelf();
        $queryBuilder1->method('setMaxResults')->willReturnSelf();
        $queryBuilder1->method('getQuery')->willReturn($query1);

        $queryBuilder2 = $this->createMock(QueryBuilder::class);
        $query2 = $this->createMock(AbstractQuery::class);
        $queryBuilder2->method('leftJoin')->willReturnSelf();
        $queryBuilder2->method('setParameter')->willReturnSelf();
        $queryBuilder2->method('andWhere')->willReturnSelf();
        $queryBuilder2->method('select')->willReturnSelf();
        $queryBuilder2->method('getQuery')->willReturn($query2);

        $this->repository->expects($this->exactly(2))->method('createQueryBuilder')
            ->willReturnOnConsecutiveCalls($queryBuilder1, $queryBuilder2);

        // Result for findEvents
        $eventsData = [
            [
                'id' => 1,
                'locale' => 'en',
                'slug' => '/event-1',
                'lastModified' => new \DateTime('2023-01-01'),
            ],
        ];

        // Result for getAlternateRoutes
        $alternateRoutesData = [
            [
                'id' => 1,
                'locale' => 'de',
                'slug' => '/event-1-de',
            ],
        ];

        // Result for findEvents (query1)
        $query1->method('getResult')->willReturn($eventsData);

        // Result for getAlternateRoutes (query2)
        $query2->method('getResult')->willReturn($alternateRoutesData);

        $result = $this->provider->build(1, 'http', 'localhost');

        $this->assertCount(1, $result);
        $sitemapUrl = $result[0];
        $this->assertEquals('http://localhost/event-1', $sitemapUrl->getLoc());
        $this->assertEquals('en', $sitemapUrl->getLocale());

        $alternateLinks = $sitemapUrl->getAlternateLinks();

        $this->assertCount(2, $alternateLinks);

        $this->assertArrayHasKey('en', $alternateLinks);
        $this->assertEquals('en', $alternateLinks['en']->getLocale());
        $this->assertEquals('http://localhost/event-1', $alternateLinks['en']->getHref());

        $this->assertArrayHasKey('de', $alternateLinks);
        $this->assertEquals('de', $alternateLinks['de']->getLocale());
        $this->assertEquals('http://localhost/event-1-de', $alternateLinks['de']->getHref());
    }

    public function testGetAlias(): void
    {
        $this->assertEquals('events', $this->provider->getAlias());
    }

    public function testGetMaxPage(): void
    {
        $portalInfo = $this->createMock(PortalInformation::class);
        $portalInfo->method('getLocale')->willReturn('en');

        $this->webspaceManager->method('findPortalInformationsByHostIncludingSubdomains')
            ->willReturn([$portalInfo]);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(AbstractQuery::class);

        $this->repository->method('createQueryBuilder')->willReturn($queryBuilder);

        $queryBuilder->method('leftJoin')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $query->method('getSingleScalarResult')->willReturn(15000); // 1.5 pages

        $maxPage = $this->provider->getMaxPage('http', 'localhost');

        // PAGE_SIZE is 10000. 15000 / 10000 = 1.5 => ceil => 2
        $this->assertEquals(2, $maxPage);
    }
}
