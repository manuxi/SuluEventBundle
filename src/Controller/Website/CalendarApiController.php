<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Website;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Service\EventTypeSelect;
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
        return array_map(function (Event $event) use ($locale) {
            /** @var EventDimensionContent $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate(
                $event,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_LIVE,
                ]
            );

            $isAllDay = $this->isAllDayEvent($event);

            $typeColor = $this->eventTypeSelect->getColor($event->getType() ?? 'default');
            $typeName = $this->eventTypeSelect->getTypeName($event->getType() ?? 'default');

            $calendarEvent = [
                'id' => $event->getId(),
                'title' => $dimensionContent->getTitle() ?? '',
                'start' => $event->getStartDate()->format('c'),
                'allDay' => $isAllDay,
                'url' => $dimensionContent->getRoute()?->getSlug() ?? '',
                'extendedProps' => [
                    'type' => $event->getType() ?? 'default',
                    'typeName' => $typeName,
                    'typeColor' => $typeColor,
                ],
            ];

            if ($event->getEndDate()) {
                $calendarEvent['end'] = $event->getEndDate()->format('c');
            }

            if ($event->getLocation()) {
                $calendarEvent['extendedProps']['location'] = $event->getLocation()->getName();
            }

            if ($dimensionContent->getSummary()) {
                $calendarEvent['extendedProps']['summary'] = $dimensionContent->getSummary();
            }

            $calendarEvent['backgroundColor'] = $typeColor;
            $calendarEvent['borderColor'] = $typeColor;

            return $calendarEvent;
        }, $events);
    }

    private function isAllDayEvent(Event $event): bool
    {
        if (!$event->getEndDate()) {
            return true;
        }

        $start = $event->getStartDate();
        $end = $event->getEndDate();

        return $start->format('H:i:s') === '00:00:00'
            && $end->format('H:i:s') === '23:59:59';
    }
}