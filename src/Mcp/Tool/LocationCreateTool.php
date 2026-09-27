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
class LocationCreateTool
{
    public function __construct(private readonly LocationRepository $locationRepository)
    {
    }

    /**
     * @return array<string, mixed>
     */
    #[McpTool(
        name: 'sulu_location_create',
        title: 'Create Event Location',
        description: 'Create a location for events. Returns its id, which is the "locationId" of an event. For online events use a location like "Online" without an address.',
    )]
    #[RequiresPermission(requirements: [
        new PermissionRequirement(Event::SECURITY_CONTEXT, PermissionTypes::ADD),
    ])]
    public function createLocation(
        string $name,
        ?string $street = null,
        ?string $number = null,
        ?string $postalCode = null,
        ?string $city = null,
        ?string $countryCode = null,
        ?string $email = null,
        ?string $phoneNumber = null,
        ?string $notes = null,
    ): array {
        try {
            $location = $this->locationRepository->create();
            $location->setName($name);
            $location->setStreet($street);
            $location->setNumber($number);
            $location->setPostalCode($postalCode);
            $location->setCity($city);
            $location->setCountryCode($countryCode);
            $location->setEmail($email);
            $location->setPhoneNumber($phoneNumber);
            $location->setNotes($notes);

            $location = $this->locationRepository->save($location);

            return ['success' => true, 'id' => $location->getId(), 'name' => $name];
        } catch (\Throwable $e) {
            return ['error' => \sprintf('Failed to create location: %s', $e->getMessage())];
        }
    }
}
