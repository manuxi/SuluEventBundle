<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use HandcraftedInTheAlps\RestRoutingBundle\Controller\Annotations\RouteResource;
use HandcraftedInTheAlps\RestRoutingBundle\Routing\ClassResourceInterface;
use Manuxi\SuluEventBundle\Domain\Event\Config\ModifiedEvent;
use Manuxi\SuluEventBundle\Entity\EventSettings;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @RouteResource("event-settings")
 */
#[Route('/admin/api')]
class SettingsController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DomainEventCollectorInterface $domainEventCollector,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        parent::__construct($viewHandler, $tokenStorage);
    }

    #[Route(
        '/event-settings/{id}.{_format}',
        name: 'sulu_event.get_event-settings',
        requirements: [
            '_format' => 'json|csv'
        ],
        options: ['expose' => true],
        defaults: [
            '_format' => 'json'
        ],
        methods: ['GET']
    )]
    public function getAction(): Response
    {
        $entity = $this->entityManager->getRepository(EventSettings::class)->findOneBy([]);

        return $this->handleView($this->view($this->getDataForEntity($entity ?: new EventSettings())));
    }

    #[Route(
        '/event-settings/{id}.{_format}',
        name: 'sulu_event.put_event-settings',
        requirements: [
            '_format' => 'json'
        ],
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['PUT']
    )]
    public function putAction(Request $request): Response
    {
        $entity = $this->entityManager->getRepository(EventSettings::class)->findOneBy([]);
        if (!$entity) {
            $entity = new EventSettings();
            $this->entityManager->persist($entity);
        }

        $this->domainEventCollector->collect(
            new ModifiedEvent($entity, $request->request->all())
        );

        $data = $request->toArray();
        $this->mapDataToEntity($data, $entity);
        $this->entityManager->flush();

        return $this->handleView($this->view($this->getDataForEntity($entity)));
    }

    protected function getDataForEntity(EventSettings $entity): array
    {
        return [
            // General Display
            'toggleHeader' => $entity->getToggleHeader(),
            'toggleHero' => $entity->getToggleHero(),
            'toggleBreadcrumbs' => $entity->getToggleBreadcrumbs(),

            // Calendar Settings
            'calendarStartDay' => $entity->getCalendarStartDay(),
            'showCalendarEventTime' => $entity->getShowCalendarEventTime(),
            'showCalendarEventLocation' => $entity->getShowCalendarEventLocation(),
            'eventLimitPerDay' => $entity->getEventLimitPerDay(),
            'showWeekNumbers' => $entity->getShowWeekNumbers(),
            'showWeekends' => $entity->getShowWeekends(),
            'limitToEventRange' => $entity->getLimitToEventRange(),

            'eventColor' => $entity->getEventColor(),
            'toggleCalendarView' => $entity->getToggleCalendarView(),
            'allowedCalenderViews' => $entity->getAllowedCalendarViews(),

            // Breadcrumbs
            'pageEvents' => $entity->getPageEvents(),
            'pageEventsPending' => $entity->getPageEventsPending(),
            'pageEventsExpired' => $entity->getPageEventsExpired(),

            // List View
            'eventsPerPage' => $entity->getEventsPerPage(),
            'defaultSortOrder' => $entity->getDefaultSortOrder(),
            'showEventImages' => $entity->getShowEventImages(),
            'showEventSummary' => $entity->getShowEventSummary(),

            // Filters
            'enableCategoryFilter' => $entity->getEnableCategoryFilter(),
            'enableLocationFilter' => $entity->getEnableLocationFilter(),
            'enableDateFilter' => $entity->getEnableDateFilter(),
            'enableSearchFilter' => $entity->getEnableSearchFilter(),
        ];
    }

    protected function mapDataToEntity(array $data, EventSettings $entity): void
    {
        // General Display
        $entity->setToggleHeader($data['toggleHeader'] ?? null);
        $entity->setToggleHero($data['toggleHero'] ?? null);
        $entity->setToggleBreadcrumbs($data['toggleBreadcrumbs'] ?? null);

        // Calendar Settings
        $entity->setCalendarStartDay($data['calendarStartDay'] ?? 1);
        $entity->setShowCalendarEventTime($data['showCalendarEventTime'] ?? true);
        $entity->setShowCalendarEventLocation($data['showCalendarEventLocation'] ?? true);
        $entity->setEventLimitPerDay($data['eventLimitPerDay'] ?? 3);
        $entity->setShowWeekNumbers($data['showWeekNumbers'] ?? true);
        $entity->setShowWeekends($data['showWeekends'] ?? true);
        $entity->setLimitToEventRange($data['limitToEventRange'] ?? true);

        $entity->setEventColor($data['eventColor'] ?? '');
        $entity->setToggleCalendarView($data['toggleCalendarView'] ?? true);
        $entity->setAllowedCalendarViews($data['allowedCalenderViews'] ?? '');

        // Breadcrumbs
        $entity->setPageEvents($data['pageEvents'] ?? null);
        $entity->setPageEventsPending($data['pageEventsPending'] ?? null);
        $entity->setPageEventsExpired($data['pageEventsExpired'] ?? null);

        // List View
        $entity->setEventsPerPage($data['eventsPerPage'] ?? 12);
        $entity->setDefaultSortOrder($data['defaultSortOrder'] ?? 'start_date_asc');
        $entity->setShowEventImages($data['showEventImages'] ?? true);
        $entity->setShowEventSummary($data['showEventSummary'] ?? true);

        // Filters
        $entity->setEnableCategoryFilter($data['enableCategoryFilter'] ?? true);
        $entity->setEnableLocationFilter($data['enableLocationFilter'] ?? true);
        $entity->setEnableDateFilter($data['enableDateFilter'] ?? true);
        $entity->setEnableSearchFilter($data['enableSearchFilter'] ?? true);
    }

    public function getSecurityContext(): string
    {
        return EventSettings::SECURITY_CONTEXT;
    }

    public function getLocale(Request $request): ?string
    {
        return $request->query->get('locale');
    }
}