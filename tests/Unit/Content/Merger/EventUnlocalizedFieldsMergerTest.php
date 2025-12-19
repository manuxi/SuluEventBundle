<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Content\Merger;

use Manuxi\SuluEventBundle\Content\Merger\EventUnlocalizedFieldsMerger;
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

    public function testMergeIgnoresNulls(): void
    {
        $target = $this->createMock(EventDimensionContent::class);
        $source = $this->createMock(EventDimensionContent::class);

        $source->method('getType')->willReturn(null);

        $target->expects($this->never())->method('setType');

        $this->merger->merge($target, $source);
    }
}
