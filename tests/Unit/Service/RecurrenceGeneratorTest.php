<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Service;

use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\EventRecurrence;
use Manuxi\SuluEventBundle\Service\RecurrenceGenerator;
use PHPUnit\Framework\TestCase;

class RecurrenceGeneratorTest extends TestCase
{
    private RecurrenceGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new RecurrenceGenerator();
    }

    public function testGenerateOccurrencesNotRecurring(): void
    {
        $recurrence = $this->createMock(EventRecurrence::class);
        $recurrence->method('getIsRecurring')->willReturn(false);

        $dimensionContent = $this->createMock(EventDimensionContent::class);

        $occurrences = $this->generator->generateOccurrences(
            $recurrence,
            $dimensionContent,
            new \DateTimeImmutable('2023-01-01'),
            new \DateTimeImmutable('2023-12-31')
        );

        $this->assertEmpty($occurrences);
    }

    public function testGenerateOccurrencesDaily(): void
    {
        $recurrence = new EventRecurrence($this->createMock(EventDimensionContent::class));
        $recurrence->setIsRecurring(true);
        $recurrence->setFrequency('daily');
        $recurrence->setInterval(1);

        $unlocalizedDimensionContent = $this->createMock(EventDimensionContent::class);
        $unlocalizedDimensionContent->method('getStartDate')->willReturn(new \DateTimeImmutable('2023-01-01 10:00:00'));

        $occurrences = $this->generator->generateOccurrences(
            $recurrence,
            $unlocalizedDimensionContent,
            new \DateTime('2023-01-01'),
            new \DateTime('2023-01-05 23:59:59')
        );

        $this->assertCount(5, $occurrences);
        $this->assertEquals('2023-01-01', $occurrences[0]->format('Y-m-d'));
        $this->assertEquals('2023-01-02', $occurrences[1]->format('Y-m-d'));
    }

    public function testGenerateOccurrencesWeeklyWithWeekdays(): void
    {
        $recurrence = new EventRecurrence($this->createMock(EventDimensionContent::class));
        $recurrence->setIsRecurring(true);
        $recurrence->setFrequency('weekly');
        $recurrence->setInterval(1);
        $recurrence->setByWeekday([1]); // Monday only, matching the start date
        // End after one week
        $recurrence->setEndType('until');
        $recurrence->setUntil(new \DateTime('2023-01-15'));

        // Start on a Monday
        $startDate = new \DateTimeImmutable('2023-01-02 10:00:00');
        $dimensionContent = $this->createMock(EventDimensionContent::class);
        $dimensionContent->method('getStartDate')->willReturn($startDate);

        $occurrences = $this->generator->generateOccurrences(
            $recurrence,
            $dimensionContent,
            new \DateTime('2023-01-02'),
            new \DateTime('2023-01-09 23:59:59')
        );

        // Should find Jan 2 and Jan 9 (if within range)
        // Range 2-9.
        // Jan 2 matches Mon.
        // Jan 9 matches Mon.
        $this->assertCount(2, $occurrences);
        $this->assertEquals('2023-01-02', $occurrences[0]->format('Y-m-d')); // Mon
        $this->assertEquals('2023-01-09', $occurrences[1]->format('Y-m-d')); // Mon
    }
}
