<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventExcerpt;
use Manuxi\SuluEventBundle\Entity\EventSeo;
use Manuxi\SuluEventBundle\Entity\EventTranslation;
use Manuxi\SuluEventBundle\Entity\Location;
use DateTimeImmutable;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class EventTest extends SuluTestCase
{
    private ObjectProphecy $location;
    private Event $event;
    private string $testString = "Lorem ipsum dolor sit amet, ...";

    protected function setUp(): void
    {
        $this->event = new Event();
        $this->event->setLocale('de');
        $this->location = $this->prophesize(Location::class);
    }

    public function testEnabled(): void
    {
        $this->assertFalse($this->event->isEnabled());
        $this->assertSame($this->event, $this->event->setEnabled(true));
        $this->assertTrue($this->event->isEnabled());
        $this->assertSame($this->event, $this->event->setEnabled(false));
        $this->assertFalse($this->event->isEnabled());
    }

    public function testStartDate(): void
    {
        $now = new DateTimeImmutable();

        $this->assertNull($this->event->getStartDate());
        $this->assertSame($this->event, $this->event->setStartDate($now));
        $this->assertNotNull($this->event->getStartDate());
        $this->assertSame($now, $this->event->getStartDate());
    }

    public function testEndDate(): void
    {
        $now = new DateTimeImmutable();

        $this->assertNull($this->event->getEndDate());
        $this->assertSame($this->event, $this->event->setEndDate($now));
        $this->assertNotNull($this->event->getEndDate());
        $this->assertSame($now, $this->event->getEndDate());
    }

    public function testLocation(): void
    {
        $this->location->getId()->willReturn(42);

        $this->assertNull($this->event->getLocation());
        $this->assertNull($this->event->getLocationId());
        $this->assertSame($this->event, $this->event->setLocation($this->location->reveal()));
        $this->assertNotNull($this->event->getLocation());
        $this->assertSame($this->location->reveal(), $this->event->getLocation());
        $this->assertSame(42, $this->event->getLocationId());
    }

    public function testImage(): void
    {
        $image = $this->prophesize(MediaInterface::class);
        $image->getId()->willReturn(42);

        $this->assertNull($this->event->getImage());
        $this->assertNull($this->event->getImageData());
        $this->assertSame($this->event, $this->event->setImage($image->reveal()));
        $this->assertSame($image->reveal(), $this->event->getImage());
        $this->assertSame(['id' => 42], $this->event->getImageData());
    }

    public function testTitle(): void
    {
        $this->assertNull($this->event->getTitle());
        $this->assertSame($this->event, $this->event->setTitle($this->testString));
        $this->assertSame($this->testString, $this->event->getTitle());

        $this->assertInstanceOf(EventTranslation::class, $this->event->getTranslations()['de']);
        $this->assertSame('de', $this->event->getTranslations()['de']->getLocale());
        $this->assertSame($this->testString, $this->event->getTranslations()['de']->getTitle());
    }

    public function testSubtitle(): void
    {
        $this->assertNull($this->event->getSubtitle());
        $this->assertSame($this->event, $this->event->setSubtitle($this->testString));
        $this->assertSame($this->testString, $this->event->getSubtitle());

        $this->assertInstanceOf(EventTranslation::class, $this->event->getTranslations()['de']);
        $this->assertSame('de', $this->event->getTranslations()['de']->getLocale());
        $this->assertSame($this->testString, $this->event->getTranslations()['de']->getSubtitle());
    }

    public function testSummary(): void
    {
        $this->assertNull($this->event->getSummary());
        $this->assertSame($this->event, $this->event->setSummary($this->testString));
        $this->assertSame($this->testString, $this->event->getSummary());

        $this->assertInstanceOf(EventTranslation::class, $this->event->getTranslations()['de']);
        $this->assertSame('de', $this->event->getTranslations()['de']->getLocale());
        $this->assertSame($this->testString, $this->event->getTranslations()['de']->getSummary());
    }

    public function testText(): void
    {
        $this->assertNull($this->event->getText());
        $this->assertSame($this->event, $this->event->setText($this->testString));
        $this->assertSame($this->testString, $this->event->getText());

        $this->assertInstanceOf(EventTranslation::class, $this->event->getTranslations()['de']);
        $this->assertSame('de', $this->event->getTranslations()['de']->getLocale());
        $this->assertSame($this->testString, $this->event->getTranslations()['de']->getText());
    }

    public function testFooter(): void
    {
        $this->assertNull($this->event->getFooter());
        $this->assertSame($this->event, $this->event->setFooter($this->testString));
        $this->assertSame($this->testString, $this->event->getFooter());

        $this->assertInstanceOf(EventTranslation::class, $this->event->getTranslations()['de']);
        $this->assertSame('de', $this->event->getTranslations()['de']->getLocale());
        $this->assertSame($this->testString, $this->event->getTranslations()['de']->getFooter());
    }

    public function testRoutePath(): void
    {
        $testRoutePath = 'events/event-100';

        $this->assertNull($this->event->getRoutePath());
        $this->assertSame($this->event, $this->event->setRoutePath($testRoutePath));
        $this->assertSame($testRoutePath, $this->event->getRoutePath());

        $this->assertInstanceOf(EventTranslation::class, $this->event->getTranslations()['de']);
        $this->assertSame('de', $this->event->getTranslations()['de']->getLocale());
        $this->assertSame($testRoutePath, $this->event->getTranslations()['de']->getRoutePath());
    }

    public function testLocale(): void
    {
        $this->assertSame('de', $this->event->getLocale());
        $this->assertSame($this->event, $this->event->setLocale('en'));
        $this->assertSame('en', $this->event->getLocale());
    }

    public function testEventSeo(): void
    {
        $eventSeo = $this->prophesize(EventSeo::class);
        $eventSeo->getId()->willReturn(42);

        $this->assertInstanceOf(EventSeo::class, $this->event->getEventSeo());
        $this->assertNull($this->event->getEventSeo()->getId());
        $this->assertSame($this->event, $this->event->setEventSeo($eventSeo->reveal()));
        $this->assertSame($eventSeo->reveal(), $this->event->getEventSeo());
    }

    public function testEventExcerpt(): void
    {
        $eventExcerpt = $this->prophesize(EventExcerpt::class);
        $eventExcerpt->getId()->willReturn(42);

        $this->assertInstanceOf(EventExcerpt::class, $this->event->getEventExcerpt());
        $this->assertNull($this->event->getEventExcerpt()->getId());
        $this->assertSame($this->event, $this->event->setEventExcerpt($eventExcerpt->reveal()));
        $this->assertSame($eventExcerpt->reveal(), $this->event->getEventExcerpt());
    }

    public function testExt(): void
    {
        $ext = $this->event->getExt();
        $this->assertArrayHasKey('seo', $ext);
        $this->assertInstanceOf(EventSeo::class, $ext['seo']);
        $this->assertNull($ext['seo']->getId());

        $this->assertArrayHasKey('excerpt', $ext);
        $this->assertInstanceOf(EventExcerpt::class, $ext['excerpt']);
        $this->assertNull($ext['excerpt']->getId());

        $this->event->addExt('foo', new EventSeo());
        $this->event->addExt('bar', new EventExcerpt());
        $ext = $this->event->getExt();

        $this->assertArrayHasKey('seo', $ext);
        $this->assertInstanceOf(EventSeo::class, $ext['seo']);
        $this->assertNull($ext['seo']->getId());

        $this->assertArrayHasKey('excerpt', $ext);
        $this->assertInstanceOf(EventExcerpt::class, $ext['excerpt']);
        $this->assertNull($ext['excerpt']->getId());

        $this->assertArrayHasKey('foo', $ext);
        $this->assertInstanceOf(EventSeo::class, $ext['foo']);
        $this->assertNull($ext['foo']->getId());

        $this->assertArrayHasKey('bar', $ext);
        $this->assertInstanceOf(EventExcerpt::class, $ext['bar']);
        $this->assertNull($ext['bar']->getId());

        $this->assertTrue($this->event->hasExt('seo'));
        $this->assertTrue($this->event->hasExt('excerpt'));
        $this->assertTrue($this->event->hasExt('foo'));
        $this->assertTrue($this->event->hasExt('bar'));

        $this->event->setExt(['and' => 'now', 'something' => 'special']);
        $ext = $this->event->getExt();
        $this->assertArrayNotHasKey('seo', $ext);
        $this->assertArrayNotHasKey('excerpt', $ext);
        $this->assertArrayNotHasKey('foo', $ext);
        $this->assertArrayNotHasKey('bar', $ext);
        $this->assertArrayHasKey('and', $ext);
        $this->assertArrayHasKey('something', $ext);
        $this->assertTrue($this->event->hasExt('and'));
        $this->assertTrue($this->event->hasExt('something'));
        $this->assertTrue('now' === $ext['and']);
        $this->assertTrue('special' === $ext['something']);
    }

    public function testPropagateLocale(): void
    {
        $this->assertSame($this->event->getExt()['seo']->getLocale(), 'de');
        $this->assertSame($this->event->getExt()['excerpt']->getLocale(), 'de');
        $this->event->setLocale('en');
        $this->assertSame($this->event->getExt()['seo']->getLocale(), 'en');
        $this->assertSame($this->event->getExt()['excerpt']->getLocale(), 'en');
    }

    public function testTranslations(): void
    {
        $this->assertSame($this->event->getTranslations(), []);
        $this->event->setText($this->testString);
        $this->assertNotSame($this->event->getTranslations(), []);
        $this->assertArrayHasKey('de', $this->event->getTranslations());
        $this->assertArrayNotHasKey('en', $this->event->getTranslations());
        $this->assertSame($this->event->getText(), $this->testString);

        $this->event->setLocale('en');
        $this->event->setText($this->testString);
        $this->assertArrayHasKey('de', $this->event->getTranslations());
        $this->assertArrayHasKey('en', $this->event->getTranslations());
        $this->assertSame($this->event->getText(), $this->testString);
        //No need to test more, it's already done...
    }
}
