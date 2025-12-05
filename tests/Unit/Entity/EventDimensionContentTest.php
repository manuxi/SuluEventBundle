<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Entity;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\EventRecurrence;
use Manuxi\SuluEventBundle\Entity\EventSocialSettings;
use Manuxi\SuluEventBundle\Entity\Location;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class EventDimensionContentTest extends TestCase
{
    use ProphecyTrait;

    private Event $event;
    private EventDimensionContent $dimensionContent;
    private string $testString = 'Lorem ipsum dolor sit amet';

    protected function setUp(): void
    {
        $this->event = new Event();
        $this->dimensionContent = new EventDimensionContent($this->event);
    }

    public function testGetEventReturnsEvent(): void
    {
        $this->assertSame($this->event, $this->dimensionContent->getEvent());
        $this->assertSame($this->event, $this->dimensionContent->getResource());
    }

    public function testLocaleGetterSetter(): void
    {
        $this->assertNull($this->dimensionContent->getLocale());
        $this->dimensionContent->setLocale('en');
        $this->assertSame('en', $this->dimensionContent->getLocale());
    }

    public function testStageGetterSetter(): void
    {
        $this->assertSame(DimensionContentInterface::STAGE_DRAFT, $this->dimensionContent->getStage());
        $this->dimensionContent->setStage(DimensionContentInterface::STAGE_LIVE);
        $this->assertSame(DimensionContentInterface::STAGE_LIVE, $this->dimensionContent->getStage());
    }

    public function testTitleGetterSetter(): void
    {
        $this->assertNull($this->dimensionContent->getTitle());
        $this->dimensionContent->setTitle($this->testString);
        $this->assertSame($this->testString, $this->dimensionContent->getTitle());
    }

    public function testSubtitleGetterSetter(): void
    {
        $this->assertNull($this->dimensionContent->getSubtitle());
        $this->dimensionContent->setSubtitle($this->testString);
        $this->assertSame($this->testString, $this->dimensionContent->getSubtitle());
    }

    public function testSummaryGetterSetter(): void
    {
        $this->assertNull($this->dimensionContent->getSummary());
        $this->dimensionContent->setSummary($this->testString);
        $this->assertSame($this->testString, $this->dimensionContent->getSummary());
    }

    public function testTextGetterSetter(): void
    {
        $this->assertNull($this->dimensionContent->getText());
        $this->dimensionContent->setText($this->testString);
        $this->assertSame($this->testString, $this->dimensionContent->getText());
    }

    public function testFooterGetterSetter(): void
    {
        $this->assertNull($this->dimensionContent->getFooter());
        $this->dimensionContent->setFooter($this->testString);
        $this->assertSame($this->testString, $this->dimensionContent->getFooter());
    }

    public function testStartDateGetterSetter(): void
    {
        $now = new \DateTimeImmutable();

        $this->assertNull($this->dimensionContent->getStartDate());
        $this->dimensionContent->setStartDate($now);
        $this->assertSame($now, $this->dimensionContent->getStartDate());
    }

    public function testEndDateGetterSetter(): void
    {
        $now = new \DateTimeImmutable();

        $this->assertNull($this->dimensionContent->getEndDate());
        $this->dimensionContent->setEndDate($now);
        $this->assertSame($now, $this->dimensionContent->getEndDate());
    }

    public function testLocationGetterSetter(): void
    {
        $location = $this->prophesize(Location::class);
        $location->getId()->willReturn(42);

        $this->assertNull($this->dimensionContent->getLocation());
        $this->dimensionContent->setLocation($location->reveal());
        $this->assertSame($location->reveal(), $this->dimensionContent->getLocation());
    }

    public function testTypeGetterSetter(): void
    {
        $this->assertSame('default', $this->dimensionContent->getType());
        $this->dimensionContent->setType('conference');
        $this->assertSame('conference', $this->dimensionContent->getType());
    }

    public function testEmailGetterSetter(): void
    {
        $this->assertNull($this->dimensionContent->getEmail());
        $this->dimensionContent->setEmail('test@example.com');
        $this->assertSame('test@example.com', $this->dimensionContent->getEmail());
    }

    public function testPhoneNumberGetterSetter(): void
    {
        $this->assertNull($this->dimensionContent->getPhoneNumber());
        $this->dimensionContent->setPhoneNumber('+1234567890');
        $this->assertSame('+1234567890', $this->dimensionContent->getPhoneNumber());
    }

    public function testImageGetterSetter(): void
    {
        $image = $this->prophesize(MediaInterface::class);
        $image->getId()->willReturn(42);

        $this->assertNull($this->dimensionContent->getImage());
        $this->dimensionContent->setImage($image->reveal());
        $this->assertSame($image->reveal(), $this->dimensionContent->getImage());
    }

    public function testImagesGetterSetter(): void
    {
        $imagesData = [
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
        ];

        $this->assertSame([], $this->dimensionContent->getImages());
        $this->dimensionContent->setImages($imagesData);
        $this->assertSame($imagesData, $this->dimensionContent->getImages());
    }

    public function testPdfGetterSetter(): void
    {
        $pdf = $this->prophesize(MediaInterface::class);
        $pdf->getId()->willReturn(99);

        $this->assertNull($this->dimensionContent->getPdf());
        $this->dimensionContent->setPdf($pdf->reveal());
        $this->assertSame($pdf->reveal(), $this->dimensionContent->getPdf());
    }

    public function testSpeakerGetterSetter(): void
    {
        $speaker = $this->prophesize(ContactInterface::class);
        $speaker->getId()->willReturn(123);

        $this->assertNull($this->dimensionContent->getSpeaker());
        $this->dimensionContent->setSpeaker($speaker->reveal());
        $this->assertSame($speaker->reveal(), $this->dimensionContent->getSpeaker());
    }

    public function testShowAuthorGetterSetter(): void
    {
        $this->assertFalse($this->dimensionContent->getShowAuthor());
        $this->dimensionContent->setShowAuthor(true);
        $this->assertTrue($this->dimensionContent->getShowAuthor());
        $this->dimensionContent->setShowAuthor(false);
        $this->assertFalse($this->dimensionContent->getShowAuthor());
    }

    public function testShowDateGetterSetter(): void
    {
        $this->assertFalse($this->dimensionContent->getShowDate());
        $this->dimensionContent->setShowDate(true);
        $this->assertTrue($this->dimensionContent->getShowDate());
        $this->dimensionContent->setShowDate(false);
        $this->assertFalse($this->dimensionContent->getShowDate());
    }

    public function testRecurrenceGetterSetter(): void
    {
        $recurrence = new EventRecurrence($this->dimensionContent);
        $recurrence->setFrequency('weekly');

        $this->assertNull($this->dimensionContent->getRecurrence());
        $this->dimensionContent->setRecurrence($recurrence);
        $this->assertSame($recurrence, $this->dimensionContent->getRecurrence());
    }

    public function testSocialSettingsGetterSetter(): void
    {
        $socialSettings = new EventSocialSettings($this->dimensionContent);
        $socialSettings->setTwitterShareText('Check out this event!');

        $this->assertNull($this->dimensionContent->getSocialSettings());
        $this->dimensionContent->setSocialSettings($socialSettings);
        $this->assertSame($socialSettings, $this->dimensionContent->getSocialSettings());
    }

    public function testGetTemplateType(): void
    {
        $this->assertSame('event', EventDimensionContent::getTemplateType());
        $this->assertSame(Event::TEMPLATE_TYPE, EventDimensionContent::getTemplateType());
    }

    public function testGetResourceKey(): void
    {
        $this->assertSame('events', EventDimensionContent::getResourceKey());
        $this->assertSame(Event::RESOURCE_KEY, EventDimensionContent::getResourceKey());
    }

    public function testSetTemplateDataSetsProperties(): void
    {
        $templateData = [
            'title' => 'Template Title',
            'subtitle' => 'Template Subtitle',
            'summary' => 'Template Summary',
            'text' => '<p>Template Text</p>',
            'footer' => 'Template Footer',
            'showAuthor' => true,
            'showDate' => false,
        ];

        $this->dimensionContent->setTemplateData($templateData);

        $this->assertSame('Template Title', $this->dimensionContent->getTitle());
        $this->assertSame('Template Subtitle', $this->dimensionContent->getSubtitle());
        $this->assertSame('Template Summary', $this->dimensionContent->getSummary());
        $this->assertSame('<p>Template Text</p>', $this->dimensionContent->getText());
        $this->assertSame('Template Footer', $this->dimensionContent->getFooter());
        $this->assertTrue($this->dimensionContent->getShowAuthor());
        $this->assertFalse($this->dimensionContent->getShowDate());
    }

    public function testSetTemplateDataHandlesInvalidTypes(): void
    {
        $templateData = [
            'title' => 123,  // Invalid: should be string
            'showAuthor' => 'yes',  // Invalid: should be bool
            'images' => 'not-an-array',  // Invalid: should be array
        ];

        $this->dimensionContent->setTemplateData($templateData);

        // Invalid values should be ignored (set to null)
        $this->assertNull($this->dimensionContent->getTitle());
        $this->assertNull($this->dimensionContent->getShowAuthor());
        $this->assertSame([], $this->dimensionContent->getImages());
    }

    public function testSetTemplateDataWithImages(): void
    {
        $imagesData = [
            ['id' => 1, 'title' => 'Image 1'],
            ['id' => 2, 'title' => 'Image 2'],
        ];

        $templateData = [
            'images' => $imagesData,
        ];

        $this->dimensionContent->setTemplateData($templateData);

        $this->assertSame($imagesData, $this->dimensionContent->getImages());
    }

    public function testSetTemplateDataWithSpeaker(): void
    {
        $speaker = $this->prophesize(ContactInterface::class);
        $speaker->getId()->willReturn(456);

        $templateData = [
            'speaker' => $speaker->reveal(),
        ];

        $this->dimensionContent->setTemplateData($templateData);

        $this->assertSame($speaker->reveal(), $this->dimensionContent->getSpeaker());
    }
}