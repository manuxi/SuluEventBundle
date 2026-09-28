<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\ResourceLoader;

use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use Sulu\Content\Application\ResourceLoader\Loader\ResourceLoaderInterface;

class LocationResourceLoader implements ResourceLoaderInterface
{
    public const RESOURCE_LOADER_KEY = 'locations';
    public function __construct(
        private LocationRepository $locationRepository,
    ) {
    }

    /**
     * @param string[] $ids
     * @param array<string, mixed> $params
     * @return array<string, array<string, mixed>>
     */
    public function load(array $ids, ?string $locale, array $params = []): array
    {
        if (empty($ids)) {
            return [];
        }

        $intIds = \array_map('intval', $ids);

        $result = $this->locationRepository->findBy(['id' => $intIds]);

        $mappedResult = [];
        foreach ($result as $location) {
            // Location is a plain entity, not a ContentRichEntityInterface: the generic
            // ContentResolver would otherwise embed the raw object, which serializes to "{}"
            // (json_encode only sees public properties). Return a plain array instead.
            $mappedResult[(string) $location->getId()] = [
                'id' => $location->getId(),
                'name' => $location->getName(),
                'street' => $location->getStreet(),
                'number' => $location->getNumber(),
                'postalCode' => $location->getPostalCode(),
                'city' => $location->getCity(),
                'state' => $location->getState(),
                'countryCode' => $location->getCountryCode(),
                'notes' => $location->getNotes(),
                'email' => $location->getEmail(),
                'phoneNumber' => $location->getPhoneNumber(),
                'image' => $location->getImage() ? ['id' => $location->getImage()->getId()] : null,
                'latitude' => $location->getLatitude(),
                'longitude' => $location->getLongitude(),
                'link' => $location->getLink(),
            ];
        }

        return $mappedResult;
    }

    public static function getKey(): string
    {
        return self::RESOURCE_LOADER_KEY;
    }

    public static function getResourceKey(): string
    {
        return Location::RESOURCE_KEY;
    }
}
