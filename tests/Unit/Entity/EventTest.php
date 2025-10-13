<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventExcerpt;
use Manuxi\SuluEventBundle\Entity\EventSeo;
use Manuxi\SuluEventBundle\Entity\EventTranslation;
use Manuxi\SuluEventBundle\Entity\Location;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class EventTest extends SuluTestCase
{
    private ObjectProphecy $location;
    private Event $entity;
    private string $testString = 'Lorem ipsum dolor sit amet, ...';

    protected function setUp(): void
    {
        $this->entity = new Event();
        $this->entity->setLocale('de');
        $this->location = $this->prophesize(Location::class);
    }

    public function testPublished(): void
    {
        $this->assertNull($this->entity->isPublished());
        $this->assertSame($this->entity, $this->entity->setPublished(true));
        $this->assertTrue($this->entity->isPublished());
        $this->assertSame($this->entity, $this->entity->setPublished(false));
        $this->assertFalse($this->entity->isPublished());
    }

    public function testPublishedState(): void
    {
        $this->assertNull($this->entity->getPublishedState());
        $this->assertSame($this->entity, $this->entity->setPublished(true));
        $this->assertEquals(1, $this->entity->getPublishedState());
        $this->assertSame($this->entity, $this->entity->setPublished(false));
        $this->assertEquals(0, $this->entity->getPublishedState());
    }

    public function testStartDate(): void
    {
        $now = new \DateTimeImmutable();

        $this->assertNull($this->entity->getStartDate());
        $this->assertSame($this->entity, $this->entity->setStartDate($now));
        $this->assertNotNull($this->entity->getStartDate());
        $this->assertSame($now, $this->entity->getStartDate());
    }

    public function testEndDate(): void
    {
        $now = new \DateTimeImmutable();

        $this->assertNull($this->entity->getEndDate());
        $this->assertSame($this->entity, $this->entity->setEndDate($now));
        $this->assertNotNull($this->entity->getEndDate());
        $this->assertSame($now, $this->entity->getEndDate());
    }

    public function testLocation(): void
    {
        $this->location->getId()->willReturn(42);

        $this->assertNull($this->entity->getLocation());
        $this->assertNull($this->entity->getLocationId());
        $this->assertSame($this->entity, $this->entity->setLocation($this->location->reveal()));
        $this->assertNotNull($this->entity->getLocation());
        $this->assertSame($this->location->reveal(), $this->entity->getLocation());
        $this->assertSame(42, $this->entity->getLocationId());
    }

    public function testImage(): void
    {
        $image = $this->prophesize(MediaInterface::class);
        $image->getId()->willReturn(42);

        $this->assertNull($this->entity->getImage());
        $this->assertNull($this->entity->getImageData());
        $this->assertSame($this->entity, $this->entity->setImage($image->reveal()));
        $this->assertSame($image->reveal(), $this->entity->getImage());
        $this->assertSame(['id' => 42], $this->entity->getImageData());
    }

    public function testTitle(): void
    {
        $this->assertNull($this->entity->getTitle());
        $this->assertSame($this->entity, $this->entity->setTitle($this->testString));
        $this->assertSame($this->testString, $this->entity->getTitle());

        $this->assertInstanceOf(EventTranslation::class, $this->entity->getTranslations()['de']);
        $this->assertSame('de', $this->entity->getTranslations()['de']->getLocale());
        $this->assertSame($this->testString, $this->entity->getTranslations()['de']->getTitle());
    }

    public function testSubtitle(): void
    {
        $this->assertNull($this->entity->getSubtitle());
        $this->assertSame($this->entity, $this->entity->setSubtitle($this->testString));
        $this->assertSame($this->testString, $this->entity->getSubtitle());

        $this->assertInstanceOf(EventTranslation::class, $this->entity->getTranslations()['de']);
        $this->assertSame('de', $this->entity->getTranslations()['de']->getLocale());
        $this->assertSame($this->testString, $this->entity->getTranslations()['de']->getSubtitle());
    }

    public function testSummary(): void
    {
        $this->assertNull($this->entity->getSummary());
        $this->assertSame($this->entity, $this->entity->setSummary($this->testString));
        $this->assertSame($this->testString, $this->entity->getSummary());

        $this->assertInstanceOf(EventTranslation::class, $this->entity->getTranslations()['de']);
        $this->assertSame('de', $this->entity->getTranslations()['de']->getLocale());
        $this->assertSame($this->testString, $this->entity->getTranslations()['de']->getSummary());
    }

    public function testText(): void
    {
        $this->assertNull($this->entity->getText());
        $this->assertSame($this->entity, $this->entity->setText($this->testString));
        $this->assertSame($this->testString, $this->entity->getText());

        $this->assertInstanceOf(EventTranslation::class, $this->entity->getTranslations()['de']);
        $this->assertSame('de', $this->entity->getTranslations()['de']->getLocale());
        $this->assertSame($this->testString, $this->entity->getTranslations()['de']->getText());
    }

    public function testFooter(): void
    {
        $this->assertNull($this->entity->getFooter());
        $this->assertSame($this->entity, $this->entity->setFooter($this->testString));
        $this->assertSame($this->testString, $this->entity->getFooter());

        $this->assertInstanceOf(EventTranslation::class, $this->entity->getTranslations()['de']);
        $this->assertSame('de', $this->entity->getTranslations()['de']->getLocale());
        $this->assertSame($this->testString, $this->entity->getTranslations()['de']->getFooter());
    }

    public function testRoutePath(): void
    {
        $testRoutePath = 'events/event-100';

        $this->assertNull($this->entity->getRoutePath());
        $this->assertSame($this->entity, $this->entity->setRoutePath($testRoutePath));
        $this->assertSame($testRoutePath, $this->entity->getRoutePath());

        $this->assertInstanceOf(EventTranslation::class, $this->entity->getTranslations()['de']);
        $this->assertSame('de', $this->entity->getTranslations()['de']->getLocale());
        $this->assertSame($testRoutePath, $this->entity->getTranslations()['de']->getRoutePath());
    }

    public function testLocale(): void
    {
        $this->assertSame('de', $this->entity->getLocale());
        $this->assertSame($this->entity, $this->entity->setLocale('en'));
        $this->assertSame('en', $this->entity->getLocale());
    }

    public function testEventSeo(): void
    {
        $eventSeo = $this->prophesize(EventSeo::class);
        $eventSeo->getId()->willReturn(42);

        $this->assertInstanceOf(EventSeo::class, $this->entity->getEventSeo());
        $this->assertNull($this->entity->getEventSeo()->getId());
        $this->assertSame($this->entity, $this->entity->setEventSeo($eventSeo->reveal()));
        $this->assertSame($eventSeo->reveal(), $this->entity->getEventSeo());
    }

    public function testEventExcerpt(): void
    {
        $eventExcerpt = $this->prophesize(EventExcerpt::class);
        $eventExcerpt->getId()->willReturn(42);

        $this->assertInstanceOf(EventExcerpt::class, $this->entity->getEventExcerpt());
        $this->assertNull($this->entity->getEventExcerpt()->getId());
        $this->assertSame($this->entity, $this->entity->setEventExcerpt($eventExcerpt->reveal()));
        $this->assertSame($eventExcerpt->reveal(), $this->entity->getEventExcerpt());
    }

    public function testExt(): void
    {
        $ext = $this->entity->getExt();
        $this->assertArrayHasKey('seo', $ext);
        $this->assertInstanceOf(EventSeo::class, $ext['seo']);
        $this->assertNull($ext['seo']->getId());

        $this->assertArrayHasKey('excerpt', $ext);
        $this->assertInstanceOf(EventExcerpt::class, $ext['excerpt']);
        $this->assertNull($ext['excerpt']->getId());

        $this->entity->addExt('foo', new EventSeo());
        $this->entity->addExt('bar', new EventExcerpt());
        $ext = $this->entity->getExt();

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

        $this->assertTrue($this->entity->hasExt('seo'));
        $this->assertTrue($this->entity->hasExt('excerpt'));
        $this->assertTrue($this->entity->hasExt('foo'));
        $this->assertTrue($this->entity->hasExt('bar'));

        $this->entity->setExt(['and' => 'now', 'something' => 'special']);
        $ext = $this->entity->getExt();
        $this->assertArrayNotHasKey('seo', $ext);
        $this->assertArrayNotHasKey('excerpt', $ext);
        $this->assertArrayNotHasKey('foo', $ext);
        $this->assertArrayNotHasKey('bar', $ext);
        $this->assertArrayHasKey('and', $ext);
        $this->assertArrayHasKey('something', $ext);
        $this->assertTrue($this->entity->hasExt('and'));
        $this->assertTrue($this->entity->hasExt('something'));
        $this->assertTrue('now' === $ext['and']);
        $this->assertTrue('special' === $ext['something']);
    }

    public function testPropagateLocale(): void
    {
        $this->assertSame($this->entity->getExt()['seo']->getLocale(), 'de');
        $this->assertSame($this->entity->getExt()['excerpt']->getLocale(), 'de');
        $this->entity->setLocale('en');
        $this->assertSame($this->entity->getExt()['seo']->getLocale(), 'en');
        $this->assertSame($this->entity->getExt()['excerpt']->getLocale(), 'en');
    }

    public function testTranslations(): void
    {
        $this->assertSame($this->entity->getTranslations(), []);
        $this->entity->setText($this->testString);
        $this->assertNotSame($this->entity->getTranslations(), []);
        $this->assertArrayHasKey('de', $this->entity->getTranslations());
        $this->assertArrayNotHasKey('en', $this->entity->getTranslations());
        $this->assertSame($this->entity->getText(), $this->testString);

        $this->entity->setLocale('en');
        $this->entity->setText($this->testString);
        $this->assertArrayHasKey('de', $this->entity->getTranslations());
        $this->assertArrayHasKey('en', $this->entity->getTranslations());
        $this->assertSame($this->entity->getText(), $this->testString);
        // No need to test more, it's already done...
    }
}
