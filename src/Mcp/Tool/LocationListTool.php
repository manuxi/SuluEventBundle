<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Mcp\Tool;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use Mcp\Capability\Attribute\McpTool;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Mcp\Domain\Security\PermissionRequirement;
use Sulu\Mcp\Domain\Security\RequiresPermission;

/**
 * @internal
 */
class LocationListTool
{
    public function __construct(private readonly LocationRepository $locationRepository)
    {
    }

    /**
     * @return array<string, mixed>
     */
    #[McpTool(
        name: 'sulu_location_list',
        title: 'List Event Locations',
        description: 'List the locations events can take place at (id, name, address). The id is the "locationId" of an event.',
    )]
    #[RequiresPermission(requirements: [
        new PermissionRequirement(Event::SECURITY_CONTEXT, PermissionTypes::VIEW),
    ])]
    public function listLocations(): array
    {
        try {
            $locations = [];
            foreach ($this->locationRepository->findBy([], ['name' => 'ASC']) as $location) {
                $locations[] = [
                    'id' => $location->getId(),
                    'name' => $location->getName(),
                    'street' => $location->getStreet(),
                    'number' => $location->getNumber(),
                    'postalCode' => $location->getPostalCode(),
                    'city' => $location->getCity(),
                    'countryCode' => $location->getCountryCode(),
                ];
            }

            return ['locations' => $locations, 'total' => \count($locations)];
        } catch (\Throwable $e) {
            return ['error' => \sprintf('Failed to list locations: %s', $e->getMessage())];
        }
    }
}
