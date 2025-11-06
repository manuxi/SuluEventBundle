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
            'toggleTags' => $entity->getToggleTags(),
            'tagsColor' => $entity->getTagsColor(),
            'toggleCategories' => $entity->getToggleCategories(),
            'categoriesColor' => $entity->getCategoriesColor(),
            'toggleFeedRss' => $entity->getToggleFeedRss(),
            'toggleFeedAtom' => $entity->getToggleFeedAtom(),
            'toggleIcal' => $entity->getToggleIcal(),

            // Calendar Settings
            'calendarStartDay' => $entity->getCalendarStartDay(),
            'showCalendarEventTime' => $entity->getShowCalendarEventTime(),
            'showCalendarEventType' => $entity->getShowCalendarEventType(),
            'showCalendarEventLocation' => $entity->getShowCalendarEventLocation(),
            'eventLimitPerDay' => $entity->getEventLimitPerDay(),
            'showCalendarWeekNumbers' => $entity->getShowWeekNumbers(),
            'showCalendarWeekends' => $entity->getShowWeekends(),
            'limitToEventRange' => $entity->getLimitToEventRange(),

            'eventColor' => $entity->getEventColor(),
            'toggleCalendarView' => $entity->getToggleCalendarView(),
            'allowedCalendarViews' => $entity->getAllowedCalendarViews(),

            'calendarWeekTimeStart' => $entity->getCalendarWeekTimeStart(),
            'calendarWeekTimeEnd' => $entity->getCalendarWeekTimeEnd(),
            'calendarYearMonths' => $entity->getCalendarYearMonths(),

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

        $entity->setToggleTags($data['toggleTags'] ?? null);
        $entity->setTagsColor($data['tagsColor'] ?? null);
        $entity->setToggleCategories($data['toggleCategories'] ?? null);
        $entity->setCategoriesColor($data['categoriesColor'] ?? null);

        $entity->setToggleFeedRss($data['toggleFeedRss'] ?? null);
        $entity->setToggleFeedAtom($data['toggleFeedAtom'] ?? null);
        $entity->setToggleIcal($data['toggleIcal'] ?? null);

        // Calendar Settings
        $entity->setCalendarStartDay($data['calendarStartDay'] ?? 1);
        $entity->setShowCalendarEventTime($data['showCalendarEventTime'] ?? false);
        $entity->setShowCalendarEventType($data['showCalendarEventType'] ?? false);
        $entity->setShowCalendarEventLocation($data['showCalendarEventLocation'] ?? false);
        $entity->setEventLimitPerDay($data['eventLimitPerDay'] ?? 3);
        $entity->setShowWeekNumbers($data['showCalendarWeekNumbers'] ?? false);
        $entity->setShowWeekends($data['showCalendarWeekends'] ?? false);
        $entity->setLimitToEventRange($data['limitToEventRange'] ?? false);

        $entity->setEventColor($data['eventColor'] ?? '');
        $entity->setToggleCalendarView($data['toggleCalendarView'] ?? false);

        $allowedViews = $data['allowedCalendarViews'] ?? [];
        if (is_string($allowedViews)) {
            $allowedViews = !empty($allowedViews) ? explode(',', $allowedViews) : [];
        }
        $entity->setAllowedCalendarViews($allowedViews);

        $entity->setCalendarWeekTimeStart($data['calendarWeekTimeStart'] ?? '00:00');
        $entity->setCalendarWeekTimeEnd($data['calendarWeekTimeEnd'] ?? '23:59');
        $entity->setCalendarYearMonths($data['calendarYearMonths'] ?? 3);

        // Breadcrumbs
        $entity->setPageEvents($data['pageEvents'] ?? null);
        $entity->setPageEventsPending($data['pageEventsPending'] ?? null);
        $entity->setPageEventsExpired($data['pageEventsExpired'] ?? null);

        // List View
        $entity->setEventsPerPage($data['eventsPerPage'] ?? 12);
        $entity->setDefaultSortOrder($data['defaultSortOrder'] ?? 'start_date_asc');
        $entity->setShowEventImages($data['showEventImages'] ?? false);
        $entity->setShowEventSummary($data['showEventSummary'] ?? false);

        // Filters
        $entity->setEnableCategoryFilter($data['enableCategoryFilter'] ?? false);
        $entity->setEnableLocationFilter($data['enableLocationFilter'] ?? false);
        $entity->setEnableDateFilter($data['enableDateFilter'] ?? false);
        $entity->setEnableSearchFilter($data['enableSearchFilter'] ?? false);
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