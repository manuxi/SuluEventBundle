<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Manuxi\SuluEventBundle\Entity\AttendanceMode;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\EventStatus;
use Manuxi\SuluEventBundle\Entity\Location;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The schema.org "Event" of an event as an array (no @context, no script tag): the project puts it into its own
 * structured data graph or encodes it as JSON-LD. Only what is known is included; nothing is made up, e.g. there is
 * no price without a currency, the price text goes into the description of the offer.
 */
class EventSchemaBuilder
{
    private const STATUS = [
        'scheduled'    => 'EventScheduled',
        'postponed'    => 'EventPostponed',
        'rescheduled'  => 'EventRescheduled',
        'cancelled'    => 'EventCancelled',
        'moved_online' => 'EventMovedOnline',
    ];

    private const ATTENDANCE_MODE = [
        'offline' => 'OfflineEventAttendanceMode',
        'online'  => 'OnlineEventAttendanceMode',
        'mixed'   => 'MixedEventAttendanceMode',
    ];

    public function __construct(
        private readonly EventTimezoneResolver $timezoneResolver,
        private readonly EventUrlResolver $urlResolver,
        private readonly MediaManagerInterface $mediaManager,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(EventDimensionContent $dimensionContent): array
    {
        $locale = (string) $dimensionContent->getLocale();
        $url    = $this->urlResolver->findUrl($dimensionContent, $locale);

        $schema = [
            '@type' => 'Event',
            'name'  => (string) $dimensionContent->getTitle(),
        ];

        $summary = $this->clean($dimensionContent->getSummary());
        if (null !== $summary) {
            $schema['description'] = $summary;
        }

        if (null !== $url) {
            $schema['url'] = $url;
        }

        $schema = [...$schema, ...$this->buildDates($dimensionContent)];

        $schema['eventStatus']         = 'https://schema.org/' . (self::STATUS[$dimensionContent->getStatus()] ?? self::STATUS[EventStatus::DEFAULT->value]);
        $schema['eventAttendanceMode'] = 'https://schema.org/' . (self::ATTENDANCE_MODE[$dimensionContent->getAttendanceMode()] ?? self::ATTENDANCE_MODE[AttendanceMode::DEFAULT->value]);

        $location = $this->buildLocation($dimensionContent);
        if (null !== $location) {
            $schema['location'] = $location;
        }

        $organizer = $this->buildOrganizer($dimensionContent);
        if (null !== $organizer) {
            $schema['organizer'] = $organizer;
        }

        $offer = $this->buildOffer($dimensionContent);
        if (null !== $offer) {
            $schema['offers'] = $offer;
        }

        if (null !== $dimensionContent->getIsFree()) {
            $schema['isAccessibleForFree'] = $dimensionContent->getIsFree();
        }

        $languages = $dimensionContent->getLanguages();
        if (null !== $languages && [] !== $languages) {
            $schema['inLanguage'] = 1 === \count($languages) ? $languages[0] : $languages;
        }

        $image = $this->findImageUrl($dimensionContent, $locale, $url);
        if (null !== $image) {
            $schema['image'] = $image;
        }

        return $schema;
    }

    /**
     * All-day events carry dates, the others the time of day with the offset of the zone of the event.
     *
     * @return array<string, string>
     */
    private function buildDates(EventDimensionContent $dimensionContent): array
    {
        $start = $dimensionContent->getStartDate();
        if (null === $start) {
            return [];
        }

        $end   = $dimensionContent->getEndDate();
        $dates = [];

        if ($dimensionContent->getAllDay()) {
            $dates['startDate'] = $start->format('Y-m-d');
            if (null !== $end && $end->format('Y-m-d') > $start->format('Y-m-d')) {
                $dates['endDate'] = $end->format('Y-m-d');
            }

            return $dates;
        }

        $startInstant       = $this->timezoneResolver->toInstant($start, $dimensionContent);
        $dates['startDate'] = $startInstant->format('c');
        if (null !== $end) {
            $endInstant = $this->timezoneResolver->toInstant($end, $dimensionContent);
            if ($endInstant > $startInstant) {
                $dates['endDate'] = $endInstant->format('c');
            }
        }

        return $dates;
    }

    /**
     * A place for events on site, a virtual location for online events, both for mixed ones.
     *
     * @return array<string, mixed>|list<array<string, mixed>>|null
     */
    private function buildLocation(EventDimensionContent $dimensionContent): ?array
    {
        $mode      = $dimensionContent->getAttendanceMode();
        $place     = null !== $dimensionContent->getLocation() ? $this->buildPlace($dimensionContent->getLocation()) : null;
        $onlineUrl = $dimensionContent->getOnlineUrl();
        $virtual   = AttendanceMode::Offline->value !== $mode && null !== $onlineUrl
            ? ['@type' => 'VirtualLocation', 'url' => $onlineUrl]
            : null;

        if (AttendanceMode::Mixed->value === $mode) {
            $locations = array_values(array_filter([$place, $virtual]));

            return match (\count($locations)) {
                0       => null,
                1       => $locations[0],
                default => $locations,
            };
        }

        if (AttendanceMode::Online->value === $mode) {
            return $virtual ?? $place;
        }

        return $place;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPlace(Location $location): array
    {
        $place = ['@type' => 'Place', 'name' => $location->getName()];

        $address = array_filter([
            '@type'           => 'PostalAddress',
            'streetAddress'   => $this->clean(trim((string) $location->getStreet() . ' ' . (string) $location->getNumber())),
            'postalCode'      => $this->clean($location->getPostalCode()),
            'addressLocality' => $this->clean($location->getCity()),
            'addressRegion'   => $this->clean($location->getState()),
            'addressCountry'  => $this->clean($location->getCountryCode()),
        ], static fn (mixed $value): bool => null !== $value);
        if (\count($address) > 1) {
            $place['address'] = $address;
        }

        if (null !== $location->getLatitude() && null !== $location->getLongitude()) {
            $place['geo'] = [
                '@type'     => 'GeoCoordinates',
                'latitude'  => $location->getLatitude(),
                'longitude' => $location->getLongitude(),
            ];
        }

        return $place;
    }

    /**
     * @return array<string, string>|null
     */
    private function buildOrganizer(EventDimensionContent $dimensionContent): ?array
    {
        $name = $dimensionContent->getOrganizerName();
        $url  = $dimensionContent->getOrganizerUrl();
        if (null === $name && null === $url) {
            return null;
        }

        return array_filter(['@type' => 'Organization', 'name' => $name, 'url' => $url], static fn (?string $value): bool => null !== $value);
    }

    /**
     * Only a registration link makes an offer; the price text stays text.
     *
     * @return array<string, string>|null
     */
    private function buildOffer(EventDimensionContent $dimensionContent): ?array
    {
        $url = $dimensionContent->getRegistrationUrl();
        if (null === $url) {
            return null;
        }

        return array_filter([
            '@type'       => 'Offer',
            'url'         => $url,
            'description' => true === $dimensionContent->getIsFree() ? null : $dimensionContent->getPriceInfo(),
        ], static fn (?string $value): bool => null !== $value);
    }

    private function findImageUrl(EventDimensionContent $dimensionContent, string $locale, ?string $pageUrl): ?string
    {
        $mediaId = $dimensionContent->getImage()?->getId() ?? ($dimensionContent->getExcerptImage()['id'] ?? null);
        if (!\is_int($mediaId) && !(\is_string($mediaId) && ctype_digit($mediaId))) {
            return null;
        }

        try {
            $path = $this->mediaManager->getById((int) $mediaId, $locale)->getUrl();
        } catch (\Throwable) {
            return null;
        }

        return $this->makeAbsolute($path, $pageUrl);
    }

    private function makeAbsolute(string $path, ?string $pageUrl): ?string
    {
        if (1 === preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $base = null;
        if (null !== $pageUrl) {
            $scheme = parse_url($pageUrl, \PHP_URL_SCHEME);
            $host   = parse_url($pageUrl, \PHP_URL_HOST);
            $port   = parse_url($pageUrl, \PHP_URL_PORT);
            if (\is_string($scheme) && \is_string($host)) {
                $base = $scheme . '://' . $host . (\is_int($port) ? ':' . $port : '');
            }
        }
        $base ??= $this->requestStack->getCurrentRequest()?->getSchemeAndHttpHost();

        return null === $base ? null : $base . '/' . ltrim($path, '/');
    }

    private function clean(?string $text): ?string
    {
        if (null === $text) {
            return null;
        }

        $text = trim(html_entity_decode(strip_tags($text), \ENT_QUOTES | \ENT_HTML5, 'UTF-8'));

        return '' === $text ? null : $text;
    }
}
