<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Controller\Website;

use Manuxi\SuluEventBundle\Controller\Website\CalendarApiController;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Service\EventTimezoneResolver;
use Manuxi\SuluEventBundle\Service\EventTypeSelect;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Route\Domain\Model\Route;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

class CalendarApiControllerTest extends TestCase
{
    private EventRepository&MockObject $eventRepository;
    private ContentAggregatorInterface&MockObject $contentAggregator;
    private WebspaceManagerInterface&MockObject $webspaceManager;
    private CalendarApiController $controller;

    protected function setUp(): void
    {
        $this->eventRepository   = $this->createMock(EventRepository::class);
        $this->contentAggregator = $this->createMock(ContentAggregatorInterface::class);
        $this->webspaceManager   = $this->createMock(WebspaceManagerInterface::class);

        $eventTypeSelect = $this->createStub(EventTypeSelect::class);
        $eventTypeSelect->method('getColor')->willReturn('#0d6efd');
        $eventTypeSelect->method('getTypeName')->willReturn('Conference');

        $this->controller = new CalendarApiController($this->eventRepository, $eventTypeSelect, $this->contentAggregator, $this->webspaceManager, new EventTimezoneResolver('Europe/Berlin'));
        $this->controller->setContainer(new Container());
    }

    public function testFiltersOfTheQueryStringReachTheRepository(): void
    {
        $this->eventRepository->expects($this->once())
            ->method('findForCalendar')
            ->with($this->callback(function (array $filters): bool {
                $this->assertSame('de', $filters['locale']);
                $this->assertSame(['12', 'abc', '15'], $filters['categories']);
                $this->assertSame(['Symfony', '', 'Unreal Engine'], $filters['tags']);
                $this->assertSame('Messe & Halle', $filters['location']);
                $this->assertSame('2027-03-01 00:00:00', $filters['start']);
                $this->assertSame('2027-04-01 00:00:00', $filters['end']);

                return true;
            }))
            ->willReturn([]);

        $request = Request::create('/api/events/calendar/de', 'GET', [
            'categories' => ['12', 'abc', '15'],
            'tags'       => ['Symfony', '', ' Unreal Engine '],
            'location'   => 'Messe & Halle',
            'start'      => '2027-03-01',
            'end'        => '2027-04-01',
        ]);

        $response = $this->controller->calendarAction($request, 'de');

        $this->assertSame('[]', $response->getContent());
    }

    public function testNoFilterIsPassedAsEmptyLists(): void
    {
        $this->eventRepository->expects($this->once())
            ->method('findForCalendar')
            ->with($this->callback(function (array $filters): bool {
                $this->assertSame([], $filters['categories']);
                $this->assertSame([], $filters['tags']);
                $this->assertNull($filters['location']);

                return true;
            }))
            ->willReturn([]);

        $this->controller->calendarAction(Request::create('/api/events/calendar/de'), 'de');
    }

    public function testEventUrlIsAbsoluteInTheWebspaceOfTheEvent(): void
    {
        $this->webspaceManager->expects($this->once())
            ->method('findUrlByResourceLocator')
            ->with('/events/live', null, 'de', 'example')
            ->willReturn('https://example.com/de/events/live');

        $calendarEvent = $this->callWithOneEvent($this->createContent('/events/live', 'example'));

        $this->assertSame('https://example.com/de/events/live', $calendarEvent['url']);
    }

    public function testEventUrlFallsBackToThePathWithoutAWebsiteUrl(): void
    {
        $this->webspaceManager->method('findUrlByResourceLocator')->willReturn(null);

        $calendarEvent = $this->callWithOneEvent($this->createContent('/events/live', null));

        $this->assertSame('/events/live', $calendarEvent['url']);
    }

    public function testEventWithoutRouteHasNoUrl(): void
    {
        $this->webspaceManager->expects($this->never())->method('findUrlByResourceLocator');

        $calendarEvent = $this->callWithOneEvent($this->createContent(null, null));

        $this->assertSame('', $calendarEvent['url']);
    }

    public function testTimedEventsCarryTheOffsetOfTheirOwnTimezone(): void
    {
        $this->webspaceManager->method('findUrlByResourceLocator')->willReturn(null);
        $content = $this->createContent('/events/live', null);
        $content->setTimezone('America/Los_Angeles');

        $calendarEvent = $this->callWithOneEvent($content);

        $this->assertFalse($calendarEvent['allDay']);
        $this->assertSame('2027-07-01T10:00:00-07:00', $calendarEvent['start']);
        $this->assertSame('2027-07-01T12:00:00-07:00', $calendarEvent['end']);
    }

    public function testAllDayEventsAreSentAsDatesWithAnExclusiveEnd(): void
    {
        $this->webspaceManager->method('findUrlByResourceLocator')->willReturn(null);
        $content = $this->createContent('/events/live', null);
        $content->setAllDay(true);
        $content->setStartDate(new \DateTimeImmutable('2027-03-02 00:00:00'));
        $content->setEndDate(new \DateTimeImmutable('2027-03-04 23:59:59'));

        $calendarEvent = $this->callWithOneEvent($content);

        $this->assertTrue($calendarEvent['allDay']);
        $this->assertSame('2027-03-02', $calendarEvent['start']);
        $this->assertSame('2027-03-05', $calendarEvent['end']);
    }

    public function testSingleDayAllDayEventHasNoEnd(): void
    {
        $this->webspaceManager->method('findUrlByResourceLocator')->willReturn(null);
        $content = $this->createContent('/events/live', null);
        $content->setAllDay(true);
        $content->setStartDate(new \DateTimeImmutable('2027-03-02 00:00:00'));
        $content->setEndDate(new \DateTimeImmutable('2027-03-02 23:59:59'));

        $calendarEvent = $this->callWithOneEvent($content);

        $this->assertSame('2027-03-02', $calendarEvent['start']);
        $this->assertArrayNotHasKey('end', $calendarEvent);
    }

    public function testEventWithoutEndIsNoLongerGuessedAsAllDay(): void
    {
        $this->webspaceManager->method('findUrlByResourceLocator')->willReturn(null);
        $content = $this->createContent('/events/live', null);
        $content->setEndDate(null);

        $calendarEvent = $this->callWithOneEvent($content);

        $this->assertFalse($calendarEvent['allDay']);
        $this->assertSame('2027-07-01T10:00:00+02:00', $calendarEvent['start']);
    }

    public function testStatusAndAttendanceModeAreExposed(): void
    {
        $this->webspaceManager->method('findUrlByResourceLocator')->willReturn(null);
        $content = $this->createContent('/events/live', null);
        $content->setStatus('cancelled');
        $content->setAttendanceMode('online');

        $calendarEvent = $this->callWithOneEvent($content);

        $this->assertSame('cancelled', $calendarEvent['extendedProps']['status']);
        $this->assertSame('online', $calendarEvent['extendedProps']['attendanceMode']);
    }

    /**
     * @return array<string, mixed>
     */
    private function callWithOneEvent(EventDimensionContent $content): array
    {
        $this->eventRepository->method('findForCalendar')->willReturn([$content->getEvent()]);
        $this->contentAggregator->method('aggregate')->willReturn($content);

        $response = $this->controller->calendarAction(Request::create('/api/events/calendar/de'), 'de');

        $events = json_decode((string) $response->getContent(), true, flags: \JSON_THROW_ON_ERROR);
        $this->assertCount(1, $events);

        return $events[0];
    }

    private function createContent(?string $slug, ?string $webspace): EventDimensionContent
    {
        $uuid    = '019bf796-423c-7e1f-969c-5c4ece5e9b73';
        $content = new EventDimensionContent(new Event($uuid));
        $content->setLocale('de');
        $content->setTitle('Test Event');
        $content->setStartDate(new \DateTimeImmutable('2027-07-01 10:00:00'));
        $content->setEndDate(new \DateTimeImmutable('2027-07-01 12:00:00'));
        $content->setMainWebspace($webspace);
        if (null !== $slug) {
            $content->setRoute(new Route('events', $uuid, 'de', $slug));
        }

        return $content;
    }
}
