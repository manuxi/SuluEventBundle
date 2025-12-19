<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Service;

use Manuxi\SuluEventBundle\Service\EventTypeSelect;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventTypeSelectTest extends TestCase
{
    private EventTypeSelect $eventTypeSelect;
    private TranslatorInterface $translator;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);

        $types = [
            'default' => [
                'name' => 'sulu_event.default',
                'color' => '#0d6efd',
            ],
            'workshop' => [
                'name' => 'sulu_event.workshop',
                'color' => '#ffc107',
            ],
            'concert' => [
                'name' => 'sulu_event.concert',
            ],
        ];

        $this->eventTypeSelect = new EventTypeSelect($this->translator, $types, 'default');
    }

    public function testGetValues(): void
    {
        $this->translator->expects($this->exactly(3))
            ->method('trans')
            ->willReturnCallback(fn($key) => match ($key) {
                'sulu_event.default' => 'Default',
                'sulu_event.workshop' => 'Workshop',
                'sulu_event.concert' => 'Concert',
                default => $key,
            });

        $values = $this->eventTypeSelect->getValues();

        $this->assertCount(3, $values);
        $this->assertEquals('default', $values[0]['name']);
        $this->assertEquals('Default', $values[0]['title']);
    }

    public function testGetDefaultValue(): void
    {
        $this->assertEquals('default', $this->eventTypeSelect->getDefaultValue());
    }

    public function testGetColor(): void
    {
        $this->assertEquals('#0d6efd', $this->eventTypeSelect->getColor('default'));
        $this->assertEquals('#ffc107', $this->eventTypeSelect->getColor('workshop'));

        // Fallback to default
        $this->assertEquals('#0d6efd', $this->eventTypeSelect->getColor('concert'));

        // Non-existent type
        $this->assertEquals('#0d6efd', $this->eventTypeSelect->getColor('unknown'));
    }

    public function testGetTypeName(): void
    {
        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturnMap([
                ['sulu_event.default', [], 'admin', null, 'Default'],
                ['sulu_event.workshop', [], 'admin', null, 'Workshop'],
            ]);

        $this->assertEquals('Workshop', $this->eventTypeSelect->getTypeName('workshop'));

        // Fallback to default
        $this->assertEquals('Default', $this->eventTypeSelect->getTypeName('unknown'));
    }

    public function testHasType(): void
    {
        $this->assertTrue($this->eventTypeSelect->hasType('workshop'));
        $this->assertFalse($this->eventTypeSelect->hasType('unknown'));
    }
}