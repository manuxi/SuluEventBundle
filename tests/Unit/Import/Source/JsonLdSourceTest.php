<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Import\Source;

use Manuxi\SuluEventBundle\Import\EventCandidate;
use Manuxi\SuluEventBundle\Import\Http\FetchException;
use Manuxi\SuluEventBundle\Import\Http\FetchResult;
use Manuxi\SuluEventBundle\Import\Http\ImportHttpClient;
use Manuxi\SuluEventBundle\Import\Resolver\TimezoneGuesser;
use Manuxi\SuluEventBundle\Import\Source\JsonLdSource;
use Manuxi\SuluEventBundle\Import\SourceConfig;
use PHPUnit\Framework\TestCase;

class JsonLdSourceTest extends TestCase
{
    public function testSourceKeyAndPriority(): void
    {
        $source = $this->createSource();

        $this->assertSame('jsonld.example', $source->getKey());
        $this->assertSame(100, $source->getPriority());
        $this->assertSame(70, $this->createSource(priority: 70)->getPriority());
    }

    public function testEventWithOffsetGetsTheZoneOfThePlaceAndAllItsFacts(): void
    {
        $candidate = $this->parse('jsonld-simple.html', 'https://conf.example.com/')['https://conf.example.com/2027#event'];

        $this->assertSame(['en' => 'Example Conf 2027'], $candidate->titles);
        $this->assertSame('Europe/Berlin', $candidate->timezone);
        $this->assertSame('2027-06-12 10:00:00', $candidate->startDate->format('Y-m-d H:i:s'));
        $this->assertSame('Europe/Berlin', $candidate->startDate->getTimezone()->getName());
        $this->assertSame('2027-06-13 17:00:00', $candidate->endDate->format('Y-m-d H:i:s'));
        $this->assertFalse($candidate->allDay);
        $this->assertSame('scheduled', $candidate->status);
        $this->assertSame('offline', $candidate->attendanceMode);
        $this->assertSame('https://conf.example.com/2027', $candidate->sourceUrl);
        $this->assertSame(['en', 'de'], $candidate->languages);
        $this->assertFalse($candidate->isFree);
        $this->assertSame('https://conf.example.com/tickets', $candidate->registrationUrl);
        $this->assertSame(['en' => '199 EUR'], $candidate->priceInfos);
        $this->assertSame('meetup', $candidate->type);
        $this->assertSame(['web-php'], $candidate->categoryKeys);
        $this->assertSame(['Symfony'], $candidate->tags);
        $this->assertSame('example', $candidate->series);

        $this->assertSame('Messe Hall 3', $candidate->location->name);
        $this->assertSame('Messeplatz 1', $candidate->location->street);
        $this->assertSame('50679', $candidate->location->postalCode);
        $this->assertSame('Cologne', $candidate->location->city);
        $this->assertSame('DE', $candidate->location->countryCode);
        $this->assertSame(50.94, $candidate->location->latitude);
        $this->assertSame(6.98, $candidate->location->longitude);
    }

    public function testDescriptionIsOnlyAHintAndPersonsAsOrganizersAreNotTaken(): void
    {
        $candidate = $this->parse('jsonld-simple.html', 'https://conf.example.com/')['https://conf.example.com/2027#event'];

        $this->assertSame('Two days about PHP & friends.', $candidate->sourceDescription);
        $this->assertSame([], $candidate->texts);
        $this->assertSame([], $candidate->summaries);
        $this->assertSame('Example e.V.', $candidate->organizerName);
        $this->assertSame('https://example.com', $candidate->organizerUrl);
        $this->assertStringNotContainsString('Jane', serialize($candidate));
    }

    public function testEventSubtypeWithLocalTimeAndPlaceAsText(): void
    {
        $candidate = $this->parse('jsonld-simple.html', 'https://conf.example.com/')['https://conf.example.com/evening'];

        $this->assertSame('Free community evening', $candidate->titles['en']);
        $this->assertTrue($candidate->isFree);
        $this->assertSame('Pub Example, Cologne', $candidate->location->name);
        $this->assertSame('2027-05-04 19:00:00', $candidate->startDate->format('Y-m-d H:i:s'));
        $this->assertNull($candidate->timezone);
        $this->assertNull($candidate->sourceDescription);
    }

    public function testGraphListAndSubtypesAreFoundAndOtherNodesIgnored(): void
    {
        $candidates = $this->parse('jsonld-graph.html', 'https://summit.example.com/program');

        $this->assertEqualsCanonicalizing([
            'https://summit.example.com/online-day',
            'https://summit.example.com/listed',
        ], array_values(array_filter(array_keys($candidates), static fn (string $id): bool => str_starts_with($id, 'https://summit.example.com/'))));
        $this->assertCount(4, $candidates);
        foreach ($candidates as $candidate) {
            $this->assertNotSame('The series itself', $candidate->titles['en']);
            $this->assertNotSame('Not read from here', $candidate->titles['en']);
            $this->assertNotSame("Last year's event", $candidate->titles['en']);
        }
    }

    public function testOnlineEventWithVirtualLocation(): void
    {
        $candidate = $this->parse('jsonld-graph.html', 'https://summit.example.com/program')['https://summit.example.com/online-day'];

        $this->assertSame('online', $candidate->attendanceMode);
        $this->assertSame('https://stream.example.com/summit', $candidate->onlineUrl);
        $this->assertNull($candidate->location);
        $this->assertSame(
            '2027-09-15 09:00:00',
            $candidate->startDate->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
            'the moment is kept, whatever zone is shown'
        );
    }

    public function testDateOnlyEventIsAllDayWithCountryNameAndZoneByCoordinates(): void
    {
        $candidate = array_values(array_filter(
            $this->parse('jsonld-graph.html', 'https://summit.example.com/program'),
            static fn (EventCandidate $candidate): bool => 'Street festival' === $candidate->titles['en']
        ))[0];

        $this->assertTrue($candidate->allDay);
        $this->assertSame('2027-08-20', $candidate->startDate->format('Y-m-d'));
        $this->assertSame('2027-08-22 23:59:59', $candidate->endDate->format('Y-m-d H:i:s'));
        $this->assertSame('America/Vancouver', $candidate->timezone);
        $this->assertSame('CA', $candidate->location->countryCode);
        $this->assertSame(49.28, $candidate->location->latitude);
        $this->assertSame(-123.12, $candidate->location->longitude);
        // no id and no url: the id is made of title and date, so it stays the same on every run
        $this->assertSame(sha1('Street festival|2027-08-20'), $candidate->externalId);
        $this->assertSame('https://summit.example.com/program', $candidate->sourceUrl);
    }

    public function testCancelledEventIsMarked(): void
    {
        $candidate = array_values(array_filter(
            $this->parse('jsonld-graph.html', 'https://summit.example.com/program'),
            static fn (EventCandidate $candidate): bool => 'Cancelled concert' === $candidate->titles['en']
        ))[0];

        $this->assertSame('cancelled', $candidate->status);
        $this->assertSame('Concert hall', $candidate->location->name);
    }

    public function testBrokenJsonAndIncompleteEventsAreSkippedButTheRestIsRead(): void
    {
        $candidates = array_values($this->parse('jsonld-broken.html', 'https://example.com/'));

        $this->assertCount(1, $candidates);
        $this->assertSame('Survivor', $candidates[0]->titles['en']);
        $this->assertSame('Somewhere', $candidates[0]->location->name);
    }

    public function testAPageWithoutStructuredDataGivesNothing(): void
    {
        $this->assertSame([], iterator_to_array($this->createSource()->parse('<html><body>Nothing</body></html>', 'https://example.com/'), false));
        $this->assertSame([], iterator_to_array($this->createSource()->parse('', 'https://example.com/'), false));
    }

    public function testConfiguredTimezoneWinsOverTheGuess(): void
    {
        $candidates = $this->parse('jsonld-simple.html', 'https://conf.example.com/', timezone: 'Europe/London');

        $candidate = $candidates['https://conf.example.com/2027#event'];
        $this->assertSame('Europe/London', $candidate->timezone);
        $this->assertSame('2027-06-12 09:00:00', $candidate->startDate->format('Y-m-d H:i:s'));
        $this->assertSame('2027-05-04 19:00:00', $candidates['https://conf.example.com/evening']->startDate->format('Y-m-d H:i:s'));
        $this->assertSame('Europe/London', $candidates['https://conf.example.com/evening']->startDate->getTimezone()->getName());
    }

    public function testFetchReadsTheConfiguredUrl(): void
    {
        $httpClient = $this->createMock(ImportHttpClient::class);
        $httpClient->expects($this->once())->method('get')->with('https://example.com/events')
            ->willReturn(new FetchResult('https://example.com/events', (string) file_get_contents(__DIR__ . '/../../../Fixtures/import/jsonld-simple.html')));

        $candidates = iterator_to_array($this->createSource(httpClient: $httpClient)->fetch(), false);

        $this->assertCount(2, $candidates);
    }

    public function testFetchErrorsAreNotSwallowed(): void
    {
        $httpClient = $this->createMock(ImportHttpClient::class);
        $httpClient->method('get')->willThrowException(new FetchException('robots.txt does not allow'));

        $this->expectException(FetchException::class);

        iterator_to_array($this->createSource(httpClient: $httpClient)->fetch());
    }

    /**
     * @return array<string, EventCandidate>
     */
    private function parse(string $fixture, string $pageUrl, ?string $timezone = null): array
    {
        $html       = (string) file_get_contents(__DIR__ . '/../../../Fixtures/import/' . $fixture);
        $candidates = [];
        foreach ($this->createSource(timezone: $timezone)->parse($html, $pageUrl) as $candidate) {
            $candidates[$candidate->externalId] = $candidate;
        }

        return $candidates;
    }

    private function createSource(?string $timezone = null, ?int $priority = null, ?ImportHttpClient $httpClient = null): JsonLdSource
    {
        return new JsonLdSource(
            new SourceConfig(
                SourceConfig::KIND_JSONLD,
                'example',
                'https://example.com/events',
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
