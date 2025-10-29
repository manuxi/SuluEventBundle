<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Website;

use Manuxi\SuluEventBundle\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;
class CalendarApiController extends AbstractController
{
    public function __construct(
        private EventRepository $eventRepository,
        private readonly RateLimiterFactory $calendarApiLimiter,
    ) {}

    /**
     * API endpoint for FullCalendar to fetch events
     */
    #[Route('/{_locale}/api/events/calendar', name: 'sulu_event.api.calendar', methods: ['GET'])]
    public function calendarAction(Request $request, string $_locale): JsonResponse
    {
        $limiter = $this->calendarApiLimiter->create($request->getClientIp());
        if (false === $limiter->consume(1)->isAccepted()) {
            return new JsonResponse(['error' => 'Too many requests'], 429);
        }

        $filters = $this->validateAndSanitizeFilters($request, $_locale);
        $events = $this->eventRepository->findForCalendar($filters);

        return new JsonResponse($this->transformEventsForFullCalendar($events, $_locale));
    }

    /**
     * Validate and sanitize request filters
     */
    private function validateAndSanitizeFilters(Request $request, string $locale): array
    {
        return [
            'locale' => $locale,
            'dataId' => $request->query->get('dataId') ? (int) $request->query->get('dataId') : null,
            'includeSubFolders' => $request->query->getBoolean('includeSubFolders', false),
            'categories' => array_filter($request->query->all('categories'), 'is_numeric'),
            'tags' => array_filter($request->query->all('tags'), 'is_numeric'),
            'location' => $request->query->get('location') ?
                filter_var($request->query->get('location'), FILTER_SANITIZE_STRING) : null,
            'sortBy' => in_array($request->query->get('sortBy'), ['startDate', 'title', 'created', 'changed']) ?
                $request->query->get('sortBy') : 'startDate',
            'sortMethod' => in_array(strtolower($request->query->get('sortMethod', 'asc')), ['asc', 'desc']) ?
                strtolower($request->query->get('sortMethod', 'asc')) : 'asc',
            'start' => $this->validateDate($request->query->get('start')),
            'end' => $this->validateDate($request->query->get('end')),
        ];
    }

    /**
     * Validate and sanitize date string
     */
    private function validateDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            // Try to parse the date to ensure it's valid
            $dateTime = new \DateTime($date);
            return $dateTime->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Transform events to FullCalendar format with locale-aware URLs
     */
    private function transformEventsForFullCalendar(array $events, string $locale): array
    {
        return array_map(function($event) use ($locale) {
            $data = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'start' => $event->getStartDate()->format('c'),
                'url' => $event->getRoutePath(), // Use actual route path from event
                'extendedProps' => [
                    'summary' => $event->getSummary(),
                ]
            ];

            // Add end date if exists
            if ($event->getEndDate()) {
                $data['end'] = $event->getEndDate()->format('c');
            }

            // Add location if exists
            if ($event->getLocation()) {
                $data['extendedProps']['location'] = $event->getLocation()->getName();
            }

            return $data;
        }, $events);
    }
}