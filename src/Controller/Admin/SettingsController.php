<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Manuxi\SuluEventBundle\Domain\Event\Config\ModifiedEvent;
use Manuxi\SuluEventBundle\Entity\EventSettings;
use Manuxi\SuluEventBundle\Import\ImportRunner;
use Manuxi\SuluEventBundle\Import\ImportRunSummary;
use Manuxi\SuluEventBundle\Repository\EventImportRunRepository;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/api')]
class SettingsController extends AbstractRestController implements SecuredControllerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DomainEventCollectorInterface $domainEventCollector,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null,
        private ?EventImportRunRepository $importRunRepository = null,
        private ?ImportRunner $importRunner = null,
        private ?EventDispatcherInterface $eventDispatcher = null,
        private ?TranslatorInterface $translator = null,
    ) {
        parent::__construct($viewHandler, $tokenStorage);
    }

    #[Route(
        '/event-settings/{id}.{_format}',
        name: 'sulu_event.get_event-settings',
        requirements: [
            '_format' => 'json|csv',
        ],
        options: ['expose' => true],
        defaults: [
            '_format' => 'json',
        ],
        methods: ['GET']
    )]
    public function getAction(): Response
    {
        $entity = $this->entityManager->getRepository(EventSettings::class)->findOneBy([]);

        return $this->handleView($this->view($this->getDataForEntity($entity ?: new EventSettings())));
        // return new JsonResponse($this->getDataForEntity($entity ?: new EventSettings()));
    }

    #[Route(
        '/event-settings/{id}.{_format}',
        name: 'sulu_event.put_event-settings',
        requirements: [
            '_format' => 'json',
        ],
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['PUT']
    )]
    public function putAction(Request $request): Response
    {
        // the button "Import now" of the tab "Import": PUT ...?action=import
        if ('import' === $request->query->get('action')) {
            return $this->startImport();
        }

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
        // return new JsonResponse($this->getDataForEntity($entity));
    }

    /**
     * Starts the import of all sources after the answer is sent (the run takes a minute or more: the request must not wait for
     * it). The tab shows the state and, when it is done, the result.
     */
    private function startImport(): Response
    {
        if (null === $this->importRunner || null === $this->eventDispatcher) {
            return new JsonResponse(['code' => 400, 'message' => 'The import is not available.'], Response::HTTP_BAD_REQUEST);
        }

        $runner = $this->importRunner;
        if (!$runner->isAvailable()) {
            return new JsonResponse(['code' => 400, 'message' => 'The import is switched off or has no source (sulu_event.import).'], Response::HTTP_BAD_REQUEST);
        }

        if (!$runner->reserve()) {
            return new JsonResponse(['code' => 409, 'message' => 'The import is running already.'], Response::HTTP_CONFLICT);
        }

        $this->eventDispatcher->addListener(KernelEvents::TERMINATE, static function (TerminateEvent $event) use ($runner): void {
            $runner->run();
        });

        return new JsonResponse(['importRunning' => true], Response::HTTP_ACCEPTED);
    }

    protected function getDataForEntity(EventSettings $entity): array
    {
        return [
            // The import: running now (the button "Import now" of the tab "Import" waits for it)
            'importRunning' => null !== $this->importRunner?->runningSince(),
            'importState' => $this->importState(),
            'importProgressLabel' => $this->importProgressLabel(),

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
        ] + $this->getImportRunData();
    }

    /**
     * The last run of the import for the read only tab "Import".
     *
     * @return array<string, string|null>
     */
    /**
     * What the tab says about the import: running since when, or ready (and how it can be started).
     */
    private function importState(): ?string
    {
        if (null === $this->importRunner) {
            return null;
        }

        $since = $this->importRunner->runningSince();
        if (null === $since) {
            return $this->importRunner->isAvailable()
                ? $this->translate('sulu_event.import.run_state_ready')
                : $this->translate('sulu_event.import.run_state_off');
        }

        $time     = (new \DateTimeImmutable('@' . $since))->setTimezone(new \DateTimeZone(date_default_timezone_get()))->format('H:i:s');
        $progress = $this->importRunner->progress();
        $source   = null !== $progress && null !== $progress['source']
            ? $this->translate('sulu_event.import.run_state_source', ['%source%' => $progress['source'], '%index%' => (string) $progress['sourceIndex'], '%count%' => (string) $progress['sourceCount']])
            : '';

        return trim($this->translate('sulu_event.import.run_state_running', ['%time%' => $time]) . ' ' . $source);
    }

    /**
     * The metric on the button: "Imported: 120/345" while the import runs and for an hour after it, null otherwise.
     */
    private function importProgressLabel(): ?string
    {
        $progress = $this->importRunner?->progress();
        if (null === $progress || ('running' === $progress['state'] && 0 === $progress['known'])) {
            return null;
        }

        return $this->translate('sulu_event.import.progress_label', ['%done%' => (string) $progress['done'], '%total%' => (string) max($progress['known'], $progress['done'])]);
    }

    /**
     * @param array<string, string> $parameters
     */
    private function translate(string $key, array $parameters = []): string
    {
        return null === $this->translator ? $key : $this->translator->trans($key, $parameters, 'admin');
    }

    private function getImportRunData(): array
    {
        $none = [
            'importLastRunWhen'         => null,
            'importLastRunSummary'      => null,
            'importLastRunErrors'       => null,
            'importLastRunNewLocations' => null,
        ];

        if (null === $this->importRunRepository) {
            return $none;
        }

        try {
            $run = $this->importRunRepository->findLast(true);
        } catch (\Throwable) {
            // the tables of the import may not exist yet
            return $none;
        }

        return null === $run ? $none : ImportRunSummary::fromRun($run);
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
