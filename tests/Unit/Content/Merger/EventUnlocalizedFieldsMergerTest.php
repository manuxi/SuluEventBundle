<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Content\Merger;

use Manuxi\SuluEventBundle\Content\Merger\EventUnlocalizedFieldsMerger;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use PHPUnit\Framework\TestCase;

class EventUnlocalizedFieldsMergerTest extends TestCase
{
    private EventUnlocalizedFieldsMerger $merger;

    protected function setUp(): void
    {
        $this->merger = new EventUnlocalizedFieldsMerger();
    }

    public function testMergeCopiesFields(): void
    {
        $target = $this->createMock(EventDimensionContent::class);
        $source = $this->createMock(EventDimensionContent::class);

        $source->method('getType')->willReturn('concert');
        $source->method('getEmail')->willReturn('mail@example.com');

        $target->expects($this->once())->method('setType')->with('concert');
        $target->expects($this->once())->method('setEmail')->with('mail@example.com');

        $this->merger->merge($target, $source);
    }

    public function testMergeCarriesTheEventDetailsOverAndKeepsTheLocalizedPriceInfo(): void
    {
        $unlocalized = new EventDimensionContent(new Event());
        $unlocalized->applyUnlocalizedDetails([
            'allDay' => true,
            'timezone' => 'Asia/Tokyo',
            'status' => 'postponed',
            'attendanceMode' => 'mixed',
            'languages' => ['ja'],
        ]);
        $localized = new EventDimensionContent(new Event());
        $localized->applyLocalizedDetails(['priceInfo' => 'from 99 EUR']);
        $localized->applyUnlocalizedDetails($this->detailsOf($unlocalized));

        $target = new EventDimensionContent(new Event());
        $this->merger->merge($target, $unlocalized);
        $this->merger->merge($target, $localized);

        $this->assertTrue($target->getAllDay());
        $this->assertSame('Asia/Tokyo', $target->getTimezone());
        $this->assertSame('postponed', $target->getStatus());
        $this->assertSame('mixed', $target->getAttendanceMode());
        $this->assertSame(['ja'], $target->getLanguages());
        $this->assertSame('from 99 EUR', $target->getPriceInfo());
    }

    /**
     * @return array<string, mixed>
     */
    private function detailsOf(EventDimensionContent $content): array
    {
        return [
            'allDay' => $content->getAllDay(),
            'timezone' => $content->getTimezone(),
            'status' => $content->getStatus(),
            'attendanceMode' => $content->getAttendanceMode(),
            'languages' => $content->getLanguages(),
        ];
    }

    public function testMergeIgnoresNulls(): void
    {
        $target = $this->createMock(EventDimensionContent::class);
        $source = $this->createMock(EventDimensionContent::class);

        $source->method('getType')->willReturn(null);

        $target->expects($this->never())->method('setType');

        $this->merger->merge($target, $source);
    }
}
