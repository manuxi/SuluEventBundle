<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Website;

use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Service\EventTimezoneResolver;
use Manuxi\SuluEventBundle\Service\EventTypeSelect;
use Manuxi\SuluEventBundle\Service\EventUrlResolver;
use Manuxi\SuluEventBundle\Service\RequestParameters;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Routing\Attribute\Route;

class CalendarApiController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly EventTypeSelect $eventTypeSelect,
        private readonly ContentAggregatorInterface $contentAggregator,
        private readonly EventUrlResolver $urlResolver,
        private readonly EventTimezoneResolver $timezoneResolver,
    ) {
    }

    #[Route(
        path: '/api/events/calendar/{_locale}',
        name: 'sulu_event.api.calendar',
        defaults: ['_locale' => 'en'],
        methods: ['GET']
    )]
    public function calendarAction(Request $request, string $_locale): JsonResponse
    {
        $this->applyRateLimit($request);

        $filters = $this->validateAndSanitizeFilters($request, $_locale);
        $events = $this->eventRepository->findForCalendar($filters);

        return new JsonResponse($this->transformEventsForFullCalendar($events, $_locale));
    }

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
            // Rate limiter not configured - continue
        }
    }

    private function validateAndSanitizeFilters(Request $request, string $locale): array
    {
        $filters = [
            'locale' => $locale,
            'dataId' => $request->query->get('dataId', null),
            'includeSubFolders' => $request->query->getBoolean('includeSubFolders', false),
            // the repository validates the values; a filter without any usable value matches nothing
            'categories' => RequestParameters::getList($request, 'categories'),
            'tags' => RequestParameters::getList($request, 'tags'),
            'types' => RequestParameters::getList($request, 'types'),
            'attendanceModes' => RequestParameters::getList($request, 'attendanceMode'),
            'countries' => RequestParameters::getList($request, 'country'),
            'free' => RequestParameters::getFlag($request, 'free'),
            'location' => '' !== trim((string) $request->query->get('location', ''))
                ? trim((string) $request->query->get('location'))
                : null,
            'sortBy' => in_array($request->query->get('sortBy'), ['startDate', 'title', 'created', 'changed']) ?
                $request->query->get('sortBy') : 'startDate',
            'sortMethod' => in_array(strtolower($request->query->get('sortMethod', 'asc')), ['asc', 'desc']) ?
                strtolower($request->query->get('sortMethod', 'asc')) : 'asc',
            'start' => $this->validateDate($request->query->get('start')),
            'end' => $this->validateDate($request->query->get('end')),
        ];

        if (empty($filters['dataId'])) {
            unset($filters['dataId']);
            unset($filters['includeSubFolders']);
        } else {
            $filters['dataId'] = (int) $filters['dataId'];
        }

        return $filters;
    }

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

    private function transformEventsForFullCalendar(array $events, string $locale): array
    {
        $result = [];

        foreach ($events as $event) {
            /** @var EventDimensionContent $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate(
                $event,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_LIVE,
                ]
            );

            // All fields (localized + unlocalized) are merged into dimensionContent
            if (!$dimensionContent->getStartDate()) {
                continue;
            }

            $isAllDay = $dimensionContent->getAllDay();
            $typeColor = $this->eventTypeSelect->getColor($dimensionContent->getType() ?? 'default');
            $typeName = $this->eventTypeSelect->getTypeName($dimensionContent->getType() ?? 'default');

            $calendarEvent = [
                'id' => $event->getId(),
                'title' => $dimensionContent->getTitle() ?? '',
                'start' => $this->formatStart($dimensionContent),
                'allDay' => $isAllDay,
                'url' => $this->findUrl($dimensionContent, $locale),
                'extendedProps' => [
                    'type' => $dimensionContent->getType() ?? 'default',
                    'typeName' => $typeName,
                    'typeColor' => $typeColor,
                    'status' => $dimensionContent->getStatus(),
                    'attendanceMode' => $dimensionContent->getAttendanceMode(),
                ],
            ];

            $end = $this->formatEnd($dimensionContent);
            if (null !== $end) {
                $calendarEvent['end'] = $end;
            }

            if ($dimensionContent->getLocation()) {
                $calendarEvent['extendedProps']['location'] = $dimensionContent->getLocation()->getName();
            }

            if ($dimensionContent->getSummary()) {
                $calendarEvent['extendedProps']['summary'] = $dimensionContent->getSummary();
            }

            $calendarEvent['backgroundColor'] = $typeColor;
            $calendarEvent['borderColor'] = $typeColor;

            $result[] = $calendarEvent;
        }

        return $result;
    }

    /**
     * All-day events are sent as a date, so no zone can shift them to another day; all others with the offset of the
     * zone of the event (see EventTimezoneResolver).
     */
    private function formatStart(EventDimensionContent $dimensionContent): string
    {
        $start = $dimensionContent->getStartDate();

        return $dimensionContent->getAllDay()
            ? $start->format('Y-m-d')
            : $this->timezoneResolver->toInstant($start, $dimensionContent)->format('c');
    }

    /**
     * FullCalendar takes the end of an all-day event as exclusive: the day after the last day.
     */
    private function formatEnd(EventDimensionContent $dimensionContent): ?string
    {
        $end = $dimensionContent->getEndDate();
        if (null === $end) {
            return null;
        }

        if (!$dimensionContent->getAllDay()) {
            return $this->timezoneResolver->toInstant($end, $dimensionContent)->format('c');
        }

        if ($end->format('Y-m-d') <= $dimensionContent->getStartDate()->format('Y-m-d')) {
            return null;
        }

        return $end->modify('+1 day')->format('Y-m-d');
    }

    /**
     * Absolute url of the event page in the webspace of the event and the given locale. Falls back to the path
     * if the webspace has no url for the locale.
     */
    private function findUrl(EventDimensionContent $dimensionContent, string $locale): string
    {
        $slug = $dimensionContent->getRoute()?->getSlug();
        if (null === $slug || '' === $slug) {
            return '';
        }

        return $this->urlResolver->findUrl($dimensionContent, $locale) ?? $slug;
    }
}
