<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Manuxi\SuluEventBundle\Repository\LocationRepository;

class LocationPremisesSelection
{
    public function __construct(
        private LocationRepository $locationRepository
    ) {
    }

    public function getValues(): array
    {
        $values = [];
        $locations = $this->locationRepository->findAll();

        foreach ($locations as $location) {
            // Main Location Entry
            $values[] = [
                'name' => $location->getName(),
                'value' => (string) $location->getId(),
            ];

            // Premises Entries
            $premises = $location->getPremises();
            if (is_array($premises)) {
                foreach ($premises as $premise) {
                    if (isset($premise['name'])) {
                        // Value format: "LocationID_PremiseName"
                        // Display: "Location Name » Premise Name"
                        $values[] = [
                            'title' => $location->getName() . ' » ' . $premise['name'],
                            'name' => $location->getId() . '_' . $premise['name'],
                        ];
                    }
                }
            }
        }

        return $values;
    }
}
