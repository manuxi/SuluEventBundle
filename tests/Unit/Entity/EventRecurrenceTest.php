<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventRecurrence;
use PHPUnit\Framework\TestCase;

class EventRecurrenceTest extends TestCase
{
    private Event $event;
    private EventRecurrence $recurrence;

    protected function setUp(): void
    {
        $this->event = $this->createMock(Event::class);
        $this->recurrence = new EventRecurrence($this->event);
    }

    public function testConstruction(): void
    {
        $this->assertSame($this->event, $this->recurrence->getEvent());
        $this->assertFalse($this->recurrence->getIsRecurring());
    }

    public function testIsRecurring(): void
    {
        $this->assertFalse($this->recurrence->getIsRecurring());
        $this->recurrence->setIsRecurring(true);
        $this->assertTrue($this->recurrence->getIsRecurring());
    }

    public function testFrequency(): void
    {
        $this->assertNull($this->recurrence->getFrequency());
        $this->recurrence->setFrequency('daily');
        $this->assertEquals('daily', $this->recurrence->getFrequency());
    }

    public function testInterval(): void
    {
        $this->assertEquals(1, $this->recurrence->getInterval());
        $this->recurrence->setInterval(2);
        $this->assertEquals(2, $this->recurrence->getInterval());
    }

    public function testByWeekday(): void
    {
        $this->assertEquals([], $this->recurrence->getByWeekday());
        $weekdays = ['monday', 'wednesday', 'friday'];
        $this->recurrence->setByWeekday($weekdays);
        $this->assertEquals($weekdays, $this->recurrence->getByWeekday());
    }

    public function testEndType(): void
    {
        $this->assertEquals('never', $this->recurrence->getEndType());
        $this->recurrence->setEndType('until');
        $this->assertEquals('until', $this->recurrence->getEndType());
    }

    public function testCount(): void
    {
        $this->assertNull($this->recurrence->getCount());
        $this->recurrence->setCount(10);
        $this->assertEquals(10, $this->recurrence->getCount());
    }

    public function testUntil(): void
    {
        $this->assertNull($this->recurrence->getUntil());
        $until = new \DateTime('2025-12-31');
        $this->recurrence->setUntil($until);
        $this->assertSame($until, $this->recurrence->getUntil());
    }
}