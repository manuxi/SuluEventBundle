<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Dashboard;

use Manuxi\SuluEventBundle\Dashboard\EventDashboardReader;
use Manuxi\SuluEventBundle\Dashboard\UpcomingEventsWidget;
use PHPUnit\Framework\TestCase;

class UpcomingEventsWidgetTest extends TestCase
{
    /**
     * @param list<array<string, mixed>> $upcoming
     */
    private function widget(array $upcoming, int $unpublished): UpcomingEventsWidget
    {
        $reader = $this->createMock(EventDashboardReader::class);
        $reader->method('upcoming')->willReturn($upcoming);
        $reader->method('countUnpublished')->willReturn($unpublished);

        return new UpcomingEventsWidget($reader);
    }

    public function testDescriptionOfTheTile(): void
    {
        $widget = $this->widget([], 0);

        $this->assertSame('sulu_event_upcoming', $widget->getKey());
        $this->assertSame('list', $widget->getType());
        $this->assertSame('side', $widget->getColumn());
        $this->assertSame('sulu.events.events', $widget->getSecurityContext());
        $this->assertSame('sulu_event_dashboard.upcoming_title', $widget->getTitle());
    }

    public function testRowsWithDatePlaceAndLinkAndUnpublishedCount(): void
    {
        $widget = $this->widget([
            [
                'id' => 'uuid-1',
                'title' => 'Summer market',
                'startDate' => new \DateTimeImmutable('2026-12-03 18:30:00'),
                'endDate' => null,
                'location' => 'Town hall, Berlin',
            ],
            [
                'id' => 'uuid-2',
                'title' => '  ',
                'startDate' => new \DateTimeImmutable('2026-12-24 00:00:00'),
                'endDate' => null,
                'location' => null,
            ],
        ], 3);

        $data = $widget->getData('de');

        $this->assertSame('sulu_event_dashboard.upcoming_empty', $data['empty']);
        $this->assertCount(2, $data['rows']);

        $this->assertSame('Summer market', $data['rows'][0]['title']);
        $this->assertSame('Town hall, Berlin', $data['rows'][0]['text']);
        $this->assertStringContainsString('03.12.2026', $data['rows'][0]['meta']);
        $this->assertStringContainsString('18:30', $data['rows'][0]['meta']);
        $this->assertSame('published', $data['rows'][0]['state']);
        $this->assertSame(
            ['view' => 'sulu_event.event.edit_tabs', 'attributes' => ['id' => 'uuid-1', 'locale' => 'de']],
            $data['rows'][0]['link']
        );

        // no title: the id is shown, no time for an all-day date
        $this->assertSame('uuid-2', $data['rows'][1]['title']);
        $this->assertNull($data['rows'][1]['text']);
        $this->assertStringNotContainsString(':', $data['rows'][1]['meta']);

        $this->assertSame(
            [
                'title' => ['key' => 'sulu_event_dashboard.unpublished', 'parameters' => ['count' => 3]],
                'rows' => [[
                    'title' => 'sulu_event_dashboard.show_unpublished',
                    'link' => ['view' => 'sulu_event.event.list', 'attributes' => ['locale' => 'de']],
                ]],
            ],
            $data['footer']
        );
    }

    public function testNoUnpublishedRowWithoutDrafts(): void
    {
        $data = $this->widget([], 0)->getData('en');

        $this->assertSame([], $data['rows']);
        $this->assertArrayNotHasKey('footer', $data);
        $this->assertSame('sulu_event_dashboard.upcoming_empty', $data['empty']);
    }

    public function testOnlyDraftsStillShowTheCount(): void
    {
        $data = $this->widget([], 2)->getData('en');

        $this->assertSame([], $data['rows']);
        $this->assertSame(2, $data['footer']['title']['parameters']['count']);
    }

    public function testAsksForTheNextFiveInTheToolbarLanguage(): void
    {
        $reader = $this->createMock(EventDashboardReader::class);
        $reader->expects($this->once())
            ->method('upcoming')
            ->with('nl', $this->isInstanceOf(\DateTimeImmutable::class), 5)
            ->willReturn([]);
        $reader->expects($this->once())->method('countUnpublished')->with('nl')->willReturn(0);

        (new UpcomingEventsWidget($reader))->getData('nl');
    }
}
