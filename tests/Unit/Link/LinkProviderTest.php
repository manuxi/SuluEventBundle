<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Link;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Link\LinkProvider;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use PHPUnit\Framework\TestCase;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Sulu\Route\Domain\Model\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class LinkProviderTest extends TestCase
{
    private LinkProvider $provider;
    private ContentAggregatorInterface $contentAggregator;
    private EventRepository $eventRepository;
    private TranslatorInterface $translator;

    protected function setUp(): void
    {
        $this->contentAggregator = $this->createMock(ContentAggregatorInterface::class);
        $this->eventRepository = $this->createMock(EventRepository::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->provider = new LinkProvider(
            $this->contentAggregator,
            $this->eventRepository,
            $this->translator
        );
    }

    public function testGetConfigurationBuilder(): void
    {
        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturn('Translated');

        $builder = $this->provider->getConfigurationBuilder();
        $config = $builder->getLinkConfiguration();

        $reflection = new \ReflectionClass($config);
        $titleProp = $reflection->getProperty('title');
        $this->assertEquals('Translated', $titleProp->getValue($config));
    }

    public function testPreload(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getId')->willReturn('019bf796-423c-7e1f-969c-5c4ece5e9b73');

        $dimensionContent = $this->createMock(EventDimensionContent::class);
        $dimensionContent->method('getTitle')->willReturn('Test Event');
        $dimensionContent->method('getWorkflowPlace')->willReturn(WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);

        $route = $this->createMock(Route::class);
        $route->method('getSlug')->willReturn('/events/test');
        $dimensionContent->method('getRoute')->willReturn($route);

        $this->eventRepository->method('findByUuids')->willReturn([$event]);
        $this->contentAggregator->method('aggregate')->willReturn($dimensionContent);

        $links = iterator_to_array($this->provider->preload(['019bf796-423c-7e1f-969c-5c4ece5e9b73'], 'en', true));

        $this->assertCount(1, $links);
        $link = $links[0];

        $this->assertEquals('019bf796-423c-7e1f-969c-5c4ece5e9b73', $link->getId());
        $this->assertEquals('Test Event', $link->getTitle());
        $this->assertEquals('/events/test', $link->getUrl());
        $this->assertTrue($link->isPublished());
    }

    public function testPreloadEmpty(): void
    {
        $links = iterator_to_array($this->provider->preload([], 'en'));
        $this->assertEmpty($links);
    }
}