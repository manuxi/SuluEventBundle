<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\EventSocialSettings;
use PHPUnit\Framework\TestCase;

class EventSocialSettingsTest extends TestCase
{
    private EventDimensionContent $eventDimensionContent;
    private EventSocialSettings $socialSettings;

    protected function setUp(): void
    {
        $event = new Event();
        $this->eventDimensionContent = new EventDimensionContent($event);
        $this->socialSettings = new EventSocialSettings($this->eventDimensionContent);
    }

    public function testConstruction(): void
    {
        $this->assertSame($this->eventDimensionContent, $this->socialSettings->getDimensionContent());
    }

    public function testTwitterShareText(): void
    {
        $this->assertNull($this->socialSettings->getTwitterShareText());
        $this->socialSettings->setTwitterShareText('Check out this event!');
        $this->assertEquals('Check out this event!', $this->socialSettings->getTwitterShareText());
    }

    public function testFacebookShareText(): void
    {
        $this->assertNull($this->socialSettings->getFacebookShareText());
        $this->socialSettings->setFacebookShareText('Join us at this amazing event!');
        $this->assertEquals('Join us at this amazing event!', $this->socialSettings->getFacebookShareText());
    }

    public function testLinkedInShareText(): void
    {
        $this->assertNull($this->socialSettings->getLinkedInShareText());
        $this->socialSettings->setLinkedInShareText('Professional event announcement');
        $this->assertEquals('Professional event announcement', $this->socialSettings->getLinkedInShareText());
    }

    public function testEmailShareSubject(): void
    {
        $this->assertNull($this->socialSettings->getEmailShareSubject());
        $this->socialSettings->setEmailShareSubject('Invitation to Event');
        $this->assertEquals('Invitation to Event', $this->socialSettings->getEmailShareSubject());
    }

    public function testEmailShareBody(): void
    {
        $this->assertNull($this->socialSettings->getEmailShareBody());
        $body = 'You are invited to attend our event...';
        $this->socialSettings->setEmailShareBody($body);
        $this->assertEquals($body, $this->socialSettings->getEmailShareBody());
    }
}
