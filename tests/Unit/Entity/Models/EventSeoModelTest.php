<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity\Models;

use Manuxi\SuluEventBundle\Entity\EventSeo;
use Manuxi\SuluEventBundle\Entity\Models\EventSeoModel;
use Manuxi\SuluEventBundle\Repository\EventSeoRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class EventSeoModelTest extends TestCase
{
    private EventSeoModel $eventSeoModel;

    private EventSeoRepository|MockObject $eventSeoRepository;

    protected function setUp(): void
    {
        $this->eventSeoRepository = $this->createMock(EventSeoRepository::class);

        $this->eventSeoModel = new EventSeoModel(
            $this->eventSeoRepository
        );
    }

    public function testUpdateEventSeoWithAllFields(): void
    {
        // Arrange
        $eventSeo = $this->createMock(EventSeo::class);
        $request = new Request([], [
            'ext' => [
                'seo' => [
                    'locale' => 'en',
                    'title' => 'SEO Title',
                    'description' => 'SEO Description',
                    'keywords' => 'keyword1, keyword2, keyword3',
                    'canonicalUrl' => 'https://example.com/canonical',
                    'noIndex' => true,
                    'noFollow' => true,
                    'hideInSitemap' => true,
                ],
            ],
        ]);

        $eventSeo->expects($this->once())
            ->method('setLocale')
            ->with('en');

        $eventSeo->expects($this->once())
            ->method('setTitle')
            ->with('SEO Title');

        $eventSeo->expects($this->once())
            ->method('setDescription')
            ->with('SEO Description');

        $eventSeo->expects($this->once())
            ->method('setKeywords')
            ->with('keyword1, keyword2, keyword3');

        $eventSeo->expects($this->once())
            ->method('setCanonicalUrl')
            ->with('https://example.com/canonical');

        $eventSeo->expects($this->once())
            ->method('setNoIndex')
            ->with(true);

        $eventSeo->expects($this->once())
            ->method('setNoFollow')
            ->with(true);

        $eventSeo->expects($this->once())
            ->method('setHideInSitemap')
            ->with(true);

        $this->eventSeoRepository
            ->expects($this->once())
            ->method('save')
            ->with($eventSeo)
            ->willReturn($eventSeo);

        // Act
        $result = $this->eventSeoModel->updateEventSeo($eventSeo, $request);

        // Assert
        $this->assertSame($eventSeo, $result);
    }

    public function testUpdateEventSeoWithLocaleOnly(): void
    {
        // Arrange
        $eventSeo = $this->createMock(EventSeo::class);
        $request = new Request([], [
            'ext' => [
                'seo' => [
                    'locale' => 'de',
                ],
            ],
        ]);

        $eventSeo->expects($this->once())
            ->method('setLocale')
            ->with('de');

        // Andere Setter sollten nicht aufgerufen werden
        $eventSeo->expects($this->never())->method('setTitle');
        $eventSeo->expects($this->never())->method('setDescription');
        $eventSeo->expects($this->never())->method('setKeywords');
        $eventSeo->expects($this->never())->method('setCanonicalUrl');
        $eventSeo->expects($this->never())->method('setNoIndex');
        $eventSeo->expects($this->never())->method('setNoFollow');
        $eventSeo->expects($this->never())->method('setHideInSitemap');

        $this->eventSeoRepository
            ->expects($this->once())
            ->method('save')
            ->with($eventSeo)
            ->willReturn($eventSeo);

        // Act
        $result = $this->eventSeoModel->updateEventSeo($eventSeo, $request);

        // Assert
        $this->assertSame($eventSeo, $result);
    }

    public function testUpdateEventSeoWithTitleAndDescription(): void
    {
        // Arrange
        $eventSeo = $this->createMock(EventSeo::class);
        $request = new Request([], [
            'ext' => [
                'seo' => [
                    'title' => 'Page Title',
                    'description' => 'Page meta description for search engines',
                ],
            ],
        ]);

        $eventSeo->expects($this->once())
            ->method('setTitle')
            ->with('Page Title');

        $eventSeo->expects($this->once())
            ->method('setDescription')
            ->with('Page meta description for search engines');

        $this->eventSeoRepository
            ->expects($this->once())
            ->method('save')
            ->with($eventSeo)
            ->willReturn($eventSeo);

        // Act
        $result = $this->eventSeoModel->updateEventSeo($eventSeo, $request);

        // Assert
        $this->assertSame($eventSeo, $result);
    }

    public function testUpdateEventSeoWithRobotSettings(): void
    {
        // Arrange
        $eventSeo = $this->createMock(EventSeo::class);
        $request = new Request([], [
            'ext' => [
                'seo' => [
                    'noIndex' => false,
                    'noFollow' => true,
                    'hideInSitemap' => false,
                ],
            ],
        ]);

        $eventSeo->expects($this->once())
            ->method('setNoIndex')
            ->with(false);

        $eventSeo->expects($this->once())
            ->method('setNoFollow')
            ->with(true);

        $eventSeo->expects($this->once())
            ->method('setHideInSitemap')
            ->with(false);

        $this->eventSeoRepository
            ->expects($this->once())
            ->method('save')
            ->with($eventSeo)
            ->willReturn($eventSeo);

        // Act
        $result = $this->eventSeoModel->updateEventSeo($eventSeo, $request);

        // Assert
        $this->assertSame($eventSeo, $result);
    }

    public function testUpdateEventSeoWithCanonicalUrl(): void
    {
        // Arrange
        $eventSeo = $this->createMock(EventSeo::class);
        $request = new Request([], [
            'ext' => [
                'seo' => [
                    'canonicalUrl' => 'https://www.example.com/article/best-practices',
                ],
            ],
        ]);

        $eventSeo->expects($this->once())
            ->method('setCanonicalUrl')
            ->with('https://www.example.com/article/best-practices');

        $this->eventSeoRepository
            ->expects($this->once())
            ->method('save')
            ->with($eventSeo)
            ->willReturn($eventSeo);

        // Act
        $result = $this->eventSeoModel->updateEventSeo($eventSeo, $request);

        // Assert
        $this->assertSame($eventSeo, $result);
    }

    public function testUpdateEventSeoWithKeywords(): void
    {
        // Arrange
        $eventSeo = $this->createMock(EventSeo::class);
        $request = new Request([], [
            'ext' => [
                'seo' => [
                    'keywords' => 'php, symfony, sulu, cms, event',
                ],
            ],
        ]);

        $eventSeo->expects($this->once())
            ->method('setKeywords')
            ->with('php, symfony, sulu, cms, event');

        $this->eventSeoRepository
            ->expects($this->once())
            ->method('save')
            ->with($eventSeo)
            ->willReturn($eventSeo);

        // Act
        $result = $this->eventSeoModel->updateEventSeo($eventSeo, $request);

        // Assert
        $this->assertSame($eventSeo, $result);
    }

    public function testUpdateEventSeoWithEmptyData(): void
    {
        // Arrange
        $eventSeo = $this->createMock(EventSeo::class);
        $request = new Request([], [
            'ext' => [
                'seo' => [],
            ],
        ]);

        $eventSeo->expects($this->never())->method('setLocale');
        $eventSeo->expects($this->never())->method('setTitle');
        $eventSeo->expects($this->never())->method('setDescription');
        $eventSeo->expects($this->never())->method('setKeywords');
        $eventSeo->expects($this->never())->method('setCanonicalUrl');
        $eventSeo->expects($this->never())->method('setNoIndex');
        $eventSeo->expects($this->never())->method('setNoFollow');
        $eventSeo->expects($this->never())->method('setHideInSitemap');

        $this->eventSeoRepository
            ->expects($this->never())
            ->method('save');

        // Act
        $result = $this->eventSeoModel->updateEventSeo($eventSeo, $request);

        // Assert
        $this->assertSame($eventSeo, $result);
    }

    public function testUpdateEventSeoMultipleFieldsCombination(): void
    {
        // Arrange
        $eventSeo = $this->createMock(EventSeo::class);
        $request = new Request([], [
            'ext' => [
                'seo' => [
                    'locale' => 'fr',
                    'title' => 'Titre SEO',
                    'keywords' => 'actualités, technologie',
                    'noIndex' => false,
                    'hideInSitemap' => false,
                ],
            ],
        ]);

        $eventSeo->expects($this->once())->method('setLocale')->with('fr');
        $eventSeo->expects($this->once())->method('setTitle')->with('Titre SEO');
        $eventSeo->expects($this->once())->method('setKeywords')->with('actualités, technologie');
        $eventSeo->expects($this->once())->method('setNoIndex')->with(false);
        $eventSeo->expects($this->once())->method('setHideInSitemap')->with(false);

        // Diese sollten nicht aufgerufen werden
        $eventSeo->expects($this->never())->method('setDescription');
        $eventSeo->expects($this->never())->method('setCanonicalUrl');
        $eventSeo->expects($this->never())->method('setNoFollow');

        $this->eventSeoRepository
            ->expects($this->once())
            ->method('save')
            ->with($eventSeo)
            ->willReturn($eventSeo);

        // Act
        $result = $this->eventSeoModel->updateEventSeo($eventSeo, $request);

        // Assert
        $this->assertSame($eventSeo, $result);
    }

    public function testUpdateEventSeoReturnsUpdatedEntity(): void
    {
        // Arrange
        $eventSeo = $this->createMock(EventSeo::class);

        $request = new Request([], [
            'ext' => [
                'seo' => [
                    'title' => 'Test',
                ],
            ],
        ]);

        $this->eventSeoRepository
            ->expects($this->once())
            ->method('save')
            ->with($eventSeo)
            ->willReturn($eventSeo);

        // Act
        $result = $this->eventSeoModel->updateEventSeo($eventSeo, $request);

        // Assert
        $this->assertSame($eventSeo, $result);
    }
}
