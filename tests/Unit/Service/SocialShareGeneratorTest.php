<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\EventSocialSettings;
use Manuxi\SuluEventBundle\Service\SocialShareGenerator;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Route\Domain\Model\Route;

class SocialShareGeneratorTest extends TestCase
{
    private SocialShareGenerator $generator;
    private ContentAggregatorInterface $contentAggregator;
    private MediaManagerInterface $mediaManager;

    protected function setUp(): void
    {
        $this->contentAggregator = $this->createMock(ContentAggregatorInterface::class);
        $this->mediaManager = $this->createMock(MediaManagerInterface::class);
        $this->generator = new SocialShareGenerator($this->contentAggregator, $this->mediaManager);
    }

    public function testGenerateShareLinks(): void
    {
        $event = $this->createMock(Event::class);
        $localizedContent = $this->createMock(EventDimensionContent::class);
        $unlocalizedContent = $this->createMock(EventDimensionContent::class);

        $route = $this->createMock(Route::class);

        $localizedContent->method('getTitle')->willReturn('Test Event');

        $route->method('getSlug')->willReturn('/events/test');
        $localizedContent->method('getRoute')->willReturn($route);

        // Mock Aggregation
        $this->contentAggregator->method('aggregate')->willReturn($localizedContent);

        // Mock getDimensionContents to return unlocalized content
        $unlocalizedContent->method('getLocale')->willReturn(null);
        $unlocalizedContent->method('getStage')->willReturn(DimensionContentInterface::STAGE_LIVE);
        $unlocalizedContent->method('getVersion')->willReturn(DimensionContentInterface::CURRENT_VERSION);

        $event->method('getDimensionContents')->willReturn(new ArrayCollection([$unlocalizedContent]));

        // Mock Unlocalized Content with Social Settings
        $socialSettings = new EventSocialSettings($unlocalizedContent);
        $socialSettings->setTwitterShareText('Check this out!');
        $unlocalizedContent->method('getSocialSettings')->willReturn($socialSettings);

        $links = $this->generator->generateShareLinks($event, 'en');

        $this->assertArrayHasKey('twitter', $links);
        $this->assertStringContainsString('Check+this+out%21', $links['twitter']);
    }

    public function testGenerateOpenGraphTags(): void
    {
        $event = $this->createMock(Event::class);
        $localizedContent = $this->createMock(EventDimensionContent::class);
        $unlocalizedContent = $this->createMock(EventDimensionContent::class);

        $localizedContent->method('getTitle')->willReturn('OG Event');
        $localizedContent->method('getRoute')->willReturn(null);

        $this->contentAggregator->method('aggregate')->willReturn($localizedContent);

        $unlocalizedContent->method('getLocale')->willReturn(null);
        $unlocalizedContent->method('getStage')->willReturn(DimensionContentInterface::STAGE_LIVE);
        $unlocalizedContent->method('getVersion')->willReturn(DimensionContentInterface::CURRENT_VERSION);

        $event->method('getDimensionContents')->willReturn(new ArrayCollection([$unlocalizedContent]));

        $tags = $this->generator->generateOpenGraphTags($event, 'en');

        $this->assertArrayHasKey('og:title', $tags);
        $this->assertEquals('OG Event', $tags['og:title']);
    }
}
