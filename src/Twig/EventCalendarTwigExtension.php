<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Twig;

use Manuxi\SuluEventBundle\Repository\EventRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EventCalendarTwigExtension extends AbstractExtension
{
    public function __construct(
        private EventRepository $eventRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sulu_events_calendar', [$this, 'getEventsForCalendar']),
            new TwigFunction('sulu_events_by_month', [$this, 'getEventsByMonth']),
        ];
    }

    /**
     * Get events grouped by date for calendar view.
     */
    public function getEventsForCalendar(
        string $locale = 'en',
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
    ): array {
        $startDate = $startDate ?? new \DateTimeImmutable('first day of this month');
        $endDate = $endDate ?? new \DateTimeImmutable('last day of this month');

        $events = $this->eventRepository->findByDateRange($locale, $startDate, $endDate);

        return $this->groupEventsByDate($events);
    }

    /**
     * Get events for a specific month.
     */
    public function getEventsByMonth(int $year, int $month, string $locale = 'en'): array
    {
        $startDate = new \DateTimeImmutable("$year-$month-01");
        $endDate = new \DateTimeImmutable($startDate->format('Y-m-t'));

        return $this->getEventsForCalendar($locale, $startDate, $endDate);
    }

    /**
     * Group events by date for easier calendar rendering.
     */
    private function groupEventsByDate(array $events): array
    {
        $grouped = [];

        foreach ($events as $event) {
            $startDate = $event->getStartDate();
            if (!$startDate) {
                continue;
            }

            $dateKey = $startDate->format('Y-m-d');
            if (!isset($grouped[$dateKey])) {
                $grouped[$dateKey] = [];
            }

            $grouped[$dateKey][] = $event;
        }

        return $grouped;
    }
}
