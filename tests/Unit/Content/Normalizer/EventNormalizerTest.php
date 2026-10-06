<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Content\Normalizer;

use Manuxi\SuluEventBundle\Content\Normalizer\EventNormalizer;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Service\EventTypeSelect;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\MediaBundle\Api\Media as ApiMedia;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventNormalizerTest extends TestCase
{
    private $translator;
    private $eventTypeSelect;
    private $mediaManager;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')->willReturn('Y-m-d');

        $this->eventTypeSelect = $this->createMock(EventTypeSelect::class);
        $this->eventTypeSelect->method('getTypeName')->willReturn('Test Type');

        $this->mediaManager = $this->createMock(MediaManagerInterface::class);
    }

    public function testEnhanceLocationId(): void
    {
        $normalizer = new EventNormalizer($this->translator, $this->eventTypeSelect, $this->mediaManager);
        $event = $this->createMock(Event::class);
        $event->method('getUuid')->willReturn('019bf796-423c-7e1f-969c-5c4ece5e9b73');

        $location = $this->createMock(Location::class);
        $location->method('getId')->willReturn(456);

        $dimensionContent = $this->createMock(EventDimensionContent::class);
        $dimensionContent->method('getResource')->willReturn($event);
        $dimensionContent->method('getLocation')->willReturn($location);

        $normalizedData = [];
        $result = $normalizer->enhance($dimensionContent, $normalizedData);

        $this->assertEquals('019bf796-423c-7e1f-969c-5c4ece5e9b73', $result['id']);
        $this->assertEquals(456, $result['locationId']);
        $this->assertEquals(456, $result['location']['id']);
    }

    public function testEnhanceNoLocation(): void
    {
        $normalizer = new EventNormalizer($this->translator, $this->eventTypeSelect, $this->mediaManager);
        $event = $this->createMock(Event::class);
        $event->method('getUuid')->willReturn('019bf796-423c-7e1f-969c-5c4ece5e9b73');

        $dimensionContent = $this->createMock(EventDimensionContent::class);
        $dimensionContent->method('getResource')->willReturn($event);
        $dimensionContent->method('getLocation')->willReturn(null);

        $normalizedData = [];
        $result = $normalizer->enhance($dimensionContent, $normalizedData);

        $this->assertEquals('019bf796-423c-7e1f-969c-5c4ece5e9b73', $result['id']);
        $this->assertNull($result['locationId']);
        $this->assertNull($result['location']);
    }

    public function testEnhanceResolvesFullImageObject(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getUuid')->willReturn('019bf796-423c-7e1f-969c-5c4ece5e9b73');

        $image = $this->createMock(MediaInterface::class);
        $image->method('getId')->willReturn(7);

        $apiImage = $this->createMock(ApiMedia::class);
        $apiImage->method('getId')->willReturn(7);
        $apiImage->method('getUrl')->willReturn('/uploads/media/original/image.jpg');
        $apiImage->method('getFormats')->willReturn(['sulu-100x100' => '/uploads/media/sulu-100x100/image.jpg']);
        $apiImage->method('getMimeType')->willReturn('image/jpeg');

        $this->mediaManager->expects($this->once())->method('getById')->with(7, 'de')->willReturn($apiImage);

        $dimensionContent = $this->createMock(EventDimensionContent::class);
        $dimensionContent->method('getResource')->willReturn($event);
        $dimensionContent->method('getLocale')->willReturn('de');
        $dimensionContent->method('getImage')->willReturn($image);

        $normalizer = new EventNormalizer($this->translator, $this->eventTypeSelect, $this->mediaManager);
        $result = $normalizer->enhance($dimensionContent, []);

        $this->assertSame([
            'id' => 7,
            'url' => '/uploads/media/original/image.jpg',
            'thumbnails' => ['sulu-100x100' => '/uploads/media/sulu-100x100/image.jpg'],
            'mimeType' => 'image/jpeg',
        ], $result['image']);
    }
}