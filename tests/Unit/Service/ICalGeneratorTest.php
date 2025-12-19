<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Service;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Service\ICalGenerator;
use PHPUnit\Framework\TestCase;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Route\Domain\Model\Route;

class ICalGeneratorTest extends TestCase
{
    private ICalGenerator $generator;
    private EventRepository $eventRepository;
    private ContentAggregatorInterface $contentAggregator;

    protected function setUp(): void
    {
        $this->eventRepository = $this->createMock(EventRepository::class);
        $this->contentAggregator = $this->createMock(ContentAggregatorInterface::class);
        $this->generator = new ICalGenerator($this->eventRepository, $this->contentAggregator);
    }

    public function testGenerate(): void
    {
        $locale = 'en';
        $filters = ['category' => 1];

        $event = $this->createMock(Event::class);
        $event->method('getId')->willReturn(123);

        $dimensionContent = $this->createMock(EventDimensionContent::class);
        $dimensionContent->method('getEvent')->willReturn($event);
        $dimensionContent->method('getTitle')->willReturn('Test Event');
        $dimensionContent->method('getSummary')->willReturn('Test Summary');

        $dimensionContent->method('getStartDate')->willReturn(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $dimensionContent->method('getEndDate')->willReturn(new \DateTimeImmutable('2023-01-01 12:00:00'));

        $location = $this->createMock(Location::class);
        $location->method('getName')->willReturn('Test Location');
        $dimensionContent->method('getLocation')->willReturn($location);

        $route = $this->createMock(Route::class);
        $route->method('getSlug')->willReturn('/events/test-event');
        $dimensionContent->method('getRoute')->willReturn($route);

        $this->eventRepository->method('findForIcal')->with(['category' => 1, 'locale' => 'en'])->willReturn([$event]);

        $this->contentAggregator->method('aggregate')
            ->with($event, ['locale' => 'en', 'stage' => DimensionContentInterface::STAGE_LIVE])
            ->willReturn($dimensionContent);

        $ical = $this->generator->generate($filters, $locale);

        $this->assertStringContainsString('BEGIN:VCALENDAR', $ical);
        $this->assertStringContainsStringIgnoringCase('summary:Test Event', $ical); // Check simple string
        $this->assertStringContainsString('LOCATION:Test Location', $ical);
        $this->assertStringContainsString('URL:/events/test-event', $ical);
        $this->assertStringContainsString('DTSTART:20230101T100000Z', $ical);
        $this->assertStringContainsString('END:VCALENDAR', $ical);
    }

    public function testGenerateSingle(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getId')->willReturn(456);

        $dimensionContent = $this->createMock(EventDimensionContent::class);
        $dimensionContent->method('getEvent')->willReturn($event);
        $dimensionContent->method('getTitle')->willReturn('Single Event');
        $dimensionContent->method('getStartDate')->willReturn(new \DateTimeImmutable('2023-05-01 10:00:00'));
        $dimensionContent->method('getEndDate')->willReturn(null);
        $dimensionContent->method('getLocation')->willReturn(null);
        $dimensionContent->method('getRoute')->willReturn(null);

        $ical = $this->generator->generateSingle($event, $dimensionContent);

        $this->assertStringContainsString('BEGIN:VCALENDAR', $ical);
        $this->assertStringContainsString('SUMMARY:Single Event', $ical);
        $this->assertStringContainsString('UID:456@', $ical);
        $this->assertStringNotContainsString('LOCATION:', $ical);
    }
}
