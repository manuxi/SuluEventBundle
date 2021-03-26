<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventSeo;
use Manuxi\SuluEventBundle\Entity\EventTranslation;
use Manuxi\SuluEventBundle\Entity\Location;
use DateTimeImmutable;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class EventTest extends SuluTestCase
{
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    /**
     * @var Location|ObjectProphecy
     */
    private $location;

    private $event;

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
        $this->assertSame($this->event, $this->event->setTitle('Sulu is awesome'));
        $this->assertSame('Sulu is awesome', $this->event->getTitle());

        $this->assertInstanceOf(EventTranslation::class, $this->event->getTranslations()['de']);
        $this->assertSame('de', $this->event->getTranslations()['de']->getLocale());
        $this->assertSame('Sulu is awesome', $this->event->getTranslations()['de']->getTitle());
    }

    public function testTeaser(): void
    {
        $this->assertNull($this->event->getTeaser());
        $this->assertSame($this->event, $this->event->setTeaser('Sulu is awesome'));
        $this->assertSame('Sulu is awesome', $this->event->getTeaser());

        $this->assertInstanceOf(EventTranslation::class, $this->event->getTranslations()['de']);
        $this->assertSame('de', $this->event->getTranslations()['de']->getLocale());
        $this->assertSame('Sulu is awesome', $this->event->getTranslations()['de']->getTeaser());
    }

    public function testDescription(): void
    {
        $this->assertNull($this->event->getDescription());
        $this->assertSame($this->event, $this->event->setDescription('Sulu is awesome'));
        $this->assertSame('Sulu is awesome', $this->event->getDescription());

        $this->assertInstanceOf(EventTranslation::class, $this->event->getTranslations()['de']);
        $this->assertSame('de', $this->event->getTranslations()['de']->getLocale());
        $this->assertSame('Sulu is awesome', $this->event->getTranslations()['de']->getDescription());
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

    public function testExt(): void
    {
        $ext = $this->event->getExt();
        $this->assertArrayHasKey('seo', $ext);
        $this->assertInstanceOf(EventSeo::class, $ext['seo']);
        $this->assertNull($ext['seo']->getId());

        $this->event->addExt('key', new EventSeo());
        $ext = $this->event->getExt();

        $this->assertArrayHasKey('seo', $ext);
        $this->assertInstanceOf(EventSeo::class, $ext['seo']);
        $this->assertNull($ext['seo']->getId());

        $this->assertArrayHasKey('key', $ext);
        $this->assertInstanceOf(EventSeo::class, $ext['key']);
        $this->assertNull($ext['key']->getId());

        $this->assertTrue($this->event->hasExt('seo'));
        $this->assertTrue($this->event->hasExt('key'));

        $this->event->setExt(['and' => 'now', 'something' => 'special']);
        $ext = $this->event->getExt();
        $this->assertArrayNotHasKey('seo', $ext);
        $this->assertArrayNotHasKey('key', $ext);
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
        $this->event->setLocale('en');
        $this->assertSame($this->event->getExt()['seo']->getLocale(), 'en');
    }

    public function testTranslations(): void
    {
        $this->assertSame($this->event->getTranslations(), []);
        $this->event->setDescription('Sulu is awesome');
        $this->assertNotSame($this->event->getTranslations(), []);
        $this->assertArrayHasKey('de', $this->event->getTranslations());
        $this->assertArrayNotHasKey('en', $this->event->getTranslations());
        $this->event->setLocale('en');
        $this->event->setDescription('Sulu is awesome');
        $this->assertArrayHasKey('de', $this->event->getTranslations());
        $this->assertArrayHasKey('en', $this->event->getTranslations());
        //No need to test more, it's s already done...
    }
}
