<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Content\DataMapper;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use Sulu\Content\Application\ContentDataMapper\DataMapper\DataMapperInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

/**
 * DataMapper for location field.
 *
 * Maps the location ID from form data to the Location entity on EventDimensionContent.
 * This handles the single_location_selection field type.
 */
class LocationDataMapper implements DataMapperInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param array{
     *     location?: int|array{id: int}|null,
     *     locationId?: int|array{id: int}|null,
     * } $data
     */
    public function map(
        DimensionContentInterface $unlocalizedDimensionContent,
        DimensionContentInterface $localizedDimensionContent,
        array $data
    ): void {
        if (!$localizedDimensionContent instanceof EventDimensionContent) {
            return;
        }

        // Handle both 'location' and 'locationId' property names
        $locationValue = $data['location'] ?? $data['locationId'] ?? null;

        // Skip if not set in data (don't override existing value)
        if (!array_key_exists('location', $data) && !array_key_exists('locationId', $data)) {
            return;
        }

        // Extract ID from various formats
        $locationId = $this->extractLocationId($locationValue);

        // Set the location
        if (null !== $locationId) {
            $location = $this->entityManager->find(Location::class, $locationId);
            if ($location) {
                $localizedDimensionContent->setLocation($location);
            }
        } else {
            // Explicitly set to null
            $localizedDimensionContent->setLocation(null);
        }
    }

    /**
     * Extract location ID from various input formats.
     *
     * The frontend may send:
     * - int: 5
     * - string: "5"
     * - array: ['id' => 5]
     * - null
     */
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