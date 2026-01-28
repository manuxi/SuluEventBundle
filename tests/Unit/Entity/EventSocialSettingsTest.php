<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventSocialSettings;
use PHPUnit\Framework\TestCase;

class EventSocialSettingsTest extends TestCase
{
    private Event $event;
    private EventSocialSettings $socialSettings;

    protected function setUp(): void
    {
        $this->event = $this->createMock(Event::class);
        $this->socialSettings = new EventSocialSettings($this->event);
    }

    public function testConstruction(): void
    {
        $this->assertSame($this->event, $this->socialSettings->getEvent());
    }

    public function testEnableSharing(): void
    {
        $this->assertFalse($this->socialSettings->isEnableSharing());
        $this->socialSettings->setEnableSharing(true);
        $this->assertTrue($this->socialSettings->isEnableSharing());
    }

    public function testPlatforms(): void
    {
        $this->assertNull($this->socialSettings->getPlatforms());
        $platforms = ['facebook', 'twitter'];
        $this->socialSettings->setPlatforms($platforms);
        $this->assertEquals($platforms, $this->socialSettings->getPlatforms());
    }

    public function testFacebookUrl(): void
    {
        $this->assertNull($this->socialSettings->getFacebookUrl());
        $this->socialSettings->setFacebookUrl('https://facebook.com/event');
        $this->assertEquals('https://facebook.com/event', $this->socialSettings->getFacebookUrl());
    }

    public function testTwitterHandle(): void
    {
        $this->assertNull($this->socialSettings->getTwitterHandle());
        $this->socialSettings->setTwitterHandle('@myevent');
        $this->assertEquals('@myevent', $this->socialSettings->getTwitterHandle());
    }

    public function testInstagramUrl(): void
    {
        $this->assertNull($this->socialSettings->getInstagramUrl());
        $this->socialSettings->setInstagramUrl('https://instagram.com/myevent');
        $this->assertEquals('https://instagram.com/myevent', $this->socialSettings->getInstagramUrl());
    }

    public function testLinkedinUrl(): void
    {
        $this->assertNull($this->socialSettings->getLinkedinUrl());
        $this->socialSettings->setLinkedinUrl('https://linkedin.com/myevent');
        $this->assertEquals('https://linkedin.com/myevent', $this->socialSettings->getLinkedinUrl());
    }

    public function testCustomShareText(): void
    {
        $this->assertNull($this->socialSettings->getCustomShareText());
        $this->socialSettings->setCustomShareText('Check out this event!');
        $this->assertEquals('Check out this event!', $this->socialSettings->getCustomShareText());
    }

    public function testTargetGroups(): void
    {
        $this->assertNull($this->socialSettings->getTargetGroups());
        $this->socialSettings->setTargetGroups('professionals,students');
        $this->assertEquals('professionals,students', $this->socialSettings->getTargetGroups());
    }
}
