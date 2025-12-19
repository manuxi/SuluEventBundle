<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use Manuxi\SuluEventBundle\Twig\EventTwigExtension;
use PHPUnit\Framework\TestCase;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Application\ContentResolver\ContentResolverInterface;

class EventTwigExtensionTest extends TestCase
{
    private EventTwigExtension $extension;
    private EntityManagerInterface $entityManager;
    private ContentAggregatorInterface $contentAggregator;
    private ContentResolverInterface $contentResolver;
    private RequestAnalyzerInterface $requestAnalyzer;
    private EventRepository $eventRepository;
    private LocationRepository $locationRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->contentAggregator = $this->createMock(ContentAggregatorInterface::class);
        $this->contentResolver = $this->createMock(ContentResolverInterface::class);
        $this->requestAnalyzer = $this->createMock(RequestAnalyzerInterface::class);
        $this->eventRepository = $this->createMock(EventRepository::class);
        $this->locationRepository = $this->createMock(LocationRepository::class);

        $this->entityManager->method('getRepository')
            ->willReturnMap([
                [Event::class, $this->eventRepository],
                [Location::class, $this->locationRepository],
            ]);

        $this->extension = new EventTwigExtension(
            $this->entityManager,
            $this->contentAggregator,
            $this->contentResolver,
            $this->requestAnalyzer
        );
    }

    public function testResolveEvent(): void
    {
        $event = new Event();
        $this->eventRepository->method('findOneBy')->with(['id' => 1])->willReturn($event);

        $dimensionContent = $this->createMock(EventDimensionContent::class);
        $dimensionContent->method('getTitle')->willReturn('Test Title');
        $this->contentAggregator->method('aggregate')->willReturn($dimensionContent);

        $this->contentResolver->expects($this->once())->method('resolve')->willReturn(['title' => 'Test Title']);

        $result = $this->extension->resolveEvent(1, [], 'en');
        $this->assertEquals(['title' => 'Test Title'], $result);
    }

    public function testResolveLocation(): void
    {
        $location = new Location();
        $this->locationRepository->method('find')->with(10)->willReturn($location);

        $result = $this->extension->resolveLocation(10);
        $this->assertSame($location, $result);
    }

    public function testGetEvents(): void
    {
        $event = new Event();
        $this->eventRepository->method('findByFilters')->willReturn([$event]);

        $dimensionContent = $this->createMock(EventDimensionContent::class);
        $dimensionContent->method('getTitle')->willReturn('Found Event');
        $this->contentAggregator->method('aggregate')->willReturn($dimensionContent);

        $this->contentResolver->method('resolve')->willReturn(['title' => 'Found Event']);

        $result = $this->extension->getEvents([], [], 'en');
        $this->assertCount(1, $result);
        $this->assertEquals('Found Event', $result[0]['title']);
    }
}
