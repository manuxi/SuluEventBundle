<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Website;

use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Service\ICalGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ICalController extends AbstractController
{
    public function __construct(
        private ICalGenerator $icalGenerator,
        private EventRepository $eventRepository
    ) {}

    /**
     * Generate iCal feed for all events
     */
    #[Route('/{_locale}/events/calendar.ics', name: 'sulu_event.ical_feed')]
    public function feedAction(Request $request, string $_locale): Response
    {
        $filters = [
            'locale' => $_locale,
            'categories' => $request->query->all('categories'),
            'tags' => $request->query->all('tags'),
        ];

        $ical = $this->icalGenerator->generate($filters);

        return new Response(
            $ical,
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="events.ics"',
            ]
        );
    }

    /**
     * Generate iCal for single event
     */
    #[Route('/{_locale}/events/{id}/calendar.ics', name: 'sulu_event.ical_single', requirements: ['id' => '\d+'])]
    public function singleAction(int $id, string $_locale): Response
    {
        $event = $this->eventRepository->findById($id, $_locale);
        
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        $ical = $this->icalGenerator->generateSingle($event);

        return new Response(
            $ical,
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => sprintf('attachment; filename="event-%d.ics"', $id),
            ]
        );
    }
}
