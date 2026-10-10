<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Import\CandidateLocation;

/**
 * Finds the existing location of a place told by a source: by name and city, or by coordinates (closer than 300 m).
 * A place that is not known yet is created, with the time zone of its country / coordinates, and reported as new so
 * the editors can look at it.
 */
class LocationResolver
{
    public const MAX_DISTANCE_METERS = 300.0;

    /** @var list<Location>|null */
    private ?array $locations = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TimezoneGuesser $timezoneGuesser,
    ) {
    }

    /**
     * @param bool $persist false for a dry run: a new location is created in memory only
     */
    public function resolve(CandidateLocation $candidate, bool $persist = true, ?string $fallbackTimezone = null): ResolvedLocation
    {
        $existing = $this->find($candidate);
        if (null !== $existing) {
            return new ResolvedLocation($existing, false);
        }

        $location = new Location();
        $location->setName($candidate->name);
        $location->setStreet($this->nullIfEmpty($candidate->street));
        $location->setNumber($this->nullIfEmpty($candidate->number));
        $location->setPostalCode($this->nullIfEmpty($candidate->postalCode));
        $location->setCity($this->nullIfEmpty($candidate->city));
        $location->setState($this->nullIfEmpty($candidate->state));
        $location->setCountryCode(null !== $candidate->countryCode ? mb_strtoupper($candidate->countryCode) : null);
        if (null !== $candidate->latitude && null !== $candidate->longitude) {
            $location->setLocation(['lat' => $candidate->latitude, 'long' => $candidate->longitude, 'zoom' => 16]);
        }

        $guess    = $this->timezoneGuesser->guess($candidate->countryCode, $candidate->latitude, $candidate->longitude);
        $timezone = $guess?->timezone ?? $fallbackTimezone;
        if (null !== $timezone) {
            $location->setTimezone($timezone);
        }

        if ($persist) {
            $this->entityManager->persist($location);
        }

        $this->getLocations();
        $this->locations[] = $location;

        return new ResolvedLocation($location, true, $guess?->certain ?? false);
    }

    /**
     * Forgets the locations read so far (after the entity manager was cleared).
     */
    public function reset(): void
    {
        $this->locations = null;
    }

    private function find(CandidateLocation $candidate): ?Location
    {
        $name = $this->normalize($candidate->name);
        $city = null !== $candidate->city ? $this->normalize($candidate->city) : null;

        $sameName = array_values(array_filter(
            $this->getLocations(),
            fn (Location $location): bool => $this->normalize((string) $location->getName()) === $name
        ));

        if (null !== $city && '' !== $city) {
            foreach ($sameName as $location) {
                if ($this->normalize((string) $location->getCity()) === $city) {
                    return $location;
                }
            }
        } elseif (1 === \count($sameName)) {
            // no city told: the name is enough only if it is unambiguous
            return $sameName[0];
        }

        if (null !== $candidate->latitude && null !== $candidate->longitude) {
            $nearest         = null;
            $nearestDistance = self::MAX_DISTANCE_METERS;
            foreach ($this->getLocations() as $location) {
                if (null === $location->getLatitude() || null === $location->getLongitude()) {
                    continue;
                }

                $distance = self::distanceInMeters($candidate->latitude, $candidate->longitude, $location->getLatitude(), $location->getLongitude());
                if ($distance < $nearestDistance) {
                    $nearest         = $location;
                    $nearestDistance = $distance;
                }
            }

            return $nearest;
        }

        return null;
    }

    /**
     * @return list<Location>
     */
    private function getLocations(): array
    {
        if (null === $this->locations) {
            $this->locations = array_values($this->entityManager->getRepository(Location::class)->findAll());
        }

        return $this->locations;
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', $value)));
    }

    private function nullIfEmpty(?string $value): ?string
    {
        $value = null !== $value ? trim($value) : null;

        return '' === $value ? null : $value;
    }

    public static function distanceInMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a    = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return 6371000.0 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
