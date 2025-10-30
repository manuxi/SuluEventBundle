<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Website;

use Manuxi\SuluEventBundle\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Routing\Annotation\Route;

class CalendarApiController extends AbstractController
{
    public function __construct(
        private EventRepository $eventRepository,
    ) {
    }

    /**
     * API endpoint for FullCalendar to fetch events.
     *
     * Returns events in FullCalendar-compatible JSON format
     * Supports filtering by date range, categories, tags, and location
     * Rate limited to 100 requests per hour per IP
     */
    #[Route(
        path: '/{_locale}/api/events/calendar',
        name: 'sulu_event.api.calendar',
        defaults: ['_locale' => 'en'],
        methods: ['GET']
    )]
    public function calendarAction(Request $request, string $_locale): JsonResponse
    {
        // Rate limiting via limiter service
        $this->applyRateLimit($request);

        $filters = $this->validateAndSanitizeFilters($request, $_locale);
        $events = $this->eventRepository->findForCalendar($filters);

        return new JsonResponse($this->transformEventsForFullCalendar($events, $_locale));
    }

    /**
     * Apply rate limiting using the limiter service.
     */
    private function applyRateLimit(Request $request): void
    {
        if (!$this->container->has('limiter')) {
            return;
        }

        try {
            $limiter = $this->container->get('limiter');
            $limit = $limiter->create('calendar_api', $request->getClientIp());

            if (false === $limit->consume(1)->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }
        } catch (\Exception $e) {
            // Rate limiter not configured - continue without limiting
        }
    }

    /**
     * Validate and sanitize request filters.
     */
    private function validateAndSanitizeFilters(Request $request, string $locale): array
    {
        $filters = [
            'locale' => $locale,
            'dataId' => $request->query->get('dataId', null),
            'includeSubFolders' => $request->query->getBoolean('includeSubFolders', false),
            'categories' => array_filter($request->query->all('categories') ?? [], 'is_numeric'),
            'tags' => array_filter($request->query->all('tags') ?? [], 'is_numeric'),
            'location' => $request->query->get('location') ?
                filter_var($request->query->get('location'), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null,
            'sortBy' => in_array($request->query->get('sortBy'), ['startDate', 'title', 'created', 'changed']) ?
                $request->query->get('sortBy') : 'startDate',
            'sortMethod' => in_array(strtolower($request->query->get('sortMethod', 'asc')), ['asc', 'desc']) ?
                strtolower($request->query->get('sortMethod', 'asc')) : 'asc',
            'start' => $this->validateDate($request->query->get('start')),
            'end' => $this->validateDate($request->query->get('end')),
        ];

        // dataId = 0 or null means: All events (no page filter)
        if (empty($filters['dataId'])) {
            unset($filters['dataId']);
            unset($filters['includeSubFolders']);  // Only makes sense with dataId
        } else {
            $filters['dataId'] = (int) $filters['dataId'];
        }

        return $filters;
    }

    /**
     * Validate and sanitize date string.
     */
    private function validateDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            $dateTime = new \DateTime($date);

            return $dateTime->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Transform events to FullCalendar format.
     */
    private function transformEventsForFullCalendar(array $events, string $locale): array
    {
        return array_map(function ($event) {
            $data = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'start' => $event->getStartDate()->format('c'),
                'url' => $event->getRoutePath(),
                'extendedProps' => [
                    'summary' => $event->getSummary(),
                ],
            ];

            if ($event->getEndDate()) {
                $data['end'] = $event->getEndDate()->format('c');
            }

            if ($event->getLocation()) {
                $data['extendedProps']['location'] = $event->getLocation()->getName();
            }

            return $data;
        }, $events);
    }
}
