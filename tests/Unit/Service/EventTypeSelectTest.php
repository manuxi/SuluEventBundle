<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Service;

use Manuxi\SuluEventBundle\Service\EventTypeSelect;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventTypeSelectTest extends TestCase
{
    private TranslatorInterface $translator;
    private EventTypeSelect $typeSelect;
    private array $testTypes;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator
            ->method('trans')
            ->willReturnCallback(fn($key) => 'translated_' . $key);

        $this->testTypes = [
            'default' => [
                'name' => 'Default Event',
                'color' => '#cccccc',
            ],
            'workshop' => [
                'name' => 'Workshop',
                'color' => '#3498db',
            ],
            'conference' => [
                'name' => 'Conference',
                'color' => '#e74c3c',
            ],
        ];

        $this->typeSelect = new EventTypeSelect(
            $this->translator,
            $this->testTypes,
            'default'
        );
    }

    public function testGetValues(): void
    {
        $values = $this->typeSelect->getValues();

        $this->assertIsArray($values);
        $this->assertCount(3, $values);

        $this->assertEquals([
            ['name' => 'default', 'title' => 'translated_Default Event'],
            ['name' => 'workshop', 'title' => 'translated_Workshop'],
            ['name' => 'conference', 'title' => 'translated_Conference'],
        ], $values);
    }

    public function testGetDefaultValue(): void
    {
        $defaultValue = $this->typeSelect->getDefaultValue();
        $this->assertEquals('default', $defaultValue);
    }

    public function testGetColor(): void
    {
        $this->assertEquals('#cccccc', $this->typeSelect->getColor('default'));
        $this->assertEquals('#3498db', $this->typeSelect->getColor('workshop'));
        $this->assertEquals('#e74c3c', $this->typeSelect->getColor('conference'));
    }

    public function testGetColorFallback(): void
    {
        $color = $this->typeSelect->getColor('nonexistent');
        $this->assertEquals('#cccccc', $color);
    }

    public function testGetTypes(): void
    {
        $types = $this->typeSelect->getTypes();
        $this->assertEquals($this->testTypes, $types);
    }

    public function testGetTypeName(): void
    {
        $name = $this->typeSelect->getTypeName('workshop');
        $this->assertEquals('translated_Workshop', $name);
    }

    public function testGetTypeNameFallback(): void
    {
        $name = $this->typeSelect->getTypeName('nonexistent');
        $this->assertEquals('translated_Default Event', $name);
    }

    public function testHasType(): void
    {
        $this->assertTrue($this->typeSelect->hasType('default'));
        $this->assertTrue($this->typeSelect->hasType('workshop'));
        $this->assertFalse($this->typeSelect->hasType('nonexistent'));
    }
}