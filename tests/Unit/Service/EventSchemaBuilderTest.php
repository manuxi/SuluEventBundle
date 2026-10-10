<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Service;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Service\EventSchemaBuilder;
use Manuxi\SuluEventBundle\Service\EventTimezoneResolver;
use Manuxi\SuluEventBundle\Service\EventUrlResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\MediaBundle\Api\Media as ApiMedia;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Route\Domain\Model\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class EventSchemaBuilderTest extends TestCase
{
    private WebspaceManagerInterface&MockObject $webspaceManager;
    private MediaManagerInterface&MockObject $mediaManager;
    private RequestStack $requestStack;
    private EventSchemaBuilder $builder;

    protected function setUp(): void
    {
        $this->webspaceManager = $this->createMock(WebspaceManagerInterface::class);
        $this->webspaceManager->method('findUrlByResourceLocator')->willReturnCallback(
            static fn (?string $resourceLocator, ?string $environment, string $locale): string => 'https://example.com/' . $locale . $resourceLocator
        );
        $this->mediaManager = $this->createMock(MediaManagerInterface::class);
        $this->requestStack = new RequestStack();

        $this->builder = new EventSchemaBuilder(
            new EventTimezoneResolver('Europe/Berlin'),
            new EventUrlResolver($this->webspaceManager),
            $this->mediaManager,
            $this->requestStack,
        );
    }

    public function testMinimalEvent(): void
    {
        $content = $this->createContent();
        $content->setRoute(new Route('events', 'x', 'de', '/events/meetup'));

        $this->assertSame([
            '@type'               => 'Event',
            'name'                => 'Symfony Meetup',
            'url'                 => 'https://example.com/de/events/meetup',
            'startDate'           => '2027-07-01T18:30:00+02:00',
            'endDate'             => '2027-07-01T21:00:00+02:00',
            'eventStatus'         => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
        ], $this->builder->build($content));
    }

    public function testTimesCarryTheOffsetOfTheZoneOfTheEvent(): void
    {
        $content = $this->createContent();
        $content->setTimezone('America/Los_Angeles');

        $schema = $this->builder->build($content);

        $this->assertSame('2027-07-01T18:30:00-07:00', $schema['startDate']);
        $this->assertSame('2027-07-01T21:00:00-07:00', $schema['endDate']);

        // winter time of the same zone
        $winter = $this->createContent('2027-01-15 18:30:00', '2027-01-15 21:00:00');
        $winter->setTimezone('America/Los_Angeles');
        $this->assertSame('2027-01-15T18:30:00-08:00', $this->builder->build($winter)['startDate']);
    }

    public function testEndIsLeftOutIfItIsNotAfterTheStart(): void
    {
        $this->assertArrayNotHasKey('endDate', $this->builder->build($this->createContent('2027-07-01 18:30:00', null)));
        $this->assertArrayNotHasKey('endDate', $this->builder->build($this->createContent('2027-07-01 18:30:00', '2027-07-01 17:00:00')));
    }

    public function testAllDayEventsCarryDatesOnly(): void
    {
        $content = $this->createContent('2027-03-02 10:00:00', '2027-03-04 15:00:00');
        $content->setAllDay(true);
        $content->normalizeAllDayTimes();

        $schema = $this->builder->build($content);

        $this->assertSame('2027-03-02', $schema['startDate']);
        $this->assertSame('2027-03-04', $schema['endDate']);

        $oneDay = $this->createContent('2027-03-02 00:00:00', '2027-03-02 23:59:59');
        $oneDay->setAllDay(true);
        $oneDaySchema = $this->builder->build($oneDay);
        $this->assertSame('2027-03-02', $oneDaySchema['startDate']);
        $this->assertArrayNotHasKey('endDate', $oneDaySchema);
    }

    /**
     * @dataProvider provideStatuses
     */
    public function testEventStatus(string $status, string $expected): void
    {
        $content = $this->createContent();
        $content->setStatus($status);

        $this->assertSame('https://schema.org/' . $expected, $this->builder->build($content)['eventStatus']);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideStatuses(): iterable
    {
        yield 'scheduled' => ['scheduled', 'EventScheduled'];
        yield 'postponed' => ['postponed', 'EventPostponed'];
        yield 'rescheduled' => ['rescheduled', 'EventRescheduled'];
        yield 'cancelled' => ['cancelled', 'EventCancelled'];
        yield 'moved online' => ['moved_online', 'EventMovedOnline'];
    }

    public function testPlaceWithAddressAndCoordinates(): void
    {
        $content = $this->createContent();
        $content->setLocation($this->createLocation());

        $location = $this->builder->build($content)['location'];

        $this->assertSame([
            '@type'   => 'Place',
            'name'    => 'Convention Center',
            'address' => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => 'Convention Center Dr 3150',
                'postalCode'      => '89109',
                'addressLocality' => 'Las Vegas',
                'addressRegion'   => 'NV',
                'addressCountry'  => 'US',
            ],
            'geo' => ['@type' => 'GeoCoordinates', 'latitude' => 36.1306, 'longitude' => -115.1527],
        ], $location);
    }

    public function testPlaceWithoutAddressOrCoordinatesIsJustAName(): void
    {
        $content  = $this->createContent();
        $location = new Location();
        $location->setName('Hall 1');
        $content->setLocation($location);

        $this->assertSame(['@type' => 'Place', 'name' => 'Hall 1'], $this->builder->build($content)['location']);
    }

    public function testOnlineEventHasAVirtualLocation(): void
    {
        $content = $this->createContent();
        $content->setAttendanceMode('online');
        $content->setOnlineUrl('https://stream.example.com/live');

        $schema = $this->builder->build($content);

        $this->assertSame('https://schema.org/OnlineEventAttendanceMode', $schema['eventAttendanceMode']);
        $this->assertSame(['@type' => 'VirtualLocation', 'url' => 'https://stream.example.com/live'], $schema['location']);
    }

    public function testOnlineEventWithoutLinkAndPlaceHasNoLocation(): void
    {
        $content = $this->createContent();
        $content->setAttendanceMode('online');

        $this->assertArrayNotHasKey('location', $this->builder->build($content));
    }

    public function testMixedEventHasPlaceAndVirtualLocation(): void
    {
        $content = $this->createContent();
        $content->setAttendanceMode('mixed');
        $content->setOnlineUrl('https://stream.example.com/live');
        $content->setLocation($this->createLocation());

        $schema = $this->builder->build($content);

        $this->assertSame('https://schema.org/MixedEventAttendanceMode', $schema['eventAttendanceMode']);
        $this->assertSame(['Place', 'VirtualLocation'], array_column($schema['location'], '@type'));
    }

    public function testOnSiteEventIgnoresAnOnlineLink(): void
    {
        $content = $this->createContent();
        $content->setOnlineUrl('https://stream.example.com/live');

        $this->assertArrayNotHasKey('location', $this->builder->build($content));
    }

    public function testOrganizerOfferFreeLanguagesAndDescription(): void
    {
        $content = $this->createContent();
        $content->setSummary('<p>Talks about <strong>Symfony</strong> &amp; Sulu.</p>');
        $content->setOrganizerName('Example User Group');
        $content->setOrganizerUrl('https://group.example.com');
        $content->setRegistrationUrl('https://tickets.example.com/meetup');
        $content->setPriceInfo('from 10 EUR');
        $content->setIsFree(false);
        $content->setLanguages(['de']);

        $schema = $this->builder->build($content);

        $this->assertSame('Talks about Symfony & Sulu.', $schema['description']);
        $this->assertSame(['@type' => 'Organization', 'name' => 'Example User Group', 'url' => 'https://group.example.com'], $schema['organizer']);
        $this->assertSame(['@type' => 'Offer', 'url' => 'https://tickets.example.com/meetup', 'description' => 'from 10 EUR'], $schema['offers']);
        $this->assertFalse($schema['isAccessibleForFree']);
        $this->assertSame('de', $schema['inLanguage']);

        $content->setLanguages(['de', 'en']);
        $this->assertSame(['de', 'en'], $this->builder->build($content)['inLanguage']);
    }

    public function testNothingIsMadeUp(): void
    {
        $schema = $this->builder->build($this->createContent());

        // no price, no offer without a registration link, "free" stays unknown
        foreach (['offers', 'organizer', 'isAccessibleForFree', 'inLanguage', 'image', 'description', 'url', 'location'] as $key) {
            $this->assertArrayNotHasKey($key, $schema);
        }
    }

    public function testFreeEventWithLinkHasNoPriceText(): void
    {
        $content = $this->createContent();
        $content->setIsFree(true);
        $content->setPriceInfo('would be misleading');
        $content->setRegistrationUrl('https://tickets.example.com');

        $schema = $this->builder->build($content);

        $this->assertTrue($schema['isAccessibleForFree']);
        $this->assertSame(['@type' => 'Offer', 'url' => 'https://tickets.example.com'], $schema['offers']);
    }

    public function testImageIsMadeAbsoluteWithTheHostOfThePage(): void
    {
        $image = $this->createStub(MediaInterface::class);
        $image->method('getId')->willReturn(7);
        $apiMedia = $this->createStub(ApiMedia::class);
        $apiMedia->method('getUrl')->willReturn('/uploads/media/sulu-400x400/image.jpg?v=1');
        $this->mediaManager->expects($this->once())->method('getById')->with(7, 'de')->willReturn($apiMedia);

        $content = $this->createContent();
        $content->setImage($image);
        $content->setRoute(new Route('events', 'x', 'de', '/events/meetup'));

        $this->assertSame('https://example.com/uploads/media/sulu-400x400/image.jpg?v=1', $this->builder->build($content)['image']);
    }

    public function testImageWithoutPageUrlUsesTheHostOfTheRequest(): void
    {
        $this->requestStack->push(Request::create('https://feeds.example.org/de/events'));
        $apiMedia = $this->createStub(ApiMedia::class);
        $apiMedia->method('getUrl')->willReturn('/uploads/media/image.jpg');
        $this->mediaManager->method('getById')->willReturn($apiMedia);

        $content = $this->createContent();
        $content->setExcerptData(['image' => ['id' => 9]]);

        $this->assertSame('https://feeds.example.org/uploads/media/image.jpg', $this->builder->build($content)['image']);
    }

    public function testAMissingMediumLeavesTheImageOut(): void
    {
        $this->mediaManager->method('getById')->willThrowException(new \RuntimeException('not found'));

        $content = $this->createContent();
        $content->setExcerptData(['image' => ['id' => 9]]);

        $this->assertArrayNotHasKey('image', $this->builder->build($content));
    }

    private function createContent(string $start = '2027-07-01 18:30:00', ?string $end = '2027-07-01 21:00:00'): EventDimensionContent
    {
        $content = new EventDimensionContent(new Event('019bf796-423c-7e1f-969c-5c4ece5e9b73'));
        $content->setLocale('de');
        $content->setTitle('Symfony Meetup');
        $content->setStartDate(new \DateTimeImmutable($start));
        $content->setEndDate(null === $end ? null : new \DateTimeImmutable($end));

        return $content;
    }

    private function createLocation(): Location
    {
        $location = new Location();
        $location->setName('Convention Center');
        $location->setStreet('Convention Center Dr');
        $location->setNumber('3150');
        $location->setPostalCode('89109');
        $location->setCity('Las Vegas');
        $location->setState('NV');
        $location->setCountryCode('US');
        $location->setLocation(['lat' => 36.1306, 'long' => -115.1527]);

        return $location;
    }
}
