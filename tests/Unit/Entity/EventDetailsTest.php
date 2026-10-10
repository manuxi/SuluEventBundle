<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use PHPUnit\Framework\TestCase;

/**
 * The participation, time and ticket details of an event (EventDetailsTrait).
 */
class EventDetailsTest extends TestCase
{
    private EventDimensionContent $content;

    protected function setUp(): void
    {
        $this->content = new EventDimensionContent(new Event('019bf796-423c-7e1f-969c-5c4ece5e9b73'));
    }

    public function testDefaultsKeepTheBehaviourOfExistingEvents(): void
    {
        $this->assertFalse($this->content->getAllDay());
        $this->assertNull($this->content->getTimezone());
        $this->assertSame('scheduled', $this->content->getStatus());
        $this->assertSame('offline', $this->content->getAttendanceMode());
        $this->assertNull($this->content->getOnlineUrl());
        $this->assertNull($this->content->getRegistrationUrl());
        $this->assertNull($this->content->getIsFree());
        $this->assertNull($this->content->getPriceInfo());
        $this->assertNull($this->content->getOrganizerName());
        $this->assertNull($this->content->getOrganizerUrl());
        $this->assertNull($this->content->getLanguages());
    }

    public function testApplyUnlocalizedDetailsTakesOverTheGivenFields(): void
    {
        $this->content->applyUnlocalizedDetails([
            'allDay'          => true,
            'timezone'        => 'America/Los_Angeles',
            'status'          => 'cancelled',
            'attendanceMode'  => 'mixed',
            'onlineUrl'       => ' https://stream.example.com/live ',
            'registrationUrl' => 'https://tickets.example.com',
            'isFree'          => false,
            'organizerName'   => '  Example Organizer ',
            'organizerUrl'    => 'https://organizer.example.com',
            'languages'       => ['DE', 'en', 'de'],
        ]);

        $this->assertTrue($this->content->getAllDay());
        $this->assertSame('America/Los_Angeles', $this->content->getTimezone());
        $this->assertSame('cancelled', $this->content->getStatus());
        $this->assertSame('mixed', $this->content->getAttendanceMode());
        $this->assertSame('https://stream.example.com/live', $this->content->getOnlineUrl());
        $this->assertSame('https://tickets.example.com', $this->content->getRegistrationUrl());
        $this->assertFalse($this->content->getIsFree());
        $this->assertSame('Example Organizer', $this->content->getOrganizerName());
        $this->assertSame('https://organizer.example.com', $this->content->getOrganizerUrl());
        $this->assertSame(['de', 'en'], $this->content->getLanguages());
    }

    public function testKeysThatAreNotPresentAreLeftAlone(): void
    {
        $this->content->applyUnlocalizedDetails(['allDay' => true, 'status' => 'postponed', 'organizerName' => 'Org']);

        // a partial update (e.g. the SEO writer or an MCP update) carries none of these keys
        $this->content->applyUnlocalizedDetails(['summary' => 'Something else']);
        $this->content->applyLocalizedDetails(['summary' => 'Something else']);

        $this->assertTrue($this->content->getAllDay());
        $this->assertSame('postponed', $this->content->getStatus());
        $this->assertSame('Org', $this->content->getOrganizerName());
    }

    public function testEmptyValuesClearOptionalFieldsAndResetStatusAndMode(): void
    {
        $this->content->applyUnlocalizedDetails([
            'timezone'       => 'Asia/Tokyo',
            'status'         => 'cancelled',
            'attendanceMode' => 'online',
            'onlineUrl'      => 'https://stream.example.com',
            'isFree'         => true,
            'languages'      => ['de'],
        ]);

        $this->content->applyUnlocalizedDetails([
            'timezone'       => 'auto',
            'status'         => '',
            'attendanceMode' => null,
            'onlineUrl'      => '',
            'isFree'         => null,
            'languages'      => [],
        ]);

        $this->assertNull($this->content->getTimezone());
        $this->assertSame('scheduled', $this->content->getStatus());
        $this->assertSame('offline', $this->content->getAttendanceMode());
        $this->assertNull($this->content->getOnlineUrl());
        $this->assertNull($this->content->getIsFree());
        $this->assertNull($this->content->getLanguages());
    }

    public function testIsFreeAcceptsTheValuesOfAFormAndOfJson(): void
    {
        foreach ([[true, true], ['1', true], ['false', false], [0, false], ['', null]] as [$given, $expected]) {
            $this->content->applyUnlocalizedDetails(['isFree' => $given]);
            $this->assertSame($expected, $this->content->getIsFree(), 'for ' . var_export($given, true));
        }
    }

    public function testLanguagesCanBeGivenAsCommaSeparatedString(): void
    {
        $this->content->applyUnlocalizedDetails(['languages' => 'de, en']);

        $this->assertSame(['de', 'en'], $this->content->getLanguages());
    }

    /**
     * @dataProvider provideInvalidDetails
     */
    public function testInvalidValuesAreRejectedWithAClearMessage(array $data, string $message): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $this->content->applyUnlocalizedDetails($data);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function provideInvalidDetails(): iterable
    {
        yield 'status' => [['status' => 'foo'], '"foo" is not a valid status'];
        yield 'attendance mode' => [['attendanceMode' => 'hybrid'], '"hybrid" is not a valid attendanceMode'];
        yield 'time zone' => [['timezone' => 'Mars/Base'], '"Mars/Base" is not a valid IANA time zone'];
        yield 'url without scheme' => [['registrationUrl' => 'tickets.example.com'], 'registrationUrl must be a complete http(s) url'];
        yield 'url with another scheme' => [['onlineUrl' => 'javascript:alert(1)'], 'onlineUrl must be a complete http(s) url'];
        yield 'organizer url' => [['organizerUrl' => 'ftp://example.com'], 'organizerUrl must be a complete http(s) url'];
        yield 'language' => [['languages' => ['german']], '"german" is not a language code'];
    }

    public function testPriceInfoIsALocalizedField(): void
    {
        $this->content->applyUnlocalizedDetails(['priceInfo' => 'ab 99 EUR']);
        $this->assertNull($this->content->getPriceInfo());

        $this->content->applyLocalizedDetails(['priceInfo' => '  ab 99 EUR ']);
        $this->assertSame('ab 99 EUR', $this->content->getPriceInfo());

        $this->content->applyLocalizedDetails(['priceInfo' => '']);
        $this->assertNull($this->content->getPriceInfo());
    }

    public function testAllDayEventsCoverWholeDays(): void
    {
        $this->content->setStartDate(new \DateTimeImmutable('2027-03-02 10:15:00'));
        $this->content->setEndDate(new \DateTimeImmutable('2027-03-04 15:00:00'));

        $this->content->normalizeAllDayTimes();
        $this->assertSame('10:15:00', $this->content->getStartDate()->format('H:i:s'), 'not all-day: untouched');

        $this->content->setAllDay(true);
        $this->content->normalizeAllDayTimes();
        $this->assertSame('2027-03-02 00:00:00', $this->content->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2027-03-04 23:59:59', $this->content->getEndDate()->format('Y-m-d H:i:s'));
    }

    public function testAllDayEventWithoutEndStaysWithoutEnd(): void
    {
        $this->content->setAllDay(true);
        $this->content->setStartDate(new \DateTimeImmutable('2027-03-02 10:15:00'));

        $this->content->normalizeAllDayTimes();

        $this->assertNull($this->content->getEndDate());
    }

    public function testSetTemplateDataTakesOverTheDetailsAndNormalizesAllDay(): void
    {
        $this->content->setTemplateData([
            'startDate'      => '2027-03-02T10:00:00',
            'endDate'        => '2027-03-03T12:00:00',
            'allDay'         => true,
            'timezone'       => 'auto',
            'status'         => 'rescheduled',
            'attendanceMode' => 'online',
            'onlineUrl'      => 'https://stream.example.com',
            'isFree'         => true,
            'priceInfo'      => 'free',
            'languages'      => ['en'],
        ]);

        $this->assertTrue($this->content->getAllDay());
        $this->assertNull($this->content->getTimezone());
        $this->assertSame('rescheduled', $this->content->getStatus());
        $this->assertSame('online', $this->content->getAttendanceMode());
        $this->assertTrue($this->content->getIsFree());
        $this->assertSame('free', $this->content->getPriceInfo());
        $this->assertSame(['en'], $this->content->getLanguages());
        $this->assertSame('2027-03-02 00:00:00', $this->content->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2027-03-03 23:59:59', $this->content->getEndDate()->format('Y-m-d H:i:s'));

        // the template data holds the normalized times as well
        $this->assertSame('2027-03-02T00:00:00', $this->content->getTemplateData()['startDate']);
        $this->assertSame('2027-03-03T23:59:59', $this->content->getTemplateData()['endDate']);
    }

    public function testSetTemplateDataWithoutDetailKeysKeepsThem(): void
    {
        $this->content->setTemplateData(['status' => 'cancelled', 'allDay' => true, 'organizerName' => 'Org', 'languages' => ['de']]);

        // the SEO writer and the update tool send template data without any of these keys
        $this->content->setTemplateData(['title' => 'New title', 'seo' => []]);

        $this->assertSame('cancelled', $this->content->getStatus());
        $this->assertTrue($this->content->getAllDay());
        $this->assertSame('Org', $this->content->getOrganizerName());
        $this->assertSame(['de'], $this->content->getLanguages());
    }

    public function testCopyAttributesFromCopiesTheDetails(): void
    {
        $source = new EventDimensionContent(new Event('019bf796-423c-7e1f-969c-5c4ece5e9b74'));
        $source->applyUnlocalizedDetails([
            'allDay'          => true,
            'timezone'        => 'Asia/Taipei',
            'status'          => 'postponed',
            'attendanceMode'  => 'mixed',
            'onlineUrl'       => 'https://stream.example.com',
            'registrationUrl' => 'https://tickets.example.com',
            'isFree'          => true,
            'organizerName'   => 'Org',
            'organizerUrl'    => 'https://org.example.com',
            'languages'       => ['zh'],
        ]);
        $source->applyLocalizedDetails(['priceInfo' => 'free']);

        $this->content->copyAttributesFrom($source);

        $this->assertTrue($this->content->getAllDay());
        $this->assertSame('Asia/Taipei', $this->content->getTimezone());
        $this->assertSame('postponed', $this->content->getStatus());
        $this->assertSame('mixed', $this->content->getAttendanceMode());
        $this->assertSame('https://stream.example.com', $this->content->getOnlineUrl());
        $this->assertSame('https://tickets.example.com', $this->content->getRegistrationUrl());
        $this->assertTrue($this->content->getIsFree());
        $this->assertSame('free', $this->content->getPriceInfo());
        $this->assertSame('Org', $this->content->getOrganizerName());
        $this->assertSame('https://org.example.com', $this->content->getOrganizerUrl());
        $this->assertSame(['zh'], $this->content->getLanguages());
    }

    public function testMergeDetailsFromKeepsWhatTheSourceDoesNotHave(): void
    {
        $unlocalized = new EventDimensionContent(new Event('019bf796-423c-7e1f-969c-5c4ece5e9b73'));
        $unlocalized->applyUnlocalizedDetails(['timezone' => 'Asia/Tokyo', 'organizerName' => 'Org', 'allDay' => true, 'status' => 'cancelled']);

        $localized = new EventDimensionContent(new Event('019bf796-423c-7e1f-969c-5c4ece5e9b73'));
        $localized->applyLocalizedDetails(['priceInfo' => 'from 99 EUR']);
        $localized->applyUnlocalizedDetails(['allDay' => true, 'status' => 'cancelled']);

        // sources are merged in order: unlocalized first, the localized content afterwards
        $merged = new EventDimensionContent(new Event('019bf796-423c-7e1f-969c-5c4ece5e9b73'));
        $merged->mergeDetailsFrom($unlocalized);
        $merged->mergeDetailsFrom($localized);

        $this->assertSame('Asia/Tokyo', $merged->getTimezone());
        $this->assertSame('Org', $merged->getOrganizerName());
        $this->assertSame('from 99 EUR', $merged->getPriceInfo());
        $this->assertTrue($merged->getAllDay());
        $this->assertSame('cancelled', $merged->getStatus());
    }
}
