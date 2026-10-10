<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Service;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Service\EventTimezoneResolver;
use Manuxi\SuluEventBundle\Service\EventUrlResolver;
use Manuxi\SuluEventBundle\Service\ICalGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Reader;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\CategoryBundle\Entity\CategoryTranslationInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Route\Domain\Model\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ICalGeneratorTest extends TestCase
{
    private const UUID = '019bf796-423c-7e1f-969c-5c4ece5e9b73';

    private EventRepository&MockObject $eventRepository;
    private ContentAggregatorInterface&MockObject $contentAggregator;
    private WebspaceManagerInterface&MockObject $webspaceManager;
    private RequestStack $requestStack;
    private ICalGenerator $generator;

    protected function setUp(): void
    {
        $this->eventRepository   = $this->createMock(EventRepository::class);
        $this->contentAggregator = $this->createMock(ContentAggregatorInterface::class);
        $this->webspaceManager   = $this->createMock(WebspaceManagerInterface::class);
        $this->requestStack      = new RequestStack();

        $this->webspaceManager->method('findUrlByResourceLocator')->willReturnCallback(
            static fn (?string $resourceLocator, ?string $environment, string $locale) => 'https://example.com/' . $locale . $resourceLocator
        );

        $this->generator = $this->createGenerator('Europe/Berlin');
    }

    public function testGenerateBuildsAValidCalendar(): void
    {
        $content = $this->createContent('2027-07-01 10:00:00', '2027-07-01 12:00:00');
        $content->setSummary('Test Summary');
        $location = new Location();
        $location->setName('Test Location');
        $content->setLocation($location);

        $ical = $this->generateFeed([$content]);

        $calendar = $this->parse($ical);
        $this->assertSame([], $calendar->validate(), $ical);
        $this->assertSame('2.0', (string) $calendar->VERSION);
        $this->assertSame('-//Sulu Event Bundle//EN', (string) $calendar->PRODID);
        $this->assertSame('PUBLISH', (string) $calendar->METHOD);

        $vevent = $this->singleEvent($calendar);
        $this->assertSame('Test Event', (string) $vevent->SUMMARY);
        $this->assertSame('Test Summary', (string) $vevent->DESCRIPTION);
        $this->assertSame('Test Location', (string) $vevent->LOCATION);
        $this->assertStringContainsString("\r\nSUMMARY:Test Event\r\n", $ical);
    }

    public function testGeneratePassesTheFiltersAndTheLocaleToTheRepository(): void
    {
        $this->eventRepository->expects($this->once())
            ->method('findForIcal')
            ->with(['categories' => ['12'], 'tags' => ['llm'], 'locale' => 'de'])
            ->willReturn([]);

        $ical = $this->generator->generate(['categories' => ['12'], 'tags' => ['llm']], 'de');

        $this->assertCount(0, $this->parse($ical)->select('VEVENT'));
    }

    /**
     * @dataProvider provideWallClockTimes
     */
    public function testStoredWallClockTimeIsConvertedToUtcInTheZoneOfTheApplication(
        string $zone,
        string $storedStart,
        string $storedEnd,
        string $expectedStart,
        string $expectedEnd,
    ): void {
        $content = $this->createContent($storedStart, $storedEnd);

        $vevent = $this->singleEvent($this->parse($this->generateFeed([$content], $zone)));

        $this->assertSame($expectedStart, (string) $vevent->DTSTART);
        $this->assertSame($expectedEnd, (string) $vevent->DTEND);
        $this->assertNull($vevent->DTSTART['TZID']);
    }

    /**
     * @return iterable<string, array{string, string, string, string, string}>
     */
    public static function provideWallClockTimes(): iterable
    {
        yield 'Berlin in winter (UTC+1)' => ['Europe/Berlin', '2027-01-15 10:00:00', '2027-01-15 12:30:00', '20270115T090000Z', '20270115T113000Z'];
        yield 'Berlin in summer (UTC+2)' => ['Europe/Berlin', '2027-07-01 10:00:00', '2027-07-01 12:30:00', '20270701T080000Z', '20270701T103000Z'];
        yield 'Berlin around the switch to summer time' => ['Europe/Berlin', '2027-03-28 01:00:00', '2027-03-28 04:00:00', '20270328T000000Z', '20270328T020000Z'];
        yield 'New York in winter (UTC-5)' => ['America/New_York', '2027-01-15 10:00:00', '2027-01-15 12:00:00', '20270115T150000Z', '20270115T170000Z'];
        yield 'New York in summer (UTC-4)' => ['America/New_York', '2027-07-01 10:00:00', '2027-07-01 12:00:00', '20270701T140000Z', '20270701T160000Z'];
        yield 'UTC stays as it is' => ['UTC', '2027-07-01 10:00:00', '2027-07-01 12:00:00', '20270701T100000Z', '20270701T120000Z'];
    }

    public function testTheZoneOfTheStoredObjectDoesNotMatterOnlyItsWallClockTime(): void
    {
        // The database holds "10:00" without a zone, whatever zone the hydrated object happens to carry.
        $content = $this->createContent('2027-07-01 10:00:00', '2027-07-01 12:00:00', new \DateTimeZone('UTC'));

        $vevent = $this->singleEvent($this->parse($this->generateFeed([$content])));

        $this->assertSame('20270701T080000Z', (string) $vevent->DTSTART);
        $this->assertSame('20270701T100000Z', (string) $vevent->DTEND);
    }

    public function testResolveTimezoneUsesTheConfiguredZoneAndOtherwiseThePhpDefault(): void
    {
        $content = $this->createContent('2027-07-01 10:00:00', null);

        $this->assertSame('Europe/Berlin', $this->generator->resolveTimezone($content)->getName());
        $this->assertSame('America/New_York', $this->createGenerator('America/New_York')->resolveTimezone($content)->getName());
        $this->assertSame(date_default_timezone_get(), $this->createGenerator(null)->resolveTimezone($content)->getName());
    }

    public function testEventWithoutEndHasNoDtend(): void
    {
        $content = $this->createContent('2027-07-01 18:30:00', null);

        $vevent = $this->singleEvent($this->parse($this->generateFeed([$content])));

        $this->assertSame('20270701T163000Z', (string) $vevent->DTSTART);
        $this->assertNull($vevent->DTEND);
    }

    public function testTimezoneOfTheEventWinsOverTheOneOfTheLocationAndTheApplication(): void
    {
        $location = new Location();
        $location->setName('Convention Center');
        $location->setTimezone('Asia/Tokyo');

        $ownZone = $this->createContent('2027-01-15 10:00:00', '2027-01-15 12:00:00');
        $ownZone->setLocation($location);
        $ownZone->setTimezone('America/Los_Angeles');
        $this->assertSame('20270115T180000Z', (string) $this->singleEvent($this->parse($this->generateFeed([$ownZone])))->DTSTART);

        $locationZone = $this->createContent('2027-01-15 10:00:00', '2027-01-15 12:00:00');
        $locationZone->setLocation($location);
        $this->assertSame('20270115T010000Z', (string) $this->singleEvent($this->parse($this->generateFeed([$locationZone])))->DTSTART);

        $applicationZone = $this->createContent('2027-01-15 10:00:00', '2027-01-15 12:00:00');
        $this->assertSame('20270115T090000Z', (string) $this->singleEvent($this->parse($this->generateFeed([$applicationZone])))->DTSTART);
    }

    public function testResolveTimezoneFollowsEventLocationApplication(): void
    {
        $content = $this->createContent('2027-07-01 10:00:00', null);
        $this->assertSame('Europe/Berlin', $this->generator->resolveTimezone($content)->getName());

        $location = new Location();
        $location->setName('Las Vegas');
        $location->setTimezone('America/Los_Angeles');
        $content->setLocation($location);
        $this->assertSame('America/Los_Angeles', $this->generator->resolveTimezone($content)->getName());

        $content->setTimezone('Asia/Taipei');
        $this->assertSame('Asia/Taipei', $this->generator->resolveTimezone($content)->getName());
    }

    public function testCancelledAndPostponedEventsAreMarked(): void
    {
        $cancelled = $this->createContent('2027-07-01 10:00:00', null);
        $cancelled->setStatus('cancelled');
        $postponed = $this->createContent('2027-07-01 10:00:00', null, uuid: 'postponed');
        $postponed->setStatus('postponed');
        $rescheduled = $this->createContent('2027-07-01 10:00:00', null, uuid: 'rescheduled');
        $rescheduled->setStatus('rescheduled');
        $scheduled = $this->createContent('2027-07-01 10:00:00', null, uuid: 'scheduled');

        $this->assertStringContainsString("\r\nSTATUS:CANCELLED\r\n", $this->generateFeed([$cancelled]));
        $this->assertStringContainsString("\r\nSTATUS:TENTATIVE\r\n", $this->generateFeed([$postponed]));
        $this->assertStringNotContainsString('STATUS:', $this->generateFeed([$rescheduled]));
        $this->assertStringNotContainsString('STATUS:', $this->generateFeed([$scheduled]));
    }

    public function testOnlineEventWithoutPlaceUsesTheLinkAsLocation(): void
    {
        $online = $this->createContent('2027-07-01 10:00:00', null);
        $online->setAttendanceMode('online');
        $online->setOnlineUrl('https://stream.example.com/keynote');
        $this->assertSame('https://stream.example.com/keynote', (string) $this->singleEvent($this->parse($this->generateFeed([$online])))->LOCATION);

        $withPlace = $this->createContent('2027-07-01 10:00:00', null, uuid: 'mixed');
        $withPlace->setAttendanceMode('mixed');
        $withPlace->setOnlineUrl('https://stream.example.com/keynote');
        $location = new Location();
        $location->setName('Hall 1');
        $withPlace->setLocation($location);
        $this->assertSame('Hall 1', (string) $this->singleEvent($this->parse($this->generateFeed([$withPlace])))->LOCATION);

        $offline = $this->createContent('2027-07-01 10:00:00', null, uuid: 'offline');
        $offline->setOnlineUrl('https://stream.example.com/keynote');
        $this->assertNull($this->singleEvent($this->parse($this->generateFeed([$offline])))->LOCATION);
    }

    public function testEndBeforeStartIsLeftOut(): void
    {
        $content = $this->createContent('2027-07-01 18:30:00', '2027-07-01 17:00:00');

        $vevent = $this->singleEvent($this->parse($this->generateFeed([$content])));

        $this->assertNull($vevent->DTEND);
    }

    public function testAllDayEventsUseDatesWithoutTimeAndAnExclusiveEnd(): void
    {
        $content = $this->createContent('2027-03-01 00:00:00', '2027-03-03 23:59:59');
        $content->setAllDay(true);

        $ical   = $this->generateFeed([$content]);
        $vevent = $this->singleEvent($this->parse($ical));

        $this->assertStringContainsString("\r\nDTSTART;VALUE=DATE:20270301\r\n", $ical);
        $this->assertStringContainsString("\r\nDTEND;VALUE=DATE:20270304\r\n", $ical);
        $this->assertSame('DATE', (string) $vevent->DTSTART['VALUE']);
    }

    public function testEventAtMidnightIsOnlyAllDayWithTheFlag(): void
    {
        // no guessing from the times any more: without the flag the event is a timed one
        $content = $this->createContent('2027-03-01 00:00:00', '2027-03-01 23:59:59');

        $vevent = $this->singleEvent($this->parse($this->generateFeed([$content])));

        $this->assertSame('20270228T230000Z', (string) $vevent->DTSTART);
        $this->assertSame('20270301T225959Z', (string) $vevent->DTEND);
    }

    public function testSingleAllDayEventWithoutEndHasNoDtend(): void
    {
        $content = $this->createContent('2027-03-01 00:00:00', null);
        $content->setAllDay(true);

        $ical = $this->generateFeed([$content]);

        $this->assertStringContainsString("\r\nDTSTART;VALUE=DATE:20270301\r\n", $ical);
        $this->assertStringNotContainsString('DTEND', $ical);
    }

    public function testSpecialCharactersAreEscapedAndLongLinesAreFolded(): void
    {
        $title   = 'Symfony, Sulu; and "friends" \\ ' . str_repeat('Übergroßer Titel ', 8);
        $summary = "First line\nSecond line, with; special \\ characters " . str_repeat('Wörter ', 30);
        $content = $this->createContent('2027-07-01 10:00:00', '2027-07-01 12:00:00');
        $content->setTitle($title);
        $content->setSummary($summary);

        $ical = $this->generateFeed([$content]);

        foreach (explode("\r\n", $ical) as $line) {
            $this->assertLessThanOrEqual(75, mb_strlen($line), 'Line is longer than 75 octets: ' . $line);
        }
        $this->assertStringContainsString('SUMMARY:Symfony\, Sulu\; and "friends" \\\\ Übergroßer', $ical);
        $this->assertStringContainsString('First line\nSecond line\, with\; special', $ical);

        $vevent = $this->singleEvent($this->parse($ical));
        $this->assertSame($title, (string) $vevent->SUMMARY);
        $this->assertSame($summary, (string) $vevent->DESCRIPTION);
    }

    public function testHtmlInTitleAndSummaryIsStripped(): void
    {
        $content = $this->createContent('2027-07-01 10:00:00', null);
        $content->setSummary('<p>Some <strong>bold</strong> text</p>');

        $vevent = $this->singleEvent($this->parse($this->generateFeed([$content])));

        $this->assertSame('Some bold text', (string) $vevent->DESCRIPTION);
    }

    public function testCategoriesAreTheNamesInTheLocaleWithAFallbackToTheDefaultLocale(): void
    {
        $content = $this->createContent('2027-07-01 10:00:00', null);
        $content->setExcerptCategories([
            $this->createCategory('de', ['de' => 'Künstliche Intelligenz', 'en' => 'AI']),
            $this->createCategory('en', ['en' => 'Web, PHP']),
            $this->createCategory('de', []),
        ]);

        $ical = $this->generateFeed([$content]);

        $this->assertStringContainsString('CATEGORIES:Künstliche Intelligenz,Web\, PHP', $ical);
        $this->assertSame(['Künstliche Intelligenz', 'Web, PHP'], $this->singleEvent($this->parse($ical))->CATEGORIES->getParts());
    }

    public function testNoCategoriesNoProperty(): void
    {
        $content = $this->createContent('2027-07-01 10:00:00', null);

        $this->assertStringNotContainsString('CATEGORIES', $this->generateFeed([$content]));
    }

    public function testUrlIsAbsoluteAndTheUidUsesTheHostOfTheWebsite(): void
    {
        $content = $this->createContent('2027-07-01 10:00:00', null);

        $vevent = $this->singleEvent($this->parse($this->generateFeed([$content])));

        $this->assertSame('https://example.com/de/events/test-event', (string) $vevent->URL);
        $this->assertSame(self::UUID . '@example.com', (string) $vevent->UID);
    }

    public function testUrlIsLookedUpInTheWebspaceAndLocaleOfTheEvent(): void
    {
        $webspaceManager = $this->createMock(WebspaceManagerInterface::class);
        $webspaceManager->expects($this->once())
            ->method('findUrlByResourceLocator')
            ->with('/events/test-event', null, 'de', 'example')
            ->willReturn('https://de.example.com/events/test-event');

        $content = $this->createContent('2027-07-01 10:00:00', null);
        $content->setMainWebspace('example');

        $vevent = $this->singleEvent($this->parse($this->generateFeed([$content], 'Europe/Berlin', $webspaceManager)));

        $this->assertSame('https://de.example.com/events/test-event', (string) $vevent->URL);
        $this->assertSame(self::UUID . '@de.example.com', (string) $vevent->UID);
    }

    public function testWithoutAWebsiteUrlThereIsNoUrlPropertyAndTheUidFallsBackToTheRequestHost(): void
    {
        $webspaceManager = $this->createMock(WebspaceManagerInterface::class);
        $webspaceManager->method('findUrlByResourceLocator')->willReturn(null);
        $this->requestStack->push(Request::create('https://feeds.example.org/de/events/calendar.ics'));

        $vevent = $this->singleEvent($this->parse($this->generateFeed([$this->createContent('2027-07-01 10:00:00', null)], 'Europe/Berlin', $webspaceManager)));

        $this->assertNull($vevent->URL);
        $this->assertSame(self::UUID . '@feeds.example.org', (string) $vevent->UID);
    }

    public function testWithoutRouteThereIsNoUrlProperty(): void
    {
        $content = $this->createContent('2027-07-01 10:00:00', null, route: false);

        $vevent = $this->singleEvent($this->parse($this->generateFeed([$content])));

        $this->assertNull($vevent->URL);
        $this->assertSame(self::UUID . '@example.com', (string) $vevent->UID);
    }

    public function testUidStaysTheSameBetweenRuns(): void
    {
        $content = $this->createContent('2027-07-01 10:00:00', null);

        $first  = (string) $this->singleEvent($this->parse($this->generateFeed([$content])))->UID;
        $second = (string) $this->singleEvent($this->parse($this->generateFeed([$content])))->UID;

        $this->assertSame($first, $second);
    }

    public function testDtstampAndLastModifiedAreTheChangeDateInUtc(): void
    {
        $content = $this->createContent('2027-07-01 10:00:00', null);
        $content->setChanged(new \DateTimeImmutable('2027-01-15 12:00:00', new \DateTimeZone('Europe/Berlin')));

        $vevent = $this->singleEvent($this->parse($this->generateFeed([$content])));

        $this->assertSame('20270115T110000Z', (string) $vevent->DTSTAMP);
        $this->assertSame('20270115T110000Z', (string) $vevent->{'LAST-MODIFIED'});
    }

    public function testEventsWithoutStartTitleOrLiveContentAreLeftOut(): void
    {
        $valid        = $this->createContent('2027-07-01 10:00:00', null);
        $withoutStart = $this->createContent('2027-07-01 10:00:00', null, uuid: 'without-start');
        $withoutStart->setStartDate(null);
        $withoutTitle = $this->createContent('2027-07-01 10:00:00', null, uuid: 'without-title');
        $withoutTitle->setTitle(null);
        $unpublished = new Event('unpublished');

        $this->eventRepository->method('findForIcal')->willReturn([
            $valid->getEvent(),
            $withoutStart->getEvent(),
            $withoutTitle->getEvent(),
            $unpublished,
        ]);
        $this->contentAggregator->method('aggregate')->willReturnCallback(
            static function (Event $event) use ($valid, $withoutStart, $withoutTitle) {
                return match ($event->getUuid()) {
                    self::UUID      => $valid,
                    'without-start' => $withoutStart,
                    'without-title' => $withoutTitle,
                    default         => throw new ContentNotFoundException($event, ['locale' => 'de']),
                };
            }
        );

        $calendar = $this->parse($this->generator->generate([], 'de'));

        $this->assertCount(1, $calendar->select('VEVENT'));
        $this->assertSame([], $calendar->validate());
    }

    public function testGenerateSingleReturnsOneEventWithoutMethod(): void
    {
        $content = $this->createContent('2027-07-01 10:00:00', '2027-07-01 12:00:00');

        $ical = $this->generator->generateSingle($content->getEvent(), $content);

        $calendar = $this->parse($ical);
        $this->assertSame([], $calendar->validate(), $ical);
        $this->assertNull($calendar->METHOD);
        $this->assertSame('20270701T080000Z', (string) $this->singleEvent($calendar)->DTSTART);
        $this->assertStringContainsString('UID:' . self::UUID . '@example.com', $ical);
    }

    /**
     * Runs generate() for a repository that returns the events of the given contents.
     *
     * @param EventDimensionContent[] $contents
     */
    private function generateFeed(array $contents, ?string $timezone = 'Europe/Berlin', ?WebspaceManagerInterface $webspaceManager = null): string
    {
        $byUuid = [];
        foreach ($contents as $content) {
            $byUuid[$content->getEvent()->getUuid()] = $content;
        }

        $repository = $this->createStub(EventRepository::class);
        $repository->method('findForIcal')->willReturn(array_map(
            static fn (EventDimensionContent $content) => $content->getEvent(),
            $contents
        ));
        $aggregator = $this->createStub(ContentAggregatorInterface::class);
        $aggregator->method('aggregate')->willReturnCallback(
            static fn (Event $event) => $byUuid[$event->getUuid()]
        );

        $generator = new ICalGenerator($repository, $aggregator, new EventUrlResolver($webspaceManager ?? $this->webspaceManager), $this->requestStack, new EventTimezoneResolver($timezone));

        return $generator->generate([], 'de');
    }

    private function createGenerator(?string $timezone): ICalGenerator
    {
        return new ICalGenerator(
            $this->eventRepository,
            $this->contentAggregator,
            new EventUrlResolver($this->webspaceManager),
            $this->requestStack,
            new EventTimezoneResolver($timezone),
        );
    }

    private function createContent(
        string $start,
        ?string $end,
        ?\DateTimeZone $storedAs = null,
        bool $route = true,
        string $uuid = self::UUID,
    ): EventDimensionContent {
        $content = new EventDimensionContent(new Event($uuid));
        $content->setStage(DimensionContentInterface::STAGE_LIVE);
        $content->setLocale('de');
        $content->setTitle('Test Event');
        $content->setStartDate(new \DateTimeImmutable($start, $storedAs));
        $content->setEndDate(null === $end ? null : new \DateTimeImmutable($end, $storedAs));
        if ($route) {
            $content->setRoute(new Route('events', $uuid, 'de', '/events/test-event'));
        }

        return $content;
    }

    /**
     * @param array<string, string> $translations locale => name
     */
    private function createCategory(string $defaultLocale, array $translations): CategoryInterface
    {
        $category = $this->createStub(CategoryInterface::class);
        $category->method('getDefaultLocale')->willReturn($defaultLocale);
        $category->method('findTranslationByLocale')->willReturnCallback(
            function (?string $locale) use ($translations) {
                if (!isset($translations[$locale])) {
                    return null;
                }

                $translation = $this->createStub(CategoryTranslationInterface::class);
                $translation->method('getTranslation')->willReturn($translations[$locale]);

                return $translation;
            }
        );

        return $category;
    }

    private function parse(string $ical): VCalendar
    {
        $calendar = Reader::read($ical);
        $this->assertInstanceOf(VCalendar::class, $calendar);

        return $calendar;
    }

    private function singleEvent(VCalendar $calendar): VEvent
    {
        $events = $calendar->select('VEVENT');
        $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertInstanceOf(VEvent::class, $event);

        return $event;
    }
}
