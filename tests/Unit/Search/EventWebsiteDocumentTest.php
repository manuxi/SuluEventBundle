<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Search;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Search\EventWebsiteDocument;
use PHPUnit\Framework\TestCase;

class EventWebsiteDocumentTest extends TestCase
{
    public function testDocumentCarriesThePreviewInMetadata(): void
    {
        $location = $this->createMock(Location::class);
        $location->method('getName')->willReturn('Stadthalle');
        $location->method('getCity')->willReturn('Aachen');

        $content = $this->createMock(EventDimensionContent::class);
        $content->method('getTitle')->willReturn('KI-Forum');
        $content->method('getSubtitle')->willReturn('Ein Tag zu KI');
        $content->method('getSummary')->willReturn('<p>Kurzfassung &amp; mehr</p>');
        $content->method('getText')->willReturn('<p>Langer Text</p>');
        $content->method('getType')->willReturn('conference');
        $content->method('getStartDate')->willReturn(new \DateTimeImmutable('2027-03-04 09:00:00', new \DateTimeZone('UTC')));
        $content->method('getEndDate')->willReturn(new \DateTimeImmutable('2027-03-04 17:00:00', new \DateTimeZone('UTC')));
        $content->method('getLocation')->willReturn($location);

        $event = $this->createMock(Event::class);
        $event->method('getId')->willReturn('abc');

        $document = EventWebsiteDocument::create($event, $content, 'de', ['site']);

        $this->assertSame('event-abc-de', $document['id']);
        $this->assertSame(['site'], $document['webspaces']);
        // plain text, no tags and no entities
        $this->assertSame(['Ein Tag zu KI', 'Kurzfassung & mehr', 'Langer Text'], $document['content']);
        $this->assertSame([
            'subtitle' => 'Ein Tag zu KI',
            'description' => 'Kurzfassung & mehr',
            'startsAt' => '2027-03-04T09:00:00+00:00',
            'endsAt' => '2027-03-04T17:00:00+00:00',
            'place' => 'Stadthalle, Aachen',
            'type' => 'conference',
        ], $document['metadata']['preview']);
        // only fields of the website schema are stored on the top level
        $this->assertArrayNotHasKey('startDate', $document);
        $this->assertArrayNotHasKey('location', $document);
    }

    public function testEmptyFieldsAreLeftOutOfThePreview(): void
    {
        $content = $this->createMock(EventDimensionContent::class);
        $content->method('getTitle')->willReturn('Nur ein Titel');

        $event = $this->createMock(Event::class);
        $event->method('getId')->willReturn('abc');

        $document = EventWebsiteDocument::create($event, $content, 'de', []);

        $this->assertSame([], $document['content']);
        $this->assertSame([], $document['metadata']);
    }
}
