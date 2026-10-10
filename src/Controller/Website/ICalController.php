<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Website;

use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Service\ICalGenerator;
use Manuxi\SuluEventBundle\Service\RequestParameters;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ICalController extends AbstractController
{
    /**
     * Event ids are UUIDs (v7 for new events). Also used as the route requirement in routes_website.yaml.
     */
    public const UUID_REQUIREMENT = '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}';

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
            'locale'          => $_locale,
            'categories'      => RequestParameters::getList($request, 'categories'),
            'tags'            => RequestParameters::getList($request, 'tags'),
            'types'           => RequestParameters::getList($request, 'types'),
            'attendanceModes' => RequestParameters::getList($request, 'attendanceMode'),
            'countries'       => RequestParameters::getList($request, 'country'),
            'free'            => RequestParameters::getFlag($request, 'free'),
        ];

        $ical = $this->icalGenerator->generate($filters, $_locale);

        return new Response(
            $ical,
            Response::HTTP_OK,
            [
                'Content-Type'        => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="events.ics"',
            ]
        );
    }

    #[Route('/{_locale}/events/{id}/calendar.ics', name: 'sulu_event.ical_single', requirements: ['id' => self::UUID_REQUIREMENT])]
    public function singleAction(string $id, string $_locale): Response
    {
        $event = $this->eventRepository->findByUuid($id);

        if (!$event) {
            throw new NotFoundHttpException('Event not found');
        }

        try {
            /** @var EventDimensionContent $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate(
                $event,
                [
                    'locale' => $_locale,
                    'stage'  => DimensionContentInterface::STAGE_LIVE,
                ]
            );
        } catch (ContentNotFoundException) {
            throw new NotFoundHttpException('Event not found in this locale');
        }

        if (!$dimensionContent->getTitle()) {
            throw new NotFoundHttpException('Event not found in this locale');
        }

        $ical = $this->icalGenerator->generateSingle($event, $dimensionContent);

        return new Response(
            $ical,
            Response::HTTP_OK,
            [
                'Content-Type'        => 'text/calendar; charset=utf-8',
                'Content-Disposition' => \sprintf('attachment; filename="event-%s.ics"', $event->getUuid()),
            ]
        );
    }
}
