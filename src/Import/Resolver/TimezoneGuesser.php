<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\Resolver;

/**
 * Finds the IANA time zone of a place from its country and, if known, its coordinates. The zones of PHP carry a
 * reference point (usually the main city), the nearest one inside the country wins.
 */
class TimezoneGuesser
{
    /** The zone of the capital for countries with several zones. */
    private const CAPITAL_ZONES = [
        'US' => 'America/New_York',
        'CA' => 'America/Toronto',
        'AU' => 'Australia/Sydney',
        'BR' => 'America/Sao_Paulo',
        'RU' => 'Europe/Moscow',
        'MX' => 'America/Mexico_City',
        'CN' => 'Asia/Shanghai',
        'ID' => 'Asia/Jakarta',
        'KZ' => 'Asia/Almaty',
        'CL' => 'America/Santiago',
        'AR' => 'America/Argentina/Buenos_Aires',
        'PT' => 'Europe/Lisbon',
        'ES' => 'Europe/Madrid',
        'UA' => 'Europe/Kyiv',
        'MN' => 'Asia/Ulaanbaatar',
        'CD' => 'Africa/Kinshasa',
        'PF' => 'Pacific/Tahiti',
        'FM' => 'Pacific/Pohnpei',
        'KI' => 'Pacific/Tarawa',
        'NZ' => 'Pacific/Auckland',
        'GL' => 'America/Nuuk',
        'EC' => 'America/Guayaquil',
    ];

    /** @var array<string, array{country: string, lat: float, lon: float}>|null */
    private ?array $points = null;

    /**
     * @param string|null $countryCode ISO 3166-1 alpha-2
     */
    public function guess(?string $countryCode, ?float $latitude = null, ?float $longitude = null): ?TimezoneGuess
    {
        $countryCode = null !== $countryCode && 1 === preg_match('/^[A-Za-z]{2}$/', $countryCode) ? mb_strtoupper($countryCode) : null;
        $zones       = null !== $countryCode ? $this->zonesOfCountry($countryCode) : [];

        if (1 === \count($zones) || ([] !== $zones && $this->sameOffsets($zones))) {
            // one zone, or zones that never differ in their offset (e.g. Berlin and Busingen)
            return new TimezoneGuess(self::CAPITAL_ZONES[$countryCode] ?? $zones[0], true);
        }

        if (null !== $latitude && null !== $longitude) {
            $nearest = $this->nearest($latitude, $longitude, [] !== $zones ? $zones : null);
            if (null !== $nearest) {
                return new TimezoneGuess($nearest, false);
            }
        }

        if ([] !== $zones) {
            return new TimezoneGuess(self::CAPITAL_ZONES[$countryCode] ?? $zones[0], false);
        }

        return null;
    }

    /**
     * The zone near the coordinates whose offset at the given moment is the given one (for sources that only give
     * "+02:00"). Null if no zone fits.
     *
     * @param string|null $countryCode limits the search to a country
     */
    public function guessForOffset(int $offsetSeconds, \DateTimeInterface $at, ?string $countryCode = null, ?float $latitude = null, ?float $longitude = null): ?string
    {
        $countryCode = null !== $countryCode && 1 === preg_match('/^[A-Za-z]{2}$/', $countryCode) ? mb_strtoupper($countryCode) : null;
        $candidates  = null !== $countryCode ? $this->zonesOfCountry($countryCode) : array_keys($this->getPoints());

        $matching = array_values(array_filter(
            $candidates,
            static fn (string $zone): bool => (new \DateTimeZone($zone))->getOffset(\DateTimeImmutable::createFromInterface($at)) === $offsetSeconds
        ));
        if ([] === $matching) {
            return null;
        }

        if (null !== $countryCode && $this->sameOffsets($candidates)) {
            // zones that never differ (Berlin and Busingen): the main zone of the country, not the nearest point
            return self::CAPITAL_ZONES[$countryCode] ?? $matching[0];
        }

        if (null !== $latitude && null !== $longitude) {
            return $this->nearest($latitude, $longitude, $matching) ?? $matching[0];
        }

        if (null !== $countryCode && isset(self::CAPITAL_ZONES[$countryCode]) && \in_array(self::CAPITAL_ZONES[$countryCode], $matching, true)) {
            return self::CAPITAL_ZONES[$countryCode];
        }

        return $matching[0];
    }

    /**
     * @param string[] $zones
     */
    private function sameOffsets(array $zones): bool
    {
        $signatures = [];
        foreach ($zones as $zone) {
            $timezone          = new \DateTimeZone($zone);
            $signatures[$zone] = $timezone->getOffset(new \DateTimeImmutable('2027-01-15 12:00:00 UTC')) . '/' . $timezone->getOffset(new \DateTimeImmutable('2027-07-15 12:00:00 UTC'));
        }

        return 1 === \count(array_unique($signatures));
    }

    /**
     * @return string[]
     */
    private function zonesOfCountry(string $countryCode): array
    {
        $zones = @\DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $countryCode);

        return \is_array($zones) ? $zones : [];
    }

    /**
     * @param string[]|null $limitTo
     */
    private function nearest(float $latitude, float $longitude, ?array $limitTo): ?string
    {
        $best         = null;
        $bestDistance = \INF;
        foreach ($this->getPoints() as $zone => $point) {
            if (null !== $limitTo && !\in_array($zone, $limitTo, true)) {
                continue;
            }

            $distance = $this->distance($latitude, $longitude, $point['lat'], $point['lon']);
            if ($distance < $bestDistance) {
                $best         = $zone;
                $bestDistance = $distance;
            }
        }

        return $best;
    }

    /**
     * @return array<string, array{country: string, lat: float, lon: float}>
     */
    private function getPoints(): array
    {
        if (null === $this->points) {
            $this->points = [];
            foreach (\DateTimeZone::listIdentifiers() as $zone) {
                $location = (new \DateTimeZone($zone))->getLocation();
                if (!\is_array($location) || !isset($location['latitude'], $location['longitude']) || 'UTC' === $zone) {
                    continue;
                }

                $this->points[$zone] = [
                    'country' => (string) ($location['country_code'] ?? '??'),
                    'lat'     => (float) $location['latitude'],
                    'lon'     => (float) $location['longitude'],
                ];
            }
        }

        return $this->points;
    }

    /**
     * Haversine distance in kilometres.
     */
    private function distance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a    = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return 6371.0 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
