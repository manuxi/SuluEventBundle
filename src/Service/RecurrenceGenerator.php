<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\EventRecurrence;

class RecurrenceGenerator
{
    /**
     * Generate occurrence dates based on recurrence rules.
     *
     * @param EventDimensionContent $unlocalizedDimensionContent Unlocalized dimension content containing startDate
     *
     * @return array<\DateTimeInterface>
     */
    public function generateOccurrences(
        EventRecurrence $recurrence,
        EventDimensionContent $unlocalizedDimensionContent,
        \DateTimeInterface $rangeStart,
        \DateTimeInterface $rangeEnd,
    ): array {
        if (!$recurrence->getIsRecurring()) {
            return [];
        }

        $startDate = $unlocalizedDimensionContent->getStartDate();
        if (!$startDate) {
            return [];
        }

        $occurrences = [];
        $currentDate = clone $startDate;

        // Ensure we start from range start or later
        if ($currentDate < $rangeStart) {
            $currentDate = clone $rangeStart;
        }

        $count = 0;
        $maxCount = $this->getMaxOccurrences($recurrence);
        $until = 'until' === $recurrence->getEndType() ? $recurrence->getUntil() : null;

        while ($count < $maxCount && $currentDate <= $rangeEnd) {
            // Check if we've passed the until date
            if ($until && $currentDate > $until) {
                break;
            }

            // Check if date matches weekday rules (for weekly recurrence)
            if ($this->matchesWeekdayRules($currentDate, $recurrence)) {
                $occurrences[] = clone $currentDate;
                ++$count;
            }

            // Move to next date based on frequency
            $currentDate = $this->incrementDate($currentDate, $recurrence);
        }

        return $occurrences;
    }

    /**
     * Get maximum number of occurrences to generate.
     */
    private function getMaxOccurrences(EventRecurrence $recurrence): int
    {
        if ('count' === $recurrence->getEndType() && $recurrence->getCount()) {
            return $recurrence->getCount();
        }

        // Default limit to prevent infinite loops
        return 500;
    }

    /**
     * Increment date based on recurrence frequency and interval.
     */
    private function incrementDate(\DateTimeInterface $date, EventRecurrence $recurrence): \DateTimeInterface
    {
        $newDate = clone $date;
        $interval = $recurrence->getInterval() ?? 1;

        return match ($recurrence->getFrequency()) {
            'daily' => $newDate->modify("+{$interval} day"),
            'weekly' => $newDate->modify("+{$interval} week"),
            'monthly' => $newDate->modify("+{$interval} month"),
            'yearly' => $newDate->modify("+{$interval} year"),
            default => $newDate,
        };
    }

    /**
     * Check if date matches weekday rules (for weekly recurrence).
     */
    private function matchesWeekdayRules(\DateTimeInterface $date, EventRecurrence $recurrence): bool
    {
        // If no weekday rules or not weekly frequency, match all dates
        if ('weekly' !== $recurrence->getFrequency()
            || null === $recurrence->getByWeekday()
            || empty($recurrence->getByWeekday())) {
            return true;
        }

        $dayOfWeek = (int) $date->format('N'); // 1=Monday, 7=Sunday

        return in_array($dayOfWeek, $recurrence->getByWeekday(), true);
    }
}
