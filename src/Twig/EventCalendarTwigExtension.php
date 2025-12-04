<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Twig;

use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EventCalendarTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly ContentAggregatorInterface $contentAggregator,
        private readonly ContentResolverInterface $contentResolver,
        private readonly RequestAnalyzerInterface $requestAnalyzer,
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
     *
     * @param array<string, string> $properties
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getEventsForCalendar(
        ?string $locale = null,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
        array $properties = []
    ): array {
        if (null === $locale) {
            $localization = $this->requestAnalyzer->getCurrentLocalization();
            if (null === $localization) {
                return [];
            }
            $locale = $localization->getLocale();
        }

        $startDate = $startDate ?? new \DateTimeImmutable('first day of this month');
        $endDate = $endDate ?? new \DateTimeImmutable('last day of this month');

        $events = $this->eventRepository->findByDateRange($locale, $startDate, $endDate);

        return $this->groupEventsByDate($events, $locale, $properties);
    }

    /**
     * Get events for a specific month.
     *
     * @param array<string, string> $properties
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getEventsByMonth(
        int $year,
        int $month,
        ?string $locale = null,
        array $properties = []
    ): array {
        $startDate = new \DateTimeImmutable("$year-$month-01");
        $endDate = new \DateTimeImmutable($startDate->format('Y-m-t'));

        return $this->getEventsForCalendar($locale, $startDate, $endDate, $properties);
    }

    /**
     * Group events by date for easier calendar rendering.
     *
     * @param array<int, \Manuxi\SuluEventBundle\Entity\Event> $events
     * @param array<string, string> $properties
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function groupEventsByDate(array $events, string $locale, array $properties): array
    {
        $grouped = [];

        foreach ($events as $event) {
            $startDate = $event->getStartDate();
            if (!$startDate) {
                continue;
            }

            /** @var EventDimensionContent $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate(
                $event,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_LIVE,
                    'version' => DimensionContentInterface::CURRENT_VERSION,
                ]
            );

            $resolvedEvent = $this->contentResolver->resolve($dimensionContent, $properties);

            $dateKey = $startDate->format('Y-m-d');
            if (!isset($grouped[$dateKey])) {
                $grouped[$dateKey] = [];
            }

            $grouped[$dateKey][] = $resolvedEvent;
        }

        return $grouped;
    }
}