<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
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
 */
class ICalGenerator
{
    private const PRODID = '-//Sulu Event Bundle//EN';

    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly ContentAggregatorInterface $contentAggregator,
        private readonly WebspaceManagerInterface $webspaceManager,
        private readonly RequestStack $requestStack,
        private readonly ?string $timezone = null,
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
     * The zone the stored start and end of an event are meant in. The database holds the wall clock time as it was
     * entered, without any zone.
     */
    public function resolveTimezone(EventDimensionContent $dimensionContent): \DateTimeZone
    {
        return new \DateTimeZone($this->timezone ?? date_default_timezone_get());
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

        if ($this->isAllDay($start, $end)) {
            // a date without time; DTEND is exclusive, so it is the day after the last day
            $vevent->add('DTSTART', new \DateTimeImmutable($start->format('Y-m-d'), $utc), ['VALUE' => 'DATE']);
            if (null !== $end && $end->format('Y-m-d') > $start->format('Y-m-d')) {
                $vevent->add('DTEND', (new \DateTimeImmutable($end->format('Y-m-d'), $utc))->modify('+1 day'), ['VALUE' => 'DATE']);
            }
        } else {
            $zone     = $this->resolveTimezone($dimensionContent);
            $startUtc = $this->toUtc($start, $zone);
            $vevent->add('DTSTART', $startUtc);

            if (null !== $end) {
                $endUtc = $this->toUtc($end, $zone);
                if ($endUtc > $startUtc) {
                    $vevent->add('DTEND', $endUtc);
                }
            }
        }

        $vevent->SUMMARY = strip_tags($dimensionContent->getTitle() ?? '');

        if ($dimensionContent->getSummary()) {
            $vevent->DESCRIPTION = strip_tags($dimensionContent->getSummary());
        }

        if ($dimensionContent->getLocation()) {
            $vevent->LOCATION = $dimensionContent->getLocation()->getName();
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
    private function toUtc(\DateTimeInterface $stored, \DateTimeZone $zone): \DateTimeImmutable
    {
        return (new \DateTimeImmutable($stored->format('Y-m-d H:i:s'), $zone))->setTimezone(new \DateTimeZone('UTC'));
    }

    /**
     * Events without an explicit "all day" field (planned) count as all-day if they start at midnight and either
     * have no end or end at the end of a day. Same idea as the calendar API.
     */
    private function isAllDay(\DateTimeInterface $start, ?\DateTimeInterface $end): bool
    {
        if ('00:00:00' !== $start->format('H:i:s')) {
            return false;
        }

        return null === $end || '23:59:59' === $end->format('H:i:s');
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
