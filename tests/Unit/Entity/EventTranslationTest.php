<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventTranslation;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class EventTranslationTest extends SuluTestCase
{
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    /**
     * @var Event|ObjectProphecy
     */
    private $event;
    private $translation;
    private $testString = "Lorem ipsum dolor sit amet, ...";

    protected function setUp(): void
    {
        $this->event       = $this->prophesize(Event::class);
        $this->translation = new EventTranslation($this->event->reveal(), 'de');
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

    public function testTeaser(): void
    {
        $this->assertNull($this->translation->getTeaser());
        $this->assertSame($this->translation, $this->translation->setTeaser($this->testString));
        $this->assertSame($this->testString, $this->translation->getTeaser());
    }

    public function testDescription(): void
    {
        $this->assertNull($this->translation->getDescription());
        $this->assertSame($this->translation, $this->translation->setDescription($this->testString));
        $this->assertSame($this->testString, $this->translation->getDescription());
    }

    public function testRoutePath(): void
    {
        $testRoutePath = 'events/event-100';
        $this->assertEmpty($this->translation->getRoutePath());
        $this->assertSame($this->translation, $this->translation->setRoutePath($testRoutePath));
        $this->assertSame($testRoutePath, $this->translation->getRoutePath());
    }


}
