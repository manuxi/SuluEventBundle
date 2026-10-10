<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Manuxi\SuluEventBundle\Entity\AttendanceMode;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\EventStatus;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sabre\VObject\Component\VCalendar;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Builds the iCalendar (RFC 5545) output with sabre/vobject, which takes care of escaping and line folding.
 *
 * Event times are stored as wall clock time without a zone (see resolveTimezone()). They are converted to UTC for
 * the calendar file, so no VTIMEZONE component is needed and every calendar application shows the right time.
 * All-day events (flag of the event) are written as dates without a time.
 */
class ICalGenerator
{
    private const PRODID = '-//Sulu Event Bundle//EN';

    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly ContentAggregatorInterface $contentAggregator,
        private readonly WebspaceManagerInterface $webspaceManager,
        private readonly RequestStack $requestStack,
        private readonly EventTimezoneResolver $timezoneResolver,
    ) {
    }

    /**
     * Generate iCal feed for events.
     *
     * @param array<string, mixed> $filters Filters for the repository (categories, tags)
     * @param string               $locale  Locale for content
     *
     * @return string iCal formatted string
     */
    public function generate(array $filters, string $locale): string
    {
        $filters['locale'] = $locale;
        $events            = $this->eventRepository->findForIcal($filters);

        $calendar = $this->createCalendar();
        $calendar->add('METHOD', 'PUBLISH');

        foreach ($events as $event) {
            try {
                /** @var EventDimensionContent $dimensionContent */
                $dimensionContent = $this->contentAggregator->aggregate(
                    $event,
                    [
                        'locale' => $locale,
                        'stage'  => DimensionContentInterface::STAGE_LIVE,
                    ]
                );
            } catch (ContentNotFoundException) {
                continue;
            }

            if (!$dimensionContent->getTitle()) {
                continue;
            }

            // All fields (including unlocalized) are merged into dimensionContent
            $this->addEvent($calendar, $event, $dimensionContent, $locale);
        }

        return $calendar->serialize();
    }

    /**
     * Generate single event iCal.
     */
    public function generateSingle(Event $event, EventDimensionContent $dimensionContent): string
    {
        $calendar = $this->createCalendar();
        $this->addEvent($calendar, $event, $dimensionContent, (string) $dimensionContent->getLocale());

        return $calendar->serialize();
    }

    /**
     * The zone the stored start and end of an event are meant in: the one of the event, its location, the configured
     * one (sulu_event.timezone) or the PHP default. The database holds the wall clock time as it was entered, without
     * any zone.
     */
    public function resolveTimezone(EventDimensionContent $dimensionContent): \DateTimeZone
    {
        return $this->timezoneResolver->resolve($dimensionContent);
    }

    private function createCalendar(): VCalendar
    {
        $calendar         = new VCalendar();
        $calendar->PRODID = self::PRODID;

        return $calendar;
    }

    private function addEvent(VCalendar $calendar, Event $event, EventDimensionContent $dimensionContent, string $locale): void
    {
        $start = $dimensionContent->getStartDate();
        if (null === $start) {
            // without a start the VEVENT would be invalid
            return;
        }

        $end = $dimensionContent->getEndDate();
        $url = $this->findUrl($dimensionContent, $locale);

        $vevent      = $calendar->add('VEVENT');
        $vevent->UID = $event->getId() . '@' . $this->resolveHost($url);

        $utc                       = new \DateTimeZone('UTC');
        $changed                   = $dimensionContent->getChanged()->setTimezone($utc);
        $vevent->DTSTAMP           = $changed;
        $vevent->{'LAST-MODIFIED'} = $changed;

        if ($dimensionContent->getAllDay()) {
            // a date without time; DTEND is exclusive, so it is the day after the last day
            $vevent->add('DTSTART', new \DateTimeImmutable($start->format('Y-m-d'), $utc), ['VALUE' => 'DATE']);
            if (null !== $end && $end->format('Y-m-d') > $start->format('Y-m-d')) {
                $vevent->add('DTEND', (new \DateTimeImmutable($end->format('Y-m-d'), $utc))->modify('+1 day'), ['VALUE' => 'DATE']);
            }
        } else {
            $startUtc = $this->toUtc($start, $dimensionContent);
            $vevent->add('DTSTART', $startUtc);

            if (null !== $end) {
                $endUtc = $this->toUtc($end, $dimensionContent);
                if ($endUtc > $startUtc) {
                    $vevent->add('DTEND', $endUtc);
                }
            }
        }

        $vevent->SUMMARY = strip_tags($dimensionContent->getTitle() ?? '');

        if ($dimensionContent->getSummary()) {
            $vevent->DESCRIPTION = strip_tags($dimensionContent->getSummary());
        }

        $status = $this->findStatus($dimensionContent);
        if (null !== $status) {
            $vevent->STATUS = $status;
        }

        if ($dimensionContent->getLocation()) {
            $vevent->LOCATION = $dimensionContent->getLocation()->getName();
        } elseif (AttendanceMode::Offline->value !== $dimensionContent->getAttendanceMode() && null !== $dimensionContent->getOnlineUrl()) {
            // an online event has no place; the link to take part is the "location" calendar applications show
            $vevent->LOCATION = $dimensionContent->getOnlineUrl();
        }

        if (null !== $url && null !== $dimensionContent->getRoute()?->getSlug()) {
            $vevent->URL = $url;
        }

        $categories = $this->findCategoryNames($dimensionContent, $locale);
        if ([] !== $categories) {
            $vevent->add('CATEGORIES', $categories);
        }
    }

    /**
     * The wall clock time of the stored date, read in the zone of the event, as an instant in UTC.
     */
    private function toUtc(\DateTimeInterface $stored, EventDimensionContent $dimensionContent): \DateTimeImmutable
    {
        return $this->timezoneResolver->toInstant($stored, $dimensionContent)->setTimezone(new \DateTimeZone('UTC'));
    }

    /**
     * A cancelled event stays in the calendar, marked as cancelled; a postponed one has no valid date any more.
     */
    private function findStatus(EventDimensionContent $dimensionContent): ?string
    {
        return match ($dimensionContent->getStatus()) {
            EventStatus::Cancelled->value => 'CANCELLED',
            EventStatus::Postponed->value => 'TENTATIVE',
            default                       => null,
        };
    }

    /**
     * Absolute url of the event page in the webspace of the event and the given locale.
     */
    private function findUrl(EventDimensionContent $dimensionContent, string $locale): ?string
    {
        $slug = $dimensionContent->getRoute()?->getSlug();

        return $this->webspaceManager->findUrlByResourceLocator(
            $slug ?? '/',
            null,
            $locale,
            $dimensionContent->getMainWebspace(),
        );
    }

    /**
     * The host the UID of the event is built with. It comes from the url of the website and not from the current
     * request, so the UID stays the same when the feed is fetched via another host name.
     */
    private function resolveHost(?string $url): string
    {
        $host = null !== $url ? parse_url($url, \PHP_URL_HOST) : null;
        if (\is_string($host) && '' !== $host) {
            return $host;
        }

        return $this->requestStack->getCurrentRequest()?->getHost() ?? 'localhost';
    }

    /**
     * @return string[]
     */
    private function findCategoryNames(EventDimensionContent $dimensionContent, string $locale): array
    {
        $names = [];
        foreach ($dimensionContent->getExcerptCategories() as $category) {
            $translation = $category->findTranslationByLocale($locale)
                ?? $category->findTranslationByLocale($category->getDefaultLocale());

            if (null !== $translation && '' !== $translation->getTranslation()) {
                $names[] = $translation->getTranslation();
            }
        }

        return $names;
    }
}
