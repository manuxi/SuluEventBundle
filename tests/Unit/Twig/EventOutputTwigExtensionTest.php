<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Twig;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Service\EventFilterOptions;
use Manuxi\SuluEventBundle\Service\EventSchemaBuilder;
use Manuxi\SuluEventBundle\Service\EventTimezoneResolver;
use Manuxi\SuluEventBundle\Twig\EventOutputTwigExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EventOutputTwigExtensionTest extends TestCase
{
    private EventFilterOptions&MockObject $filterOptions;
    private EventSchemaBuilder&MockObject $schemaBuilder;
    private EventOutputTwigExtension $extension;

    protected function setUp(): void
    {
        $this->filterOptions = $this->createMock(EventFilterOptions::class);
        $this->schemaBuilder = $this->createMock(EventSchemaBuilder::class);
        $this->extension     = new EventOutputTwigExtension($this->filterOptions, $this->schemaBuilder, new EventTimezoneResolver('Europe/Berlin'));
    }

    public function testFunctionsAreRegistered(): void
    {
        $names = array_map(static fn ($function): string => $function->getName(), $this->extension->getFunctions());

        $this->assertSame(['sulu_event_filter_options', 'sulu_event_schema', 'sulu_event_datetime', 'sulu_event_timezone'], $names);
    }

    public function testFilterOptionsAreThoseOfTheLocale(): void
    {
        $this->filterOptions->expects($this->once())->method('getOptions')->with('de')->willReturn(['types' => []]);

        $this->assertSame(['types' => []], $this->extension->getFilterOptions('de'));
    }

    public function testSchemaIsBuiltForEventsOnly(): void
    {
        $content = $this->createContent();
        $this->schemaBuilder->expects($this->once())->method('build')->with($content)->willReturn(['@type' => 'Event']);

        $this->assertSame(['@type' => 'Event'], $this->extension->getSchema($content));
        $this->assertSame([], $this->extension->getSchema(['title' => 'array']));
        $this->assertSame([], $this->extension->getSchema(null));
    }

    public function testDateTimeIsInTheZoneOfTheEvent(): void
    {
        $content = $this->createContent();
        $content->setTimezone('America/Los_Angeles');

        $start = $this->extension->getDateTime($content, 'start');
        $end   = $this->extension->getDateTime($content, 'end');

        $this->assertSame('2027-07-01T10:00:00-07:00', $start->format('c'));
        $this->assertSame('2027-07-01T12:00:00-07:00', $end->format('c'));
        $this->assertSame('America/Los_Angeles', $this->extension->getTimezone($content)->getName());
    }

    public function testDateTimeIsNullWithoutADateOrAnEvent(): void
    {
        $content = $this->createContent();
        $content->setEndDate(null);

        $this->assertNull($this->extension->getDateTime($content, 'end'));
        $this->assertNull($this->extension->getDateTime(null));
        $this->assertNull($this->extension->getTimezone('nope'));
    }

    private function createContent(): EventDimensionContent
    {
        $content = new EventDimensionContent(new Event());
        $content->setStartDate(new \DateTimeImmutable('2027-07-01 10:00:00'));
        $content->setEndDate(new \DateTimeImmutable('2027-07-01 12:00:00'));

        return $content;
    }
}
