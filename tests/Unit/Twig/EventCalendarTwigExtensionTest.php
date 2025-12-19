<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Twig\EventCalendarTwigExtension;
use PHPUnit\Framework\TestCase;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class EventCalendarTwigExtensionTest extends TestCase
{
    private EventCalendarTwigExtension $extension;
    private EntityManagerInterface $entityManager;
    private ContentAggregatorInterface $contentAggregator;
    private ContentResolverInterface $contentResolver;
    private RequestAnalyzerInterface $requestAnalyzer;
    private EventRepository $eventRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->contentAggregator = $this->createMock(ContentAggregatorInterface::class);
        $this->contentResolver = $this->createMock(ContentResolverInterface::class);
        $this->requestAnalyzer = $this->createMock(RequestAnalyzerInterface::class);
        $this->eventRepository = $this->createMock(EventRepository::class);

        $this->entityManager->method('getRepository')->with(Event::class)->willReturn($this->eventRepository);

        $this->extension = new EventCalendarTwigExtension(
            $this->entityManager,
            $this->contentAggregator,
            $this->contentResolver,
            $this->requestAnalyzer
        );
    }

    public function testGetEventsForCalendar(): void
    {
        $event = $this->createMock(Event::class);

        // Mock Unlocalized Content for Date
        $unlocalizedContent = $this->createMock(EventDimensionContent::class);
        $unlocalizedContent->method('getLocale')->willReturn(null);
        $unlocalizedContent->method('getStage')->willReturn(DimensionContentInterface::STAGE_LIVE);
        $unlocalizedContent->method('getVersion')->willReturn(DimensionContentInterface::CURRENT_VERSION);
        $unlocalizedContent->method('getStartDate')->willReturn(new \DateTimeImmutable('2023-05-15'));

        $event->method('getDimensionContents')->willReturn(new ArrayCollection([$unlocalizedContent]));

        $this->eventRepository->method('findByDateRange')->willReturn([$event]);

        // Mock Aggregation for Content
        $localizedContent = $this->createMock(EventDimensionContent::class);
        $this->contentAggregator->method('aggregate')->willReturn($localizedContent);

        $this->contentResolver->method('resolve')->willReturn(['title' => 'Calendar Event']);

        $result = $this->extension->getEventsForCalendar('en');

        $this->assertArrayHasKey('2023-05-15', $result);
        $this->assertEquals('Calendar Event', $result['2023-05-15'][0]['title']);
    }
}
