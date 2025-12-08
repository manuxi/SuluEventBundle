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
     * @return array<string, Location>
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
            $mappedResult[(string) $location->getId()] = $location;
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
