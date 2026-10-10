<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\Source;

use Manuxi\SuluEventBundle\Import\CandidateLocation;
use Manuxi\SuluEventBundle\Import\EventCandidate;
use Manuxi\SuluEventBundle\Import\EventSourceInterface;
use Manuxi\SuluEventBundle\Import\Http\ImportHttpClient;
use Manuxi\SuluEventBundle\Import\Resolver\TimezoneGuesser;
use Manuxi\SuluEventBundle\Import\SourceConfig;
use Symfony\Component\Intl\Countries;

/**
 * Reads the schema.org Events (JSON-LD) of a page: also in an "@graph", in lists and in an ItemList, with every
 * subtype (BusinessEvent, SocialEvent, Festival, ...). Only the facts are taken. The description is no text of ours
 * (it goes to EventCandidate::$sourceDescription), and organizers that are persons are left out.
 */
class JsonLdSource implements EventSourceInterface
{
    /** The schema.org subtypes of Event that occur on the web. */
    private const EVENT_TYPES = [
        'Event', 'BusinessEvent', 'ChildrensEvent', 'ComedyEvent', 'CourseInstance', 'DanceEvent', 'DeliveryEvent',
        'EducationEvent', 'EventSeries', 'ExhibitionEvent', 'Festival', 'FoodEvent', 'Hackathon', 'LiteraryEvent',
        'MusicEvent', 'PublicationEvent', 'SaleEvent', 'ScreeningEvent', 'SocialEvent', 'SportsEvent', 'TheaterEvent',
        'VisualArtsEvent',
    ];

    private const STATUS = [
        'EventScheduled'   => 'scheduled',
        'EventPostponed'   => 'postponed',
        'EventRescheduled' => 'rescheduled',
        'EventCancelled'   => 'cancelled',
        'EventMovedOnline' => 'moved_online',
    ];

    private const ATTENDANCE_MODE = [
        'OfflineEventAttendanceMode' => 'offline',
        'OnlineEventAttendanceMode'  => 'online',
        'MixedEventAttendanceMode'   => 'mixed',
    ];

    /** @var \Closure(): \DateTimeImmutable */
    private \Closure $now;

    /** @var array<string, string>|null country name (english, lower case) => code */
    private ?array $countryCodes = null;

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
        $result = $this->httpClient->get($this->config->url);

        return $this->parse($result->body, $this->config->url);
    }

    /**
     * @return \Generator<EventCandidate>
     */
    public function parse(string $html, string $pageUrl): \Generator
    {
        $from = ($this->now)()->setTime(0, 0, 0);
        $seen = [];

        foreach ($this->extractJsonLdBlocks($html) as $block) {
            foreach ($this->findEvents($block) as $node) {
                $candidate = $this->createCandidate($node, $pageUrl);
                if (null === $candidate || isset($seen[$candidate->externalId]) || $this->hasEnded($candidate, $from)) {
                    continue;
                }

                $seen[$candidate->externalId] = true;

                yield $candidate;
            }
        }
    }

    /**
     * @return list<mixed> the decoded contents of all JSON-LD scripts; a script with invalid JSON is skipped
     */
    private function extractJsonLdBlocks(string $html): array
    {
        if ('' === trim($html)) {
            return [];
        }

        $previous = libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $document->loadHTML('<?xml encoding="utf-8" ?>' . $html, \LIBXML_NOWARNING | \LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $blocks = [];
        foreach ((new \DOMXPath($document))->query('//script[@type="application/ld+json"]') ?: [] as $script) {
            $json    = trim(preg_replace('/^\s*<!--|-->\s*$/', '', $script->textContent) ?? '');
            $decoded = json_decode($json, true);
            if (\JSON_ERROR_NONE !== json_last_error()) {
                // a stray control character is the usual reason, try once more without
                $decoded = json_decode((string) preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $json), true);
                if (\JSON_ERROR_NONE !== json_last_error()) {
                    continue;
                }
            }

            $blocks[] = $decoded;
        }

        return $blocks;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function findEvents(mixed $node): array
    {
        if (!\is_array($node)) {
            return [];
        }

        if (array_is_list($node)) {
            return array_merge([], ...array_map(fn (mixed $child): array => $this->findEvents($child), $node));
        }

        $events = [];
        if ($this->isEvent($node)) {
            $events[] = $node;
        }

        foreach (['@graph', 'itemListElement', 'item', 'mainEntity', 'about'] as $key) {
            if (isset($node[$key])) {
                $events = array_merge($events, $this->findEvents($node[$key]));
            }
        }

        return $events;
    }

    /**
     * @param array<string, mixed> $node
     */
    private function isEvent(array $node): bool
    {
        foreach ((array) ($node['@type'] ?? []) as $type) {
            if (\is_string($type) && \in_array(self::stripNamespace($type), self::EVENT_TYPES, true)) {
                return 'EventSeries' !== self::stripNamespace($type);
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $node
     */
    private function createCandidate(array $node, string $pageUrl): ?EventCandidate
    {
        $title      = $this->clean($this->text($node['name'] ?? null));
        $startValue = \is_string($node['startDate'] ?? null) ? trim($node['startDate']) : '';
        if (null === $title || '' === $startValue) {
            return null;
        }

        $locationNodes       = $this->asList($node['location'] ?? null);
        [$place, $onlineUrl] = $this->readLocations($locationNodes);
        $zoneHint            = null !== $place ? $this->guessZoneName($place) : null;

        $dates = $this->readDates($startValue, \is_string($node['endDate'] ?? null) ? trim($node['endDate']) : null, $place, $zoneHint);
        if (null === $dates) {
            return null;
        }

        [$isFree, $registrationUrl, $priceInfo] = $this->readOffers($this->asList($node['offers'] ?? null));
        $url                                    = $this->absoluteUrl($this->text($node['url'] ?? null), $pageUrl);
        $id                                     = $this->absoluteUrl($this->text($node['@id'] ?? null), $pageUrl);
        $externalId                             = $id ?? $url ?? sha1($title . '|' . $startValue);

        $attendanceMode                 = $this->readAttendanceMode($node['eventAttendanceMode'] ?? null, null !== $onlineUrl, null !== $place);
        [$organizerName, $organizerUrl] = $this->readOrganizer($this->asList($node['organizer'] ?? null));
        $description                    = $this->clean($this->text($node['description'] ?? null));
        if (\is_bool($node['isAccessibleForFree'] ?? null)) {
            $isFree = $node['isAccessibleForFree'];
        }

        return new EventCandidate(
            externalId: $externalId,
            titles: [$this->config->locale => mb_substr($title, 0, 255)],
            startDate: $dates['start'],
            endDate: $dates['end'],
            allDay: $dates['allDay'],
            timezone: $dates['timezone'],
            status: $this->readStatus($node['eventStatus'] ?? null),
            attendanceMode: $attendanceMode,
            location: $place,
            onlineUrl: $onlineUrl,
            registrationUrl: $registrationUrl,
            organizerName: $organizerName,
            organizerUrl: $organizerUrl,
            isFree: $isFree,
            priceInfos: null !== $priceInfo ? [$this->config->locale => $priceInfo] : [],
            languages: []    !== $this->readLanguages($node['inLanguage'] ?? null) ? $this->readLanguages($node['inLanguage'] ?? null) : $this->config->languages,
            type: $this->config->type,
            categoryKeys: $this->config->categories,
            tags: $this->config->tags,
            sourceUrl: $url ?? ($id && 1 === preg_match('#^https?://#i', $id) ? $id : null) ?? $pageUrl,
            sourceDescription: null === $description ? null : mb_substr($description, 0, 4000),
            series: $this->config->series,
        );
    }

    /**
     * @return array{start: \DateTimeImmutable, end: \DateTimeImmutable|null, allDay: bool, timezone: string|null}|null
     */
    private function readDates(string $startValue, ?string $endValue, ?CandidateLocation $place, ?string $zoneHint): ?array
    {
        $dateOnly  = 1 === preg_match('/^\d{4}-\d{2}-\d{2}$/', $startValue);
        $hasOffset = 1 === preg_match('/(Z|[+-]\d{2}:?\d{2})$/i', $startValue);

        try {
            if ($dateOnly) {
                $zone  = $this->config->timezone ?? $zoneHint;
                $start = new \DateTimeImmutable($startValue . ' 00:00:00', new \DateTimeZone($zone ?? date_default_timezone_get()));
                $end   = null;
                if (null !== $endValue && '' !== $endValue) {
                    // an end date without time is the last day
                    $end = new \DateTimeImmutable(mb_substr($endValue, 0, 10) . ' 23:59:59', $start->getTimezone());
                    $end = $end->format('Y-m-d') > $start->format('Y-m-d') ? $end : null;
                }

                return ['start' => $start, 'end' => $end, 'allDay' => true, 'timezone' => $zone];
            }

            $start = new \DateTimeImmutable($startValue);
            $end   = null !== $endValue && '' !== $endValue ? new \DateTimeImmutable($endValue) : null;
        } catch (\Exception) {
            return null;
        }

        if ($hasOffset) {
            // the offset says when it happens, the zone says what the clock shows at the place
            $zoneName = $this->config->timezone
                ?? $this->timezoneGuesser->guessForOffset($start->getOffset(), $start, $place?->countryCode, $place?->latitude, $place?->longitude);
            if (null !== $zoneName) {
                $zone = new \DateTimeZone($zoneName);

                return [
                    'start'    => $start->setTimezone($zone),
                    'end'      => null !== $end ? $end->setTimezone($zone) : null,
                    'allDay'   => false,
                    'timezone' => $zoneName,
                ];
            }

            // unknown zone: keep the clock time the page shows, the zone of the application applies
            return ['start' => $start, 'end' => $end, 'allDay' => false, 'timezone' => null];
        }

        // local time without offset: it is the clock of the place
        $zoneName = $this->config->timezone ?? $zoneHint;
        $zone     = new \DateTimeZone($zoneName ?? date_default_timezone_get());

        return [
            'start'    => new \DateTimeImmutable($start->format('Y-m-d H:i:s'), $zone),
            'end'      => null !== $end ? new \DateTimeImmutable($end->format('Y-m-d H:i:s'), $zone) : null,
            'allDay'   => false,
            'timezone' => $zoneName,
        ];
    }

    private function guessZoneName(CandidateLocation $place): ?string
    {
        return $this->timezoneGuesser->guess($place->countryCode, $place->latitude, $place->longitude)?->timezone;
    }

    /**
     * @param list<mixed> $nodes
     *
     * @return array{0: CandidateLocation|null, 1: string|null}
     */
    private function readLocations(array $nodes): array
    {
        $place     = null;
        $onlineUrl = null;

        foreach ($nodes as $node) {
            if (\is_string($node)) {
                $name = $this->clean($node);
                if (null !== $name && null === $place && 1 !== preg_match('#^https?://#i', $name)) {
                    $place = new CandidateLocation(mb_substr($name, 0, 255));
                } elseif (null !== $name && null === $onlineUrl) {
                    $onlineUrl = $name;
                }
                continue;
            }

            if (!\is_array($node)) {
                continue;
            }

            $types = array_map(static fn ($type): string => \is_string($type) ? self::stripNamespace($type) : '', (array) ($node['@type'] ?? []));
            if (\in_array('VirtualLocation', $types, true)) {
                $url = $this->text($node['url'] ?? null);
                if (null !== $url && null === $onlineUrl && 1 === preg_match('#^https?://\S+$#i', $url)) {
                    $onlineUrl = $url;
                }
                continue;
            }

            if (null === $place) {
                $place = $this->readPlace($node);
            }
        }

        return [$place, $onlineUrl];
    }

    /**
     * @param array<string, mixed> $node
     */
    private function readPlace(array $node): ?CandidateLocation
    {
        $address = $node['address'] ?? null;
        $street  = $postalCode = $city = $state = $country = null;
        $name    = $this->clean($this->text($node['name'] ?? null));

        if (\is_string($address)) {
            $name ??= $this->clean($address);
        } elseif (\is_array($address)) {
            $street     = $this->clean($this->text($address['streetAddress'] ?? null));
            $postalCode = $this->clean($this->text($address['postalCode'] ?? null));
            $city       = $this->clean($this->text($address['addressLocality'] ?? null));
            $state      = $this->clean($this->text($address['addressRegion'] ?? null));
            $country    = $this->readCountryCode($address['addressCountry'] ?? null);
        }

        $name ??= $city;
        if (null === $name) {
            return null;
        }

        $geo       = $node['geo'] ?? null;
        $latitude  = \is_array($geo) && is_numeric($geo['latitude'] ?? null) ? (float) $geo['latitude'] : null;
        $longitude = \is_array($geo) && is_numeric($geo['longitude'] ?? null) ? (float) $geo['longitude'] : null;

        return new CandidateLocation(mb_substr($name, 0, 255), $street, null, $postalCode, $city, $state, $country, $latitude, $longitude);
    }

    private function readCountryCode(mixed $value): ?string
    {
        if (\is_array($value)) {
            $value = $value['name'] ?? $value['alternateName'] ?? null;
        }

        $value = \is_string($value) ? trim($value) : '';
        if ('' === $value) {
            return null;
        }

        if (1 === preg_match('/^[A-Za-z]{2}$/', $value)) {
            return mb_strtoupper($value);
        }

        if (null === $this->countryCodes) {
            $this->countryCodes = [];
            foreach (Countries::getNames('en') as $code => $name) {
                $this->countryCodes[mb_strtolower($name)] = (string) $code;
            }
        }

        return $this->countryCodes[mb_strtolower($value)] ?? null;
    }

    /**
     * @param list<mixed> $offers
     *
     * @return array{0: bool|null, 1: string|null, 2: string|null} free, registration url, price text
     */
    private function readOffers(array $offers): array
    {
        $isFree    = null;
        $url       = null;
        $priceInfo = null;

        foreach ($offers as $offer) {
            if (!\is_array($offer)) {
                continue;
            }

            $offerUrl = $this->text($offer['url'] ?? null);
            if (null === $url && null !== $offerUrl && 1 === preg_match('#^https?://\S+$#i', $offerUrl)) {
                $url = $offerUrl;
            }

            $price = $offer['price'] ?? $offer['lowPrice'] ?? null;
            if (is_numeric($price)) {
                if (0.0 === (float) $price) {
                    $isFree = true;
                } elseif (null === $priceInfo) {
                    $isFree ??= false;
                    $currency  = $this->text($offer['priceCurrency'] ?? null);
                    $priceInfo = null !== $currency ? rtrim(rtrim(number_format((float) $price, 2, '.', ''), '0'), '.') . ' ' . $currency : null;
                }
            }
        }

        return [$isFree, $url, $priceInfo];
    }

    /**
     * Persons are not taken over, only organizations.
     *
     * @param list<mixed> $organizers
     *
     * @return array{0: string|null, 1: string|null}
     */
    private function readOrganizer(array $organizers): array
    {
        foreach ($organizers as $organizer) {
            if (!\is_array($organizer)) {
                continue;
            }

            $types = array_map(static fn ($type): string => \is_string($type) ? self::stripNamespace($type) : '', (array) ($organizer['@type'] ?? []));
            if (\in_array('Person', $types, true)) {
                continue;
            }

            $name = $this->clean($this->text($organizer['name'] ?? null));
            $url  = $this->text($organizer['url'] ?? null);
            $url  = null !== $url && 1 === preg_match('#^https?://\S+$#i', $url) ? $url : null;
            if (null !== $name || null !== $url) {
                return [null !== $name ? mb_substr($name, 0, 255) : null, $url];
            }
        }

        return [null, null];
    }

    private function readStatus(mixed $value): string
    {
        $name = \is_string($value) ? self::stripNamespace($value) : '';

        return self::STATUS[$name] ?? 'scheduled';
    }

    private function readAttendanceMode(mixed $value, bool $hasOnlineUrl, bool $hasPlace): string
    {
        $name = \is_string($value) ? self::stripNamespace($value) : '';
        if (isset(self::ATTENDANCE_MODE[$name])) {
            return self::ATTENDANCE_MODE[$name];
        }

        // not told: a link and no place is an online event
        return $hasOnlineUrl && !$hasPlace ? 'online' : 'offline';
    }

    /**
     * @return string[]
     */
    private function readLanguages(mixed $value): array
    {
        $codes = [];
        foreach ($this->asList($value) as $language) {
            if (\is_array($language)) {
                $language = $language['alternateName'] ?? $language['name'] ?? null;
            }

            if (\is_string($language) && 1 === preg_match('/^([A-Za-z]{2,3})(?:[-_][A-Za-z]{2,4})?$/', trim($language), $match)) {
                $codes[mb_strtolower($match[1])] = mb_strtolower($match[1]);
            }
        }

        return array_values($codes);
    }

    private function hasEnded(EventCandidate $candidate, \DateTimeImmutable $from): bool
    {
        $last = $candidate->endDate ?? $candidate->startDate;
        if (null === $last) {
            return true;
        }

        return new \DateTimeImmutable($last->format('Y-m-d H:i:s'), $from->getTimezone()) < $from;
    }

    /**
     * @return list<mixed>
     */
    private function asList(mixed $value): array
    {
        if (null === $value || '' === $value) {
            return [];
        }

        return \is_array($value) && array_is_list($value) ? $value : [$value];
    }

    /**
     * A text value: a string, or the first of a list / language-tagged value.
     */
    private function text(mixed $value): ?string
    {
        if (\is_array($value)) {
            $value = array_is_list($value) ? ($value[0] ?? null) : ($value['@value'] ?? null);
            if (\is_array($value)) {
                return $this->text($value);
            }
        }

        return \is_string($value) || is_numeric($value) ? trim((string) $value) : null;
    }

    private function clean(?string $text): ?string
    {
        if (null === $text) {
            return null;
        }

        $text = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($text), \ENT_QUOTES | \ENT_HTML5, 'UTF-8')));

        return '' === $text ? null : $text;
    }

    private function absoluteUrl(?string $value, string $pageUrl): ?string
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (1 === preg_match('#^https?://#i', $value)) {
            return $value;
        }

        $parts = parse_url($pageUrl);
        if (!isset($parts['scheme'], $parts['host'])) {
            return $value;
        }

        $base = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
        if (str_starts_with($value, '#')) {
            return $pageUrl . (str_contains($pageUrl, '#') ? '' : $value);
        }

        return str_starts_with($value, '/') ? $base . $value : $base . rtrim(\dirname($parts['path'] ?? '/'), '/') . '/' . $value;
    }

    private static function stripNamespace(string $value): string
    {
        return preg_replace('#^.*[/:]#', '', $value) ?? $value;
    }
}
