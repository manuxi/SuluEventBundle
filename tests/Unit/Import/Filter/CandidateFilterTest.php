<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Import\Filter;

use Manuxi\SuluEventBundle\Import\CandidateLocation;
use Manuxi\SuluEventBundle\Import\EventCandidate;
use Manuxi\SuluEventBundle\Import\Filter\CandidateFilter;
use PHPUnit\Framework\TestCase;

class CandidateFilterTest extends TestCase
{
    private const NOW = '2027-03-01 12:00:00';

    public function testWithoutRulesEverythingPasses(): void
    {
        $filter = new CandidateFilter();

        $this->assertTrue($this->evaluate($filter, $this->candidate())->passed);
        $this->assertTrue($this->evaluate($filter, $this->candidate(location: null, attendanceMode: 'online'))->passed);
        $this->assertTrue($this->evaluate($filter, $this->candidate(location: new CandidateLocation('Hall')))->passed);
    }

    public function testCountryIncludeAndExclude(): void
    {
        $include = new CandidateFilter(['countries' => ['include' => ['de', 'FR']]]);
        $this->assertTrue($this->evaluate($include, $this->candidate(country: 'DE'))->passed);
        $this->assertTrue($this->evaluate($include, $this->candidate(country: 'fr'))->passed);

        $result = $this->evaluate($include, $this->candidate(country: 'US'));
        $this->assertFalse($result->passed);
        $this->assertSame('country_not_included', $result->code);
        $this->assertStringContainsString('US', $result->reason);

        $exclude = new CandidateFilter(['countries' => ['exclude' => ['RU']]]);
        $this->assertTrue($this->evaluate($exclude, $this->candidate(country: 'US'))->passed);
        $result = $this->evaluate($exclude, $this->candidate(country: 'RU'));
        $this->assertSame('country_excluded', $result->code);
    }

    public function testUnknownCountryFollowsItsRule(): void
    {
        $noCountry = $this->candidate(location: new CandidateLocation('Some hall'));

        $this->assertTrue($this->evaluate(new CandidateFilter(['countries' => ['include' => ['DE']]]), $noCountry)->passed, 'unknown_country defaults to allow');

        $result = $this->evaluate(new CandidateFilter(['unknown_country' => 'deny']), $noCountry);
        $this->assertFalse($result->passed);
        $this->assertSame('country_unknown', $result->code);

        $noPlace = $this->candidate(location: null);
        $this->assertSame('country_unknown', $this->evaluate(new CandidateFilter(['unknown_country' => 'deny']), $noPlace)->code);
    }

    public function testOnlineOnlyMeansNoPlace(): void
    {
        $deny = new CandidateFilter(['online' => 'deny', 'unknown_country' => 'deny']);

        $result = $this->evaluate($deny, $this->candidate(location: null, attendanceMode: 'online'));
        $this->assertSame('online', $result->code);

        // an online event is not judged by its (missing) country
        $allow = new CandidateFilter(['unknown_country' => 'deny']);
        $this->assertTrue($this->evaluate($allow, $this->candidate(location: null, attendanceMode: 'online'))->passed);

        // with a place it is no "online only" event
        $this->assertTrue($this->evaluate($deny, $this->candidate(country: 'DE', attendanceMode: 'online'))->passed);
    }

    public function testCountryOfAPlaceThatIsOnlyGivenByIdIsPassedIn(): void
    {
        $filter    = new CandidateFilter(['countries' => ['include' => ['DE']], 'unknown_country' => 'deny']);
        $candidate = $this->candidate(location: null, locationId: 3);

        $this->assertSame('country_unknown', $this->evaluate($filter, $candidate)->code);
        $this->assertTrue($filter->evaluate('ical.a', $candidate, new \DateTimeImmutable(self::NOW), 'DE', 'conference')->passed);
        $this->assertSame('country_not_included', $filter->evaluate('ical.a', $candidate, new \DateTimeImmutable(self::NOW), 'US', 'conference')->code);
    }

    public function testTypes(): void
    {
        $filter = new CandidateFilter(['types' => ['conference']]);

        $this->assertTrue($this->evaluate($filter, $this->candidate(type: 'conference'))->passed);
        $result = $this->evaluate($filter, $this->candidate(type: 'meetup'));
        $this->assertSame('type', $result->code);
        $this->assertStringContainsString('meetup', $result->reason);
    }

    public function testTopicsOneMustMatch(): void
    {
        $filter = new CandidateFilter(['topics' => ['ai', 'security']]);

        $this->assertTrue($this->evaluate($filter, $this->candidate(categoryKeys: ['web', 'security']))->passed);
        $this->assertSame('topic', $this->evaluate($filter, $this->candidate(categoryKeys: ['web']))->code);
        $this->assertSame('topic', $this->evaluate($filter, $this->candidate(categoryKeys: []))->code);
    }

    public function testTitleExcludeIgnoresCaseAndAlwaysApplies(): void
    {
        $filter = new CandidateFilter(['title_exclude' => ['^webinar', 'sale ~ ends']]);

        $this->assertSame('title', $this->evaluate($filter, $this->candidate(title: 'WEBINAR: Intro to PHP'))->code);
        $this->assertSame('title', $this->evaluate($filter, $this->candidate(title: 'Sale ~ ends soon'))->code, 'a tilde in the pattern is fine');
        $this->assertTrue($this->evaluate($filter, $this->candidate(title: 'A webinar series'))->passed, 'anchored at the start');
        $this->assertSame('title', $this->evaluate($filter, $this->candidate(title: 'Webinar Conf', series: 'conf'))->code, 'also for a series');
    }

    public function testHostExcludeIncludesSubdomains(): void
    {
        $filter = new CandidateFilter(['host_exclude' => ['Spam.example']]);

        $this->assertSame('host', $this->evaluate($filter, $this->candidate(sourceUrl: 'https://spam.example/event'))->code);
        $this->assertSame('host', $this->evaluate($filter, $this->candidate(sourceUrl: 'https://tickets.spam.example/e/1'))->code);
        $this->assertSame('host', $this->evaluate($filter, $this->candidate(registrationUrl: 'https://spam.example/buy'))->code);
        $this->assertTrue($this->evaluate($filter, $this->candidate(sourceUrl: 'https://notspam.example/'))->passed, 'a different host with the same ending');
        $this->assertSame('host', $this->evaluate($filter, $this->candidate(sourceUrl: 'https://spam.example/', series: 'conf'))->code, 'also for a series');
    }

    public function testMaxMonthsAhead(): void
    {
        $filter = new CandidateFilter(['max_months_ahead' => 6]);

        $this->assertTrue($this->evaluate($filter, $this->candidate(start: '2027-08-31 10:00:00'))->passed);
        $result = $this->evaluate($filter, $this->candidate(start: '2027-10-01 10:00:00'));
        $this->assertSame('too_far_ahead', $result->code);
    }

    public function testSeriesBypassesCountryUnknownCountryAndOnlineByDefault(): void
    {
        $filter = new CandidateFilter(['countries' => ['include' => ['DE']], 'unknown_country' => 'deny', 'online' => 'deny', 'topics' => ['ai']]);

        $this->assertSame('country_not_included', $this->evaluate($filter, $this->candidate(country: 'US'))->code);
        $this->assertSame('topic', $this->evaluate($filter, $this->candidate(country: 'DE', series: 'conf'))->code, 'the topic is not bypassed by default');

        $inSeries = $this->candidate(country: 'US', series: 'conf', categoryKeys: ['ai']);
        $this->assertTrue($this->evaluate($filter, $inSeries)->passed);
        $this->assertTrue($this->evaluate($filter, $this->candidate(location: new CandidateLocation('Hall'), series: 'conf', categoryKeys: ['ai']))->passed);
        $this->assertTrue($this->evaluate($filter, $this->candidate(location: null, attendanceMode: 'online', series: 'conf', categoryKeys: ['ai']))->passed);
    }

    public function testAMatchKeyAloneMakesItASeries(): void
    {
        $filter = new CandidateFilter(['countries' => ['include' => ['DE']]]);

        $this->assertTrue($this->evaluate($filter, $this->candidate(country: 'US', series: null, matchKey: 'conf-2027'))->passed);
    }

    public function testSeriesBypassCanBeChanged(): void
    {
        $filter = new CandidateFilter(['countries' => ['include' => ['DE']], 'topics' => ['ai'], 'types' => ['conference'], 'max_months_ahead' => 3, 'series_bypass' => ['topics', 'types', 'max_months_ahead']]);

        // countries are no longer bypassed, the others are
        $this->assertSame('country_not_included', $this->evaluate($filter, $this->candidate(country: 'US', series: 'conf'))->code);
        $this->assertTrue($this->evaluate($filter, $this->candidate(country: 'DE', series: 'conf', type: 'meetup', categoryKeys: [], start: '2028-01-01 10:00:00'))->passed);
        $this->assertSame('topic', $this->evaluate($filter, $this->candidate(country: 'DE', type: 'conference', start: '2027-04-01 10:00:00'))->code);
    }

    public function testRulesOfASourceOverrideTheGlobalOnesRuleByRule(): void
    {
        $filter = new CandidateFilter(
            ['countries' => ['include' => ['DE'], 'exclude' => ['AT']], 'topics' => ['ai'], 'max_months_ahead' => 6],
            [
                'ical.b'     => ['countries' => ['include' => ['US']], 'topics' => []],
                'confs-tech' => ['max_months_ahead' => 12],
            ],
        );

        // ical.a: the global rules
        $this->assertSame('country_not_included', $filter->evaluate('ical.a', $this->candidate(country: 'US', categoryKeys: ['ai']), new \DateTimeImmutable(self::NOW))->code);
        $this->assertSame('topic', $filter->evaluate('ical.a', $this->candidate(country: 'DE'), new \DateTimeImmutable(self::NOW))->code);

        // ical.b: its own country list (the exclude of the global rule stays), no topic rule
        $this->assertTrue($filter->evaluate('ical.b', $this->candidate(country: 'US'), new \DateTimeImmutable(self::NOW))->passed);
        $this->assertSame('country_not_included', $filter->evaluate('ical.b', $this->candidate(country: 'DE'), new \DateTimeImmutable(self::NOW))->code);
        $this->assertSame('country_excluded', $filter->evaluate('ical.b', $this->candidate(country: 'AT'), new \DateTimeImmutable(self::NOW))->code);

        // confs-tech: only the number of months differs
        $far = $this->candidate(country: 'DE', categoryKeys: ['ai'], start: '2028-01-15 10:00:00');
        $this->assertSame('too_far_ahead', $filter->evaluate('ical.a', $far, new \DateTimeImmutable(self::NOW))->code);
        $this->assertTrue($filter->evaluate('confs-tech', $far, new \DateTimeImmutable(self::NOW))->passed);
    }

    public function testTagsAllowedKeepsOnlyTheListedOnesIgnoringCase(): void
    {
        $filter = new CandidateFilter(['tags_allowed' => ['Symfony', 'PHP']], ['editorial' => ['tags_allowed' => ['AI']]]);

        $this->assertSame([['symfony', 'PHP'], ['Rust', 'Laravel']], $filter->filterTags('ical.a', ['symfony', 'Rust', 'PHP', 'Laravel']));
        $this->assertSame([['symfony', 'PHP'], ['Rust']], $filter->filterTags(null, ['symfony', 'Rust', 'PHP']));
        $this->assertSame([['ai'], ['PHP']], $filter->filterTags('editorial', ['ai', 'PHP']));
        $this->assertSame([['Anything'], []], (new CandidateFilter())->filterTags('ical.a', ['Anything']), 'no list: all tags');
    }

    public function testKosovoIsAcceptedAlthoughItHasNoIsoCode(): void
    {
        $filter = new CandidateFilter(['countries' => ['include' => ['xk']]]);

        $this->assertTrue($this->evaluate($filter, $this->candidate(country: 'XK'))->passed);
        $this->assertFalse($this->evaluate($filter, $this->candidate(country: 'DE'))->passed);
    }

    public function testWrongRulesAreReportedWhenTheFilterIsBuilt(): void
    {
        foreach ([
            [['countries' => ['include' => ['XX']]], 'country code'],
            [['countries' => ['include' => 'DE']], 'must be a list'],
            [['countries' => ['allow' => ['DE']]], 'only have'],
            [['unknown_country' => 'maybe'], 'allow'],
            [['online' => true], 'allow'],
            [['title_exclude' => ['(unclosed']], 'regular expression'],
            [['max_months_ahead' => 0], 'months'],
            [['series_bypass' => ['title_exclude']], 'cannot be bypassed'],
            [['host_exclude' => [' ']], 'not empty'],
            [['colour' => 'red'], 'unknown rule'],
        ] as [$rules, $message]) {
            try {
                new CandidateFilter($rules);
                $this->fail('not reported: ' . json_encode($rules));
            } catch (\InvalidArgumentException $e) {
                $this->assertStringContainsString($message, $e->getMessage(), json_encode($rules));
            }
        }

        try {
            new CandidateFilter([], ['ical.a' => ['countries' => ['exclude' => ['ZZ']]]]);
            $this->fail('the rules of a source are checked as well');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('source_filters.ical.a', $e->getMessage());
        }
    }

    private function evaluate(CandidateFilter $filter, EventCandidate $candidate): \Manuxi\SuluEventBundle\Import\Filter\FilterResult
    {
        return $filter->evaluate('ical.a', $candidate, new \DateTimeImmutable(self::NOW), null, $candidate->type);
    }

    private function candidate(
        string $title = 'Example Conf 2027',
        ?string $country = 'DE',
        mixed $location = '__default__',
        string $attendanceMode = 'offline',
        ?string $type = 'conference',
        array $categoryKeys = ['web'],
        ?string $series = null,
        ?string $matchKey = null,
        ?string $sourceUrl = null,
        ?string $registrationUrl = null,
        string $start = '2027-06-12 10:00:00',
        ?int $locationId = null,
    ): EventCandidate {
        if ('__default__' === $location) {
            $location = null !== $country ? new CandidateLocation('Hall', city: 'Somewhere', countryCode: $country) : null;
        }

        return new EventCandidate(
            externalId: 'e1',
            titles: ['en' => $title],
            startDate: new \DateTimeImmutable($start),
            attendanceMode: $attendanceMode,
            location: $location,
            registrationUrl: $registrationUrl,
            type: $type,
            categoryKeys: $categoryKeys,
            sourceUrl: $sourceUrl,
            series: $series,
            matchKey: $matchKey,
            locationId: $locationId,
        );
    }
}
