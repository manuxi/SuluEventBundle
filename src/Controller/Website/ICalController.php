<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Website;

use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Service\ICalGenerator;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ICalController extends AbstractController
{
    public function __construct(
        private readonly ICalGenerator $icalGenerator,
        private readonly EventRepository $eventRepository,
        private readonly ContentAggregatorInterface $contentAggregator,
    ) {
    }

    #[Route('/{_locale}/events/calendar.ics', name: 'sulu_event.ical_feed')]
    public function feedAction(Request $request, string $_locale): Response
    {
        $filters = [
            'locale' => $_locale,
            'categories' => $request->query->all('categories'),
            'tags' => $request->query->all('tags'),
        ];

        $ical = $this->icalGenerator->generate($filters, $_locale);

        return new Response(
            $ical,
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="events.ics"',
            ]
        );
    }

    #[Route('/{_locale}/events/{id}/calendar.ics', name: 'sulu_event.ical_single', requirements: ['id' => '\d+'])]
    public function singleAction(int $id, string $_locale): Response
    {
        $event = $this->eventRepository->findOneBy(['id' => $id]);

        if (!$event) {
            throw new NotFoundHttpException('Event not found');
        }

        /** @var EventDimensionContent $dimensionContent */
        $dimensionContent = $this->contentAggregator->aggregate(
            $event,
            [
                'locale' => $_locale,
                'stage' => DimensionContentInterface::STAGE_LIVE,
            ]
        );

        if (!$dimensionContent->getTitle()) {
            throw new NotFoundHttpException('Event not found in this locale');
        }

        $ical = $this->icalGenerator->generateSingle($event, $dimensionContent);

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