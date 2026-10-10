<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\Source;

use Manuxi\SuluEventBundle\Import\CandidateLocation;
use Manuxi\SuluEventBundle\Import\EventCandidate;
use Manuxi\SuluEventBundle\Import\EventSourceInterface;
use Manuxi\SuluEventBundle\Import\Http\ImportHttpClient;
use Manuxi\SuluEventBundle\Import\Resolver\TimezoneGuesser;
use Manuxi\SuluEventBundle\Import\SourceConfig;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\DateTimeParser;
use Sabre\VObject\Reader;
use Sabre\VObject\Recur\EventIterator;

/**
 * Reads an iCalendar feed (Luma calendars, conference calendars, ...). Only the facts of an event are taken: title,
 * time, place, status. Names and addresses of organizers and attendees are dropped, the description is no text of ours
 * (it only goes to EventCandidate::$sourceDescription for whoever writes the text). Recurring events are expanded for
 * the next 12 months.
 */
class ICalSource implements EventSourceInterface
{
    private const WINDOW        = '+12 months';
    private const MAX_INSTANCES = 60;

    /** @var \Closure(): \DateTimeImmutable */
    private \Closure $now;

    /**
     * @param (\Closure(): \DateTimeImmutable)|null $now replaceable for tests
     */
    public function __construct(
        private readonly SourceConfig $config,
        private readonly ImportHttpClient $httpClient,
        private readonly TimezoneGuesser $timezoneGuesser,
        ?\Closure $now = null,
    ) {
        $this->now = $now ?? static fn (): \DateTimeImmutable => new \DateTimeImmutable();
    }

    public function getKey(): string
    {
        return $this->config->getSourceKey();
    }

    public function getPriority(): int
    {
        return $this->config->getPriority();
    }

    public function fetch(): iterable
    {
        $body = $this->httpClient->get($this->config->url)->body;

        return $this->parse($body);
    }

    /**
     * @return \Generator<EventCandidate>
     */
    public function parse(string $body): \Generator
    {
        try {
            $calendar = Reader::read($body, Reader::OPTION_FORGIVING);
        } catch (\Throwable $e) {
            throw new \RuntimeException(\sprintf('%s is not a valid iCalendar file: %s', $this->config->url, $e->getMessage()), 0, $e);
        }

        if (!$calendar instanceof VCalendar) {
            throw new \RuntimeException(\sprintf('%s is not an iCalendar file.', $this->config->url));
        }

        $now              = ($this->now)();
        $from             = $now->setTime(0, 0, 0);
        $until            = $now->modify(self::WINDOW);
        $calendarTimezone = $this->readCalendarTimezone($calendar);

        /** @var array<string, list<VEvent>> $byUid */
        $byUid = [];
        foreach ($calendar->select('VEVENT') as $vevent) {
            if ($vevent instanceof VEvent && isset($vevent->UID) && '' !== (string) $vevent->UID) {
                $byUid[(string) $vevent->UID][] = $vevent;
            }
        }

        foreach ($byUid as $uid => $vevents) {
            $recurring = array_filter($vevents, static fn (VEvent $vevent): bool => isset($vevent->RRULE) || isset($vevent->RDATE));

            if ([] !== $recurring) {
                yield from $this->expand($calendar, (string) $uid, $from, $until, $calendarTimezone);
                continue;
            }

            foreach ($vevents as $vevent) {
                $candidate = $this->createCandidate($vevent, (string) $uid, $calendarTimezone);
                if (null !== $candidate && !$this->hasEnded($candidate, $from)) {
                    yield $candidate;
                }
            }
        }
    }

    /**
     * @return \Generator<EventCandidate>
     */
    private function expand(VCalendar $calendar, string $uid, \DateTimeImmutable $from, \DateTimeImmutable $until, ?string $calendarTimezone): \Generator
    {
        try {
            $iterator = new EventIterator($calendar, $uid);
            $iterator->fastForward($from);
        } catch (\Throwable) {
            return;
        }

        $count = 0;
        while ($iterator->valid() && $count < self::MAX_INSTANCES) {
            $start = $iterator->getDTStart();
            if ($start >= $until) {
                break;
            }

            $instance   = $iterator->getEventObject();
            $externalId = $uid . '#' . $start->setTimezone(new \DateTimeZone('UTC'))->format('Ymd\THis');
            $candidate  = $instance instanceof VEvent ? $this->createCandidate($instance, $externalId, $calendarTimezone) : null;
            if (null !== $candidate) {
                yield $candidate;
                ++$count;
            }

            $iterator->next();
        }
    }

    private function createCandidate(VEvent $vevent, string $externalId, ?string $calendarTimezone): ?EventCandidate
    {
        if (!isset($vevent->DTSTART)) {
            return null;
        }

        if ('PRIVATE' === mb_strtoupper((string) ($vevent->CLASS ?? ''))) {
            return null;
        }

        $title = $this->clean((string) ($vevent->SUMMARY ?? ''));
        if (null === $title) {
            return null;
        }

        [$latitude, $longitude] = $this->readGeo($vevent);
        $location               = $this->clean((string) ($vevent->LOCATION ?? ''));

        $allDay   = !$vevent->DTSTART->hasTime();
        $timezone = $this->resolveTimezone($vevent, $latitude, $longitude, $calendarTimezone);
        $zone     = null !== $timezone ? new \DateTimeZone($timezone) : new \DateTimeZone(date_default_timezone_get());

        $start = $this->toZone($vevent->DTSTART->getDateTime(), $vevent->DTSTART->isFloating(), $zone);
        $end   = $this->readEnd($vevent, $start, $zone, $allDay);

        $onlineUrl      = null;
        $place          = null;
        $attendanceMode = 'offline';
        if (null !== $location) {
            if (1 === preg_match('#^https?://\S+$#i', $location)) {
                // no address, only a link: an online event (Luma does this)
                $onlineUrl      = $location;
                $attendanceMode = 'online';
            } else {
                $place = new CandidateLocation(
                    mb_substr($location, 0, 255),
                    latitude: $latitude,
                    longitude: $longitude,
                );
            }
        }

        $description = $this->clean((string) ($vevent->DESCRIPTION ?? ''));
        $sourceUrl   = $this->readUrl($vevent, $description);

        return new EventCandidate(
            externalId: $externalId,
            titles: [$this->config->locale => mb_substr($title, 0, 255)],
            startDate: $start,
            endDate: $end,
            allDay: $allDay,
            timezone: $timezone,
            status: 'CANCELLED' === mb_strtoupper((string) ($vevent->STATUS ?? '')) ? 'cancelled' : 'scheduled',
            attendanceMode: $attendanceMode,
            location: $place,
            onlineUrl: $onlineUrl,
            languages: $this->config->languages,
            type: $this->config->type,
            categoryKeys: $this->config->categories,
            tags: $this->config->tags,
            sourceUrl: $sourceUrl,
            sourceDescription: null === $description ? null : mb_substr($description, 0, 4000),
            series: $this->config->series,
            imageMediaId: $this->config->image,
        );
    }

    /**
     * The zone of the event: the TZID of the start; for UTC times (Luma) the zone of the place by its coordinates; the
     * zone of the calendar or of the configuration otherwise.
     */
    private function resolveTimezone(VEvent $vevent, ?float $latitude, ?float $longitude, ?string $calendarTimezone): ?string
    {
        $property = $vevent->DTSTART;
        $fallback = $this->config->timezone ?? $calendarTimezone;

        if ($property->isFloating()) {
            return $fallback;
        }

        $name = $property->getDateTime()->getTimezone()->getName();
        if (\in_array($name, \DateTimeZone::listIdentifiers(), true) && 'UTC' !== $name) {
            return $name;
        }

        if ('UTC' === $name || 'Z' === $name) {
            // all-day events have a date only; their zone does not change the date
            if (null !== $this->config->timezone) {
                return $this->config->timezone;
            }

            if (null !== $latitude && null !== $longitude) {
                $guess = $this->timezoneGuesser->guess(null, $latitude, $longitude);
                if (null !== $guess) {
                    return $guess->timezone;
                }
            }

            return $calendarTimezone ?? 'UTC';
        }

        // a fixed offset such as "+02:00"
        $offset = $property->getDateTime()->getOffset();

        return $this->timezoneGuesser->guessForOffset($offset, $property->getDateTime(), null, $latitude, $longitude) ?? $fallback;
    }

    private function toZone(\DateTimeInterface $dateTime, bool $floating, \DateTimeZone $zone): \DateTimeImmutable
    {
        $immutable = \DateTimeImmutable::createFromInterface($dateTime);

        // a floating time is a wall clock time already: read it in the zone, do not convert it
        return $floating ? new \DateTimeImmutable($immutable->format('Y-m-d H:i:s'), $zone) : $immutable->setTimezone($zone);
    }

    private function readEnd(VEvent $vevent, \DateTimeImmutable $start, \DateTimeZone $zone, bool $allDay): ?\DateTimeImmutable
    {
        $end = null;
        if (isset($vevent->DTEND)) {
            $end = $this->toZone($vevent->DTEND->getDateTime(), $vevent->DTEND->isFloating(), $zone);
        } elseif (isset($vevent->DURATION)) {
            try {
                $end = $start->add(DateTimeParser::parseDuration((string) $vevent->DURATION, true));
            } catch (\Throwable) {
                $end = null;
            }
        }

        if (null === $end) {
            return null;
        }

        if ($allDay) {
            // the end of an all-day event is the day after the last day
            $lastDay = $end->modify('-1 day')->setTime(23, 59, 59);

            return $lastDay->format('Y-m-d') > $start->format('Y-m-d') ? $lastDay : null;
        }

        return $end > $start ? $end : null;
    }

    /**
     * @return array{0: float|null, 1: float|null}
     */
    private function readGeo(VEvent $vevent): array
    {
        if (!isset($vevent->GEO)) {
            return [null, null];
        }

        $parts = explode(';', (string) $vevent->GEO);
        if (2 !== \count($parts) || !is_numeric($parts[0]) || !is_numeric($parts[1])) {
            return [null, null];
        }

        return [(float) $parts[0], (float) $parts[1]];
    }

    private function readUrl(VEvent $vevent, ?string $description): ?string
    {
        $url = isset($vevent->URL) ? trim((string) $vevent->URL) : '';
        if (1 === preg_match('#^https?://\S+$#i', $url)) {
            return $url;
        }

        // Luma starts the description with "Get up-to-date information at: <url>"
        if (null !== $description && 1 === preg_match('#(https?://\S+)#i', explode("\n", $description)[0], $match)) {
            return rtrim($match[1], '.,;)');
        }

        return null;
    }

    private function readCalendarTimezone(VCalendar $calendar): ?string
    {
        $name = isset($calendar->{'X-WR-TIMEZONE'}) ? trim((string) $calendar->{'X-WR-TIMEZONE'}) : '';

        return '' !== $name && \in_array($name, \DateTimeZone::listIdentifiers(), true) ? $name : null;
    }

    private function hasEnded(EventCandidate $candidate, \DateTimeImmutable $from): bool
    {
        $last = $candidate->endDate ?? $candidate->startDate;
        if (null === $last) {
            return true;
        }

        // compare the wall clock time with today, a few hours of zone difference do not matter for a day
        return new \DateTimeImmutable($last->format('Y-m-d H:i:s'), $from->getTimezone()) < $from;
    }

    private function clean(string $text): ?string
    {
        $text = trim(html_entity_decode(strip_tags(str_replace(['\n', '\N'], "\n", $text)), \ENT_QUOTES | \ENT_HTML5, 'UTF-8'));

        return '' === $text ? null : $text;
    }
}
