<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Content\DataMapper;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Content\DataMapper\EventUnlocalizedDataMapper;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use PHPUnit\Framework\TestCase;

class EventUnlocalizedDataMapperTest extends TestCase
{
    private EventUnlocalizedDataMapper $mapper;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->mapper = new EventUnlocalizedDataMapper($this->entityManager);
    }

    public function testMapBasicFields(): void
    {
        $unlocalized = $this->createMock(EventDimensionContent::class);
        $localized = $this->createMock(EventDimensionContent::class);

        $data = [
            'type' => 'workshop',
            'email' => 'test@example.com',
            'phoneNumber' => '123456',
            'startDate' => '2023-01-01',
            'endDate' => '2023-01-02'
        ];

        // Type
        $unlocalized->expects($this->once())->method('setType')->with('workshop');
        $localized->expects($this->once())->method('setType')->with('workshop');

        // Email
        $unlocalized->expects($this->once())->method('setEmail')->with('test@example.com');
        $localized->expects($this->once())->method('setEmail')->with('test@example.com');

        // Dates
        $unlocalized->expects($this->once())->method('setStartDate');
        $localized->expects($this->once())->method('setStartDate');

        $this->mapper->map($unlocalized, $localized, $data);
    }

    public function testMapDetailsWritesTheSameValuesToBothDimensionContents(): void
    {
        $unlocalized = new EventDimensionContent(new Event());
        $localized = new EventDimensionContent(new Event());

        $this->mapper->map($unlocalized, $localized, [
            'timezone' => 'America/Los_Angeles',
            'status' => 'cancelled',
            'attendanceMode' => 'online',
            'onlineUrl' => 'https://stream.example.com',
            'registrationUrl' => 'https://tickets.example.com',
            'isFree' => true,
            'organizerName' => 'Org',
            'organizerUrl' => 'https://org.example.com',
            'languages' => ['de', 'en'],
            'priceInfo' => 'free',
        ]);

        foreach ([$unlocalized, $localized] as $content) {
            $this->assertSame('America/Los_Angeles', $content->getTimezone());
            $this->assertSame('cancelled', $content->getStatus());
            $this->assertSame('online', $content->getAttendanceMode());
            $this->assertSame('https://stream.example.com', $content->getOnlineUrl());
            $this->assertSame('https://tickets.example.com', $content->getRegistrationUrl());
            $this->assertTrue($content->getIsFree());
            $this->assertSame('Org', $content->getOrganizerName());
            $this->assertSame('https://org.example.com', $content->getOrganizerUrl());
            $this->assertSame(['de', 'en'], $content->getLanguages());
        }

        // the price info differs per locale: only the localized content gets it
        $this->assertNull($unlocalized->getPriceInfo());
        $this->assertSame('free', $localized->getPriceInfo());
    }

    public function testMapDetailsNormalizesAllDayEvents(): void
    {
        $unlocalized = new EventDimensionContent(new Event());
        $localized = new EventDimensionContent(new Event());

        $this->mapper->map($unlocalized, $localized, [
            'allDay' => true,
            'startDate' => '2027-03-02T10:00:00',
            'endDate' => '2027-03-03T12:00:00',
        ]);

        foreach ([$unlocalized, $localized] as $content) {
            $this->assertTrue($content->getAllDay());
            $this->assertSame('2027-03-02 00:00:00', $content->getStartDate()->format('Y-m-d H:i:s'));
            $this->assertSame('2027-03-03 23:59:59', $content->getEndDate()->format('Y-m-d H:i:s'));
        }
    }

    public function testMapDetailsAlsoNormalizesWhenOnlyTheFlagIsSent(): void
    {
        $unlocalized = new EventDimensionContent(new Event());
        $localized = new EventDimensionContent(new Event());
        foreach ([$unlocalized, $localized] as $content) {
            $content->setStartDate(new \DateTimeImmutable('2027-03-02 10:00:00'));
        }

        $this->mapper->map($unlocalized, $localized, ['allDay' => true]);

        $this->assertSame('2027-03-02 00:00:00', $localized->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2027-03-02 00:00:00', $unlocalized->getStartDate()->format('Y-m-d H:i:s'));
    }

    public function testMapDetailsLeavesEverythingAloneForPartialData(): void
    {
        $unlocalized = new EventDimensionContent(new Event());
        $localized = new EventDimensionContent(new Event());
        $this->mapper->map($unlocalized, $localized, ['status' => 'cancelled', 'allDay' => true, 'organizerName' => 'Org']);

        $this->mapper->map($unlocalized, $localized, ['title' => 'Only the title']);

        foreach ([$unlocalized, $localized] as $content) {
            $this->assertSame('cancelled', $content->getStatus());
            $this->assertTrue($content->getAllDay());
            $this->assertSame('Org', $content->getOrganizerName());
        }
    }

    public function testMapLocation(): void
    {
        $unlocalized = $this->createMock(EventDimensionContent::class);
        $localized = $this->createMock(EventDimensionContent::class);
        $location = new Location();

        $this->entityManager->method('find')->with(Location::class, 1)->willReturn($location);

        $unlocalized->expects($this->once())->method('setLocation')->with($location);
        $localized->expects($this->once())->method('setLocation')->with($location);

        $this->mapper->map($unlocalized, $localized, ['locationId' => 1]);
    }
}
