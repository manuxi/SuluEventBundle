<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Content\Normalizer;

use Manuxi\SuluEventBundle\Content\Normalizer\EventNormalizer;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Service\EventTypeSelect;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventNormalizerTest extends TestCase
{
    private $translator;
    private $eventTypeSelect;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')->willReturn('Y-m-d');

        $this->eventTypeSelect = $this->createMock(EventTypeSelect::class);
        $this->eventTypeSelect->method('getTypeName')->willReturn('Test Type');
    }

    public function testEnhanceLocationId(): void
    {
        $normalizer = new EventNormalizer($this->translator, $this->eventTypeSelect);
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
        $normalizer = new EventNormalizer($this->translator, $this->eventTypeSelect);
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
}