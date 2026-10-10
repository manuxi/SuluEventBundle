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
        description: 'Create a location for events. Returns its id, which is the "locationId" of an event. Online events do not need a location (set attendanceMode "online" on the event). Give the IANA "timezone" of the place (e.g. "America/Los_Angeles") for places outside the time zone of the website: the event times are read in it.',
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
        ?string $timezone = null,
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
            $location->setTimezone($timezone);

            $location = $this->locationRepository->save($location);

            return ['success' => true, 'id' => $location->getId(), 'name' => $name];
        } catch (\Throwable $e) {
            return ['error' => \sprintf('Failed to create location: %s', $e->getMessage())];
        }
    }
}
