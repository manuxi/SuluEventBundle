<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Import\Source;

use Manuxi\SuluEventBundle\Import\EventCandidate;
use Manuxi\SuluEventBundle\Import\Http\FetchException;
use Manuxi\SuluEventBundle\Import\Http\FetchResult;
use Manuxi\SuluEventBundle\Import\Http\ImportHttpClient;
use Manuxi\SuluEventBundle\Import\Resolver\TimezoneGuesser;
use Manuxi\SuluEventBundle\Import\Source\ICalSource;
use Manuxi\SuluEventBundle\Import\SourceConfig;
use PHPUnit\Framework\TestCase;

class ICalSourceTest extends TestCase
{
    /** @var array<string, EventCandidate> */
    private array $candidates;

    protected function setUp(): void
    {
        $this->candidates = [];
        foreach ($this->createSource()->parse((string) file_get_contents(__DIR__ . '/../../../Fixtures/import/events.ics')) as $candidate) {
            $this->candidates[$candidate->externalId] = $candidate;
        }
    }

    public function testSourceKeyAndPriority(): void
    {
        $source = $this->createSource();

        $this->assertSame('ical.example', $source->getKey());
        $this->assertSame(50, $source->getPriority());
        $this->assertSame(80, $this->createSource(priority: 80)->getPriority());
    }

    public function testPastEventsAndPrivateOnesAreLeftOut(): void
    {
        $this->assertArrayNotHasKey('past@example.com', $this->candidates);
        $this->assertArrayNotHasKey('private@example.com', $this->candidates);
    }

    public function testTimedEventWithTzidKeepsItsZone(): void
    {
        $candidate = $this->candidates['timed-berlin@example.com'];

        $this->assertSame(['en' => 'Symfony, Sulu and friends'], $candidate->titles);
        $this->assertSame('Europe/Berlin', $candidate->timezone);
        $this->assertSame('2027-07-05 18:00:00', $candidate->startDate->format('Y-m-d H:i:s'));
        $this->assertSame('Europe/Berlin', $candidate->startDate->getTimezone()->getName());
        $this->assertSame('2027-07-05 21:00:00', $candidate->endDate->format('Y-m-d H:i:s'));
        $this->assertFalse($candidate->allDay);
        $this->assertSame('scheduled', $candidate->status);
        $this->assertSame('offline', $candidate->attendanceMode);
        $this->assertSame('Example Hub', $candidate->location->name);
        $this->assertSame(52.52, $candidate->location->latitude);
        $this->assertSame('https://example.com/events/symfony-sulu', $candidate->sourceUrl);
        $this->assertSame('meetup', $candidate->type);
        $this->assertSame(['web-php'], $candidate->categoryKeys);
        $this->assertSame(['Symfony'], $candidate->tags);
        $this->assertSame('example', $candidate->series);
    }

    public function testDescriptionIsOnlyAHintForTheEditorsAndPeopleAreNotTaken(): void
    {
        $candidate = $this->candidates['timed-berlin@example.com'];

        $this->assertSame("A long description of the evening.\nSecond line, with a comma.", $candidate->sourceDescription);
        $this->assertSame([], $candidate->texts);
        $this->assertSame([], $candidate->summaries);
        $this->assertNull($candidate->organizerName);
        $this->assertNull($candidate->organizerUrl);
        // nothing of the organizer or attendee anywhere in the candidate
        $this->assertStringNotContainsString('Jane', serialize($candidate));
        $this->assertStringNotContainsString('john@example.com', serialize($candidate));
    }

    public function testAllDayEventUsesTheLastDayAsEnd(): void
    {
        $candidate = $this->candidates['fair-allday@example.com'];

        $this->assertTrue($candidate->allDay);
        $this->assertSame('2027-09-02', $candidate->startDate->format('Y-m-d'));
        $this->assertSame('2027-09-05 23:59:59', $candidate->endDate->format('Y-m-d H:i:s'));
        // the zone of the calendar (X-WR-TIMEZONE)
        $this->assertSame('Europe/Berlin', $candidate->timezone);
    }

    public function testUtcTimesGetTheZoneOfThePlaceAndLumaDetailsAreRead(): void
    {
        $candidate = $this->candidates['evt-luma@events.lu.ma'];

        $this->assertSame('America/Vancouver', $candidate->timezone);
        $this->assertSame('2027-06-10 10:00:00', $candidate->startDate->format('Y-m-d H:i:s'), '17:00 UTC is 10:00 in Vancouver in June');
        $this->assertSame('2027-06-10 12:00:00', $candidate->endDate->format('Y-m-d H:i:s'));
        $this->assertSame('https://luma.com/ai-builders', $candidate->sourceUrl);
        $this->assertSame('123 Water Street, Vancouver', $candidate->location->name);
        $this->assertNull($candidate->organizerName, 'the organizer of a Luma feed is a person');
    }

    public function testLocationThatIsALinkMakesAnOnlineEvent(): void
    {
        $candidate = $this->candidates['online-stream@events.lu.ma'];

        $this->assertSame('online', $candidate->attendanceMode);
        $this->assertSame('https://stream.example.com/keynote', $candidate->onlineUrl);
        $this->assertNull($candidate->location);
        // the end comes from DURATION; without coordinates the zone of the calendar is used
        $this->assertSame('Europe/Berlin', $candidate->timezone);
        $this->assertSame('2027-06-20 18:00:00', $candidate->startDate->format('Y-m-d H:i:s'));
        $this->assertSame('2027-06-20 19:30:00', $candidate->endDate->format('Y-m-d H:i:s'));
    }

    public function testRecurringEventIsExpandedWithoutTheExcludedDate(): void
    {
        $instances = array_filter($this->candidates, static fn (string $id): bool => str_starts_with($id, 'weekly-meetup@example.com#'), \ARRAY_FILTER_USE_KEY);
        $starts    = array_map(static fn (EventCandidate $candidate): string => $candidate->startDate->format('Y-m-d H:i'), array_values($instances));

        // 5 weekly dates from 4 March, minus the 18th (EXDATE); the first ones are before "today" (1 March) - no, all are after
        $this->assertSame(['2027-03-04 18:30', '2027-03-11 18:30', '2027-03-25 18:30', '2027-04-01 18:30'], $starts);
        $this->assertSame(['weekly-meetup@example.com#20270304T173000', 'weekly-meetup@example.com#20270311T173000', 'weekly-meetup@example.com#20270325T173000', 'weekly-meetup@example.com#20270401T163000'], array_keys($instances));
    }

    public function testCancelledEventIsMarked(): void
    {
        $candidate = $this->candidates['cancelled@example.com'];

        $this->assertSame('cancelled', $candidate->status);
        $this->assertSame('America/Los_Angeles', $candidate->timezone);
        $this->assertSame('2027-08-15 10:00:00', $candidate->startDate->format('Y-m-d H:i:s'));
    }

    public function testFloatingTimeIsAWallClockTimeInTheZoneOfTheCalendar(): void
    {
        $candidate = $this->candidates['floating@example.com'];

        $this->assertSame('Europe/Berlin', $candidate->timezone);
        $this->assertSame('2027-07-20 14:00:00', $candidate->startDate->format('Y-m-d H:i:s'));
    }

    public function testConfiguredTimezoneWinsForUtcTimes(): void
    {
        $source     = $this->createSource(timezone: 'Asia/Tokyo');
        $candidates = iterator_to_array($source->parse((string) file_get_contents(__DIR__ . '/../../../Fixtures/import/events.ics')), false);
        $byId       = [];
        foreach ($candidates as $candidate) {
            $byId[$candidate->externalId] = $candidate;
        }

        $this->assertSame('Asia/Tokyo', $byId['evt-luma@events.lu.ma']->timezone);
        $this->assertSame('2027-06-11 02:00:00', $byId['evt-luma@events.lu.ma']->startDate->format('Y-m-d H:i:s'));
    }

    public function testFetchReadsTheConfiguredUrl(): void
    {
        $httpClient = $this->createMock(ImportHttpClient::class);
        $httpClient->expects($this->once())->method('get')->with('https://example.com/feed.ics')
            ->willReturn(new FetchResult('https://example.com/feed.ics', (string) file_get_contents(__DIR__ . '/../../../Fixtures/import/events.ics')));

        $candidates = iterator_to_array($this->createSource(httpClient: $httpClient)->fetch(), false);

        $this->assertNotEmpty($candidates);
    }

    public function testFetchErrorsAreNotSwallowed(): void
    {
        $httpClient = $this->createMock(ImportHttpClient::class);
        $httpClient->method('get')->willThrowException(new FetchException('HTTP 500'));

        $this->expectException(FetchException::class);

        iterator_to_array($this->createSource(httpClient: $httpClient)->fetch());
    }

    public function testAnInvalidFileIsAnError(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('not');

        iterator_to_array($this->createSource()->parse('<html><body>Login</body></html>'));
    }

    private function createSource(?string $timezone = null, ?int $priority = null, ?ImportHttpClient $httpClient = null): ICalSource
    {
        return new ICalSource(
            new SourceConfig(
                SourceConfig::KIND_ICAL,
                'example',
                'https://example.com/feed.ics',
                type: 'meetup',
                categories: ['web-php'],
                tags: ['Symfony'],
                locale: 'en',
                timezone: $timezone,
                series: 'example',
                priority: $priority,
            ),
            $httpClient ?? $this->createStub(ImportHttpClient::class),
            new TimezoneGuesser(),
            static fn (): \DateTimeImmutable => new \DateTimeImmutable('2027-03-01 12:00:00', new \DateTimeZone('Europe/Berlin')),
        );
    }
}
