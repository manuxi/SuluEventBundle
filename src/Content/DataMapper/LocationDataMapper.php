<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\DataMapper;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use Sulu\Content\Application\ContentDataMapper\DataMapper\DataMapperInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class LocationDataMapper implements DataMapperInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }
    public function map(
        DimensionContentInterface $unlocalizedDimensionContent,
        DimensionContentInterface $localizedDimensionContent,
        array $data,
    ): void {
        if (!$localizedDimensionContent instanceof EventDimensionContent) {
            return;
        }

        $locationValue = $data['location'] ?? $data['locationId'] ?? null;

        if (!array_key_exists('location', $data) && !array_key_exists('locationId', $data)) {
            return;
        }

        $locationId = $this->extractLocationId($locationValue);

        if (null !== $locationId) {
            $location = $this->entityManager->find(Location::class, $locationId);
            if ($location) {
                $localizedDimensionContent->setLocation($location);
            }
        } else {
            $localizedDimensionContent->setLocation(null);
        }
    }
    private function extractLocationId(mixed $value): ?int
    {
        if (null === $value) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        if (is_array($value) && isset($value['id'])) {
            return (int) $value['id'];
        }

        return null;
    }
}