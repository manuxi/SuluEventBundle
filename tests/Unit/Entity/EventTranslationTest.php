<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventTranslation;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class EventTranslationTest extends SuluTestCase
{
    private ObjectProphecy $event;
    private EventTranslation $translation;
    private string $testString = "Lorem ipsum dolor sit amet, ...";

    protected function setUp(): void
    {
        $this->event       = $this->prophesize(Event::class);
        $this->translation = new EventTranslation($this->event->reveal(), 'de');
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    public function testEvent(): void
    {
        $this->assertSame($this->event->reveal(), $this->translation->getEvent());
    }

    public function testLocale(): void
    {
        $this->assertSame('de', $this->translation->getLocale());
    }

    public function testTitle(): void
    {
        $this->assertNull($this->translation->getTitle());
        $this->assertSame($this->translation, $this->translation->setTitle($this->testString));
        $this->assertSame($this->testString, $this->translation->getTitle());
    }

    public function testSubtitle(): void
    {
        $this->assertNull($this->translation->getSubtitle());
        $this->assertSame($this->translation, $this->translation->setSubtitle($this->testString));
        $this->assertSame($this->testString, $this->translation->getSubtitle());
    }

    public function testSummary(): void
    {
        $this->assertNull($this->translation->getSummary());
        $this->assertSame($this->translation, $this->translation->setSummary($this->testString));
        $this->assertSame($this->testString, $this->translation->getSummary());
    }

    public function testText(): void
    {
        $this->assertNull($this->translation->getText());
        $this->assertSame($this->translation, $this->translation->setText($this->testString));
        $this->assertSame($this->testString, $this->translation->getText());
    }

    public function testFooter(): void
    {
        $this->assertNull($this->translation->getFooter());
        $this->assertSame($this->translation, $this->translation->setFooter($this->testString));
        $this->assertSame($this->testString, $this->translation->getFooter());
    }

    public function testRoutePath(): void
    {
        $testRoutePath = 'events/event-100';
        $this->assertEmpty($this->translation->getRoutePath());
        $this->assertSame($this->translation, $this->translation->setRoutePath($testRoutePath));
        $this->assertSame($testRoutePath, $this->translation->getRoutePath());
    }


}
