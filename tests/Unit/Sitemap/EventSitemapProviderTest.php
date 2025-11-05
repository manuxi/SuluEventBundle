<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Sitemap;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Sitemap\EventSitemapProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\WebsiteBundle\Sitemap\Sitemap;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapUrl;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Webspace\PortalInformation;

class EventSitemapProviderTest extends TestCase
{
    private EventSitemapProvider $sitemapProvider;

    private EventRepository|MockObject $repository;
    private WebspaceManagerInterface|MockObject $webspaceManager;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EventRepository::class);
        $this->webspaceManager = $this->createMock(WebspaceManagerInterface::class);

        $this->sitemapProvider = new EventSitemapProvider(
            $this->repository,
            $this->webspaceManager
        );
    }

    public function testGetAliasReturns(): void
    {
        // Act
        $alias = $this->sitemapProvider->getAlias();

        // Assert
        $this->assertEquals('events', $alias);
    }

    public function testBuildReturnsEmptyArrayWhenNoEvent(): void
    {
        // Arrange
        $page = 1;
        $scheme = 'https';
        $host = 'example.com';

        $this->mockWebspaceManager($host, 'en');

        $this->repository
            ->expects($this->once())
            ->method('findAllForSitemap')
            ->with('en', EventSitemapProvider::PAGE_SIZE, 0)
            ->willReturn([]);

        // Act
        $result = $this->sitemapProvider->build($page, $scheme, $host);

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testBuildReturnsSitemapUrlsForEvent(): void
    {
        // Arrange
        $page = 1;
        $scheme = 'https';
        $host = 'example.com';

        $event1 = $this->createEventEntity(1, '/event/first-event', 'en', new \DateTime('2024-01-15'));
        $event2 = $this->createEventEntity(2, '/event/second-event', 'en', new \DateTime('2024-01-20'));

        $this->mockWebspaceManager($host, 'en');

        $this->repository
            ->expects($this->once())
            ->method('findAllForSitemap')
            ->with('en', EventSitemapProvider::PAGE_SIZE, 0)
            ->willReturn([$event1, $event2]);

        // Act
        $result = $this->sitemapProvider->build($page, $scheme, $host);

        // Assert
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(SitemapUrl::class, $result);

        /** @var SitemapUrl $firstUrl */
        $firstUrl = $result[0];
        $this->assertEquals('https://example.com/event/first-event', $firstUrl->getLoc());
        $this->assertEquals('en', $firstUrl->getLocale());
        $this->assertEquals(new \DateTime('2024-01-15'), $firstUrl->getLastmod());

        /** @var SitemapUrl $secondUrl */
        $secondUrl = $result[1];
        $this->assertEquals('https://example.com/event/second-event', $secondUrl->getLoc());
    }

    public function testBuildHandlesSecondPage(): void
    {
        // Arrange
        $page = 2;
        $scheme = 'https';
        $host = 'example.com';

        $event = $this->createEventEntity(1, '/event/event', 'en', new \DateTime());

        $this->mockWebspaceManager($host, 'en');

        $this->repository
            ->expects($this->once())
            ->method('findAllForSitemap')
            ->with('en', EventSitemapProvider::PAGE_SIZE, EventSitemapProvider::PAGE_SIZE)
            ->willReturn([$event]);

        // Act
        $result = $this->sitemapProvider->build($page, $scheme, $host);

        // Assert
        $this->assertCount(1, $result);
    }

    public function testBuildHandlesDifferentLocales(): void
    {
        // Arrange
        $page = 1;
        $scheme = 'https';
        $host = 'example.de';

        $event = $this->createEventEntity(1, '/nachrichten/artikel', 'de', new \DateTime());

        $this->mockWebspaceManager($host, 'de');

        $this->repository
            ->expects($this->once())
            ->method('findAllForSitemap')
            ->with('de', EventSitemapProvider::PAGE_SIZE, 0)
            ->willReturn([$event]);

        // Act
        $result = $this->sitemapProvider->build($page, $scheme, $host);

        // Assert
        $this->assertCount(1, $result);
        /** @var SitemapUrl $url */
        $url = $result[0];
        $this->assertEquals('de', $url->getLocale());
        $this->assertEquals('https://example.de/nachrichten/artikel', $url->getLoc());
    }

    public function testBuildHandlesHttpScheme(): void
    {
        // Arrange
        $page = 1;
        $scheme = 'http';
        $host = 'localhost';

        $event = $this->createEventEntity(1, '/event/test', 'en', new \DateTime());

        $this->mockWebspaceManager($host, 'en');

        $this->repository
            ->expects($this->once())
            ->method('findAllForSitemap')
            ->willReturn([$event]);

        // Act
        $result = $this->sitemapProvider->build($page, $scheme, $host);

        // Assert
        /** @var SitemapUrl $url */
        $url = $result[0];
        $this->assertStringStartsWith('http://localhost', $url->getLoc());
    }

    public function testCreateSitemapReturnsSitemapWithCorrectAlias(): void
    {
        // Arrange
        $scheme = 'https';
        $host = 'example.com';

        $this->mockWebspaceManager($host, 'en');

        $this->repository
            ->expects($this->once())
            ->method('countForSitemap')
            ->with('en')
            ->willReturn(10);

        // Act
        $sitemap = $this->sitemapProvider->createSitemap($scheme, $host);

        // Assert
        $this->assertInstanceOf(Sitemap::class, $sitemap);

        $this->assertEquals('events', $sitemap->getAlias());
    }

    public function testCreateSitemapCalculatesMaxPageCorrectly(): void
    {
        // Arrange
        $scheme = 'https';
        $host = 'example.com';

        $this->mockWebspaceManager($host, 'en');

        // PAGE_SIZE = 50000 (from SitemapProviderInterface - Google limit)
        // 25000 event items: ceil(25000/50000) = 1
        $this->repository
            ->expects($this->once())
            ->method('countForSitemap')
            ->with('en')
            ->willReturn(25000);

        // Act
        $sitemap = $this->sitemapProvider->createSitemap($scheme, $host);

        // Assert
        $this->assertEquals(1, $sitemap->getMaxPage());
    }

    public function testGetMaxPageReturnsCorrectValue(): void
    {
        // Arrange
        $scheme = 'https';
        $host = 'example.com';

        $this->mockWebspaceManager($host, 'en');

        $this->repository
            ->expects($this->once())
            ->method('countForSitemap')
            ->with('en')
            ->willReturn(150);

        // Act
        $maxPage = $this->sitemapProvider->getMaxPage($scheme, $host);

        // Assert
        // Bei PAGE_SIZE=10000: ceil(150/10000) = 1
        $this->assertEquals(1.0, $maxPage);
    }

    public function testGetMaxPageHandlesZeroEvent(): void
    {
        // Arrange
        $scheme = 'https';
        $host = 'example.com';

        $this->mockWebspaceManager($host, 'en');

        $this->repository
            ->expects($this->once())
            ->method('countForSitemap')
            ->with('en')
            ->willReturn(0);

        // Act
        $maxPage = $this->sitemapProvider->getMaxPage($scheme, $host);

        // Assert
        $this->assertEquals(0.0, $maxPage);
    }

    public function testLocaleIsCachedForSameHost(): void
    {
        // Arrange
        $host = 'example.com';

        $this->mockWebspaceManager($host, 'en');

        $this->repository
            ->expects($this->exactly(2))
            ->method('findAllForSitemap')
            ->willReturn([]);

        // Act - Zwei Aufrufe mit gleichem Host
        $this->sitemapProvider->build(1, 'https', $host);
        $this->sitemapProvider->build(2, 'https', $host);

        // Assert - WebspaceManager sollte nur einmal aufgerufen werden
        // Dies wird durch den Mock verifiziert (->once() im mockWebspaceManager)
        $this->assertTrue(true);
    }

    public function testBuildHandlesMultipleEventWithDifferentDates(): void
    {
        // Arrange
        $page = 1;
        $scheme = 'https';
        $host = 'example.com';

        $event1 = $this->createEventEntity(1, '/event/old', 'en', new \DateTime('2023-01-01'));
        $event2 = $this->createEventEntity(2, '/event/new', 'en', new \DateTime('2024-12-31'));
        $event3 = $this->createEventEntity(3, '/event/middle', 'en', new \DateTime('2024-06-15'));

        $this->mockWebspaceManager($host, 'en');

        $this->repository
            ->expects($this->once())
            ->method('findAllForSitemap')
            ->willReturn([$event1, $event2, $event3]);

        // Act
        $result = $this->sitemapProvider->build($page, $scheme, $host);

        // Assert
        $this->assertCount(3, $result);

        /* @var SitemapUrl[] $result */
        $this->assertEquals(new \DateTime('2023-01-01'), $result[0]->getLastmod());
        $this->assertEquals(new \DateTime('2024-12-31'), $result[1]->getLastmod());
        $this->assertEquals(new \DateTime('2024-06-15'), $result[2]->getLastmod());
    }

    public function testBuildHandlesPortalInformationWithDifferentHostFormat(): void
    {
        // Arrange
        $page = 1;
        $scheme = 'https';
        $host = 'www.example.com';

        $event = $this->createEventEntity(1, '/event/event', 'en', new \DateTime());

        // Mock PortalInformation wo der Host als getHost() zurückgegeben wird
        $portalInfo = $this->createMock(PortalInformation::class);
        $portalInfo->expects($this->once())
            ->method('getHost')
            ->willReturn($host);
        $portalInfo->expects($this->once())
            ->method('getLocale')
            ->willReturn('en');

        $this->webspaceManager
            ->expects($this->once())
            ->method('getPortalInformations')
            ->willReturn([
                'other-host.com' => $portalInfo,
            ]);

        $this->repository
            ->expects($this->once())
            ->method('findAllForSitemap')
            ->with('en', EventSitemapProvider::PAGE_SIZE, 0)
            ->willReturn([$event]);

        // Act
        $result = $this->sitemapProvider->build($page, $scheme, $host);

        // Assert
        $this->assertCount(1, $result);
    }

    /**
     * Helper: Erstellt ein Event-Mock-Objekt.
     */
    private function createEventEntity(int $id, string $routePath, string $locale, \DateTime $changed): Event|MockObject
    {
        $event = $this->createMock(Event::class);

        $event->expects($this->any())
            ->method('getId')
            ->willReturn($id);

        $event->expects($this->any())
            ->method('getRoutePath')
            ->willReturn($routePath);

        $event->expects($this->any())
            ->method('getLocale')
            ->willReturn($locale);

        $event->expects($this->any())
            ->method('getChanged')
            ->willReturn($changed);

        return $event;
    }

    /**
     * Helper: Mockt den WebspaceManager für einen Host.
     */
    private function mockWebspaceManager(string $host, string $locale): void
    {
        $portalInfo = $this->createMock(PortalInformation::class);
        $portalInfo->expects($this->any())
            ->method('getLocale')
            ->willReturn($locale);

        $this->webspaceManager
            ->expects($this->once())
            ->method('getPortalInformations')
            ->willReturn([
                $host => $portalInfo,
            ]);
    }
}
