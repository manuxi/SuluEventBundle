<?php

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

#[ORM\Entity()]
#[ORM\Table(name: 'app_event_settings')]
class EventSettings implements AuditableInterface
{
    use AuditableTrait;

    public const RESOURCE_KEY = 'event_settings';
    public const FORM_KEY = 'config';
    public const SECURITY_CONTEXT = 'sulu.event.settings';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    // General Display Settings
    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $toggleHeader = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $toggleHero = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $toggleBreadcrumbs = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $toggleTags = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $tagsColor = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $toggleCategories = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $categoriesColor = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $toggleFeedRss = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $toggleFeedAtom = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $toggleIcal = null;

    // Calendar Settings
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $calendarStartDay = 1;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $showWeekNumbers = false;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $showWeekends = true;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $eventLimitPerDay = 3;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $showCalendarEventTime = true;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $showCalendarEventLocation = true;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $showCalendarEventType = true;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $limitToEventRange = true;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $eventColor = '';

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $toggleCalendarView = true;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $allowedCalendarViews = ['dayGridMonth'];

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $calendarWeekTimeStart = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $calendarWeekTimeEnd = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $calendarYearMonths = 3;

    // Breadcrumbs
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageEvents = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageEventsPending = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageEventsExpired = null;

    // List View Settings
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $eventsPerPage = 12;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $defaultSortOrder = 'start_date_asc';

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $showEventImages = true;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $showEventSummary = true;

    // Filter Settings
    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $enableCategoryFilter = true;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $enableLocationFilter = true;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $enableDateFilter = true;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $enableSearchFilter = true;

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToggleHeader(): ?bool
    {
        return $this->toggleHeader;
    }

    public function setToggleHeader(?bool $toggleHeader): void
    {
        $this->toggleHeader = $toggleHeader;
    }

    public function getToggleHero(): ?bool
    {
        return $this->toggleHero;
    }

    public function setToggleHero(?bool $toggleHero): void
    {
        $this->toggleHero = $toggleHero;
    }

    public function getToggleBreadcrumbs(): ?bool
    {
        return $this->toggleBreadcrumbs;
    }

    public function setToggleBreadcrumbs(?bool $toggleBreadcrumbs): void
    {
        $this->toggleBreadcrumbs = $toggleBreadcrumbs;
    }

    public function getToggleTags(): ?bool
    {
        return $this->toggleTags;
    }

    public function setToggleTags(?bool $toggleTags): void
    {
        $this->toggleTags = $toggleTags;
    }

    public function getTagsColor(): ?string
    {
        return $this->tagsColor;
    }

    public function setTagsColor(?string $tagsColor): void
    {
        $this->tagsColor = $tagsColor;
    }

    public function getToggleCategories(): ?bool
    {
        return $this->toggleCategories;
    }

    public function setToggleCategories(?bool $toggleCategories): void
    {
        $this->toggleCategories = $toggleCategories;
    }

    public function getCategoriesColor(): ?string
    {
        return $this->categoriesColor;
    }

    public function setCategoriesColor(?string $categoriesColor): void
    {
        $this->categoriesColor = $categoriesColor;
    }

    public function getToggleFeedRss(): ?bool
    {
        return $this->toggleFeedRss;
    }

    public function setToggleFeedRss(?bool $toggleFeedRss): void
    {
        $this->toggleFeedRss = $toggleFeedRss;
    }

    public function getToggleFeedAtom(): ?bool
    {
        return $this->toggleFeedAtom;
    }

    public function setToggleFeedAtom(?bool $toggleFeedAtom): void
    {
        $this->toggleFeedAtom = $toggleFeedAtom;
    }

    public function getToggleIcal(): ?bool
    {
        return $this->toggleIcal;
    }

    public function setToggleIcal(?bool $toggleIcal): void
    {
        $this->toggleIcal = $toggleIcal;
    }

    public function getCalendarStartDay(): ?int
    {
        return $this->calendarStartDay;
    }

    public function setCalendarStartDay(?int $calendarStartDay): void
    {
        $this->calendarStartDay = $calendarStartDay;
    }

    public function getShowCalendarEventTime(): ?bool
    {
        return $this->showCalendarEventTime;
    }

    public function setShowCalendarEventTime(?bool $showCalendarEventTime): void
    {
        $this->showCalendarEventTime = $showCalendarEventTime;
    }

    public function getShowCalendarEventLocation(): ?bool
    {
        return $this->showCalendarEventLocation;
    }

    public function setShowCalendarEventLocation(?bool $showCalendarEventLocation): void
    {
        $this->showCalendarEventLocation = $showCalendarEventLocation;
    }

    public function getShowCalendarEventType(): ?bool
    {
        return $this->showCalendarEventType;
    }

    public function setShowCalendarEventType(?bool $showCalendarEventType): void
    {
        $this->showCalendarEventType = $showCalendarEventType;
    }

    public function getShowWeekNumbers(): ?bool
    {
        return $this->showWeekNumbers;
    }

    public function setShowWeekNumbers(?bool $showWeekNumbers): void
    {
        $this->showWeekNumbers = $showWeekNumbers;
    }

    public function getShowWeekends(): ?bool
    {
        return $this->showWeekends;
    }

    public function setShowWeekends(?bool $showWeekends): void
    {
        $this->showWeekends = $showWeekends;
    }

    public function getEventLimitPerDay(): ?int
    {
        return $this->eventLimitPerDay;
    }

    public function setEventLimitPerDay(?int $eventLimitPerDay): void
    {
        $this->eventLimitPerDay = $eventLimitPerDay;
    }

    public function getLimitToEventRange(): ?bool
    {
        return $this->limitToEventRange;
    }

    public function setLimitToEventRange(?bool $limitToEventRange): void
    {
        $this->limitToEventRange = $limitToEventRange;
    }

    public function getEventColor(): ?string
    {
        return $this->eventColor;
    }

    public function setEventColor(?string $eventColor): void
    {
        $this->eventColor = $eventColor;
    }

    public function getToggleCalendarView(): ?bool
    {
        return $this->toggleCalendarView;
    }

    public function setToggleCalendarView(?bool $toggleCalendarView): void
    {
        $this->toggleCalendarView = $toggleCalendarView;
    }

    public function getAllowedCalendarViews(): ?array
    {
        return $this->allowedCalendarViews;
    }

    public function setAllowedCalendarViews(array $allowedCalendarViews): void
    {
        $this->allowedCalendarViews = $allowedCalendarViews;
    }

    public function getCalendarWeekTimeStart(): ?string
    {
        return $this->calendarWeekTimeStart;
    }

    public function setCalendarWeekTimeStart(?string $calendarWeekTimeStart): void
    {
        $this->calendarWeekTimeStart = $calendarWeekTimeStart;
    }

    public function getCalendarWeekTimeEnd(): ?string
    {
        return $this->calendarWeekTimeEnd;
    }

    public function setCalendarWeekTimeEnd(?string $calendarWeekTimeEnd): void
    {
        $this->calendarWeekTimeEnd = $calendarWeekTimeEnd;
    }

    public function getCalendarYearMonths(): ?int
    {
        return $this->calendarYearMonths;
    }

    public function setCalendarYearMonths(?int $calendarYearMonths): void
    {
        $this->calendarYearMonths = $calendarYearMonths;
    }

    public function getPageEvents(): ?string
    {
        return $this->pageEvents;
    }

    public function setPageEvents(?string $pageEvents): void
    {
        $this->pageEvents = $pageEvents;
    }

    public function getPageEventsPending(): ?string
    {
        return $this->pageEventsPending;
    }

    public function setPageEventsPending(?string $pageEventsPending): void
    {
        $this->pageEventsPending = $pageEventsPending;
    }

    public function getPageEventsExpired(): ?string
    {
        return $this->pageEventsExpired;
    }

    public function setPageEventsExpired(?string $pageEventsExpired): void
    {
        $this->pageEventsExpired = $pageEventsExpired;
    }

    public function getEventsPerPage(): ?int
    {
        return $this->eventsPerPage;
    }

    public function setEventsPerPage(?int $eventsPerPage): void
    {
        $this->eventsPerPage = $eventsPerPage;
    }

    public function getDefaultSortOrder(): ?string
    {
        return $this->defaultSortOrder;
    }

    public function setDefaultSortOrder(?string $defaultSortOrder): void
    {
        $this->defaultSortOrder = $defaultSortOrder;
    }

    public function getShowEventImages(): ?bool
    {
        return $this->showEventImages;
    }

    public function setShowEventImages(?bool $showEventImages): void
    {
        $this->showEventImages = $showEventImages;
    }

    public function getShowEventSummary(): ?bool
    {
        return $this->showEventSummary;
    }

    public function setShowEventSummary(?bool $showEventSummary): void
    {
        $this->showEventSummary = $showEventSummary;
    }

    public function getEnableCategoryFilter(): ?bool
    {
        return $this->enableCategoryFilter;
    }

    public function setEnableCategoryFilter(?bool $enableCategoryFilter): void
    {
        $this->enableCategoryFilter = $enableCategoryFilter;
    }

    public function getEnableLocationFilter(): ?bool
    {
        return $this->enableLocationFilter;
    }

    public function setEnableLocationFilter(?bool $enableLocationFilter): void
    {
        $this->enableLocationFilter = $enableLocationFilter;
    }

    public function getEnableDateFilter(): ?bool
    {
        return $this->enableDateFilter;
    }

    public function setEnableDateFilter(?bool $enableDateFilter): void
    {
        $this->enableDateFilter = $enableDateFilter;
    }

    public function getEnableSearchFilter(): ?bool
    {
        return $this->enableSearchFilter;
    }

    public function setEnableSearchFilter(?bool $enableSearchFilter): void
    {
        $this->enableSearchFilter = $enableSearchFilter;
    }
}