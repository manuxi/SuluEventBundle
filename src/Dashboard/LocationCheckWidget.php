<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Dashboard;

use Manuxi\SuluDashboardBundle\Widget\DashboardWidgetInterface;
use Manuxi\SuluDashboardBundle\Widget\WidgetColumns;
use Manuxi\SuluDashboardBundle\Widget\WidgetTypes;
use Manuxi\SuluEventBundle\Admin\LocationAdmin;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\Location;

/**
 * Tile for the dashboard of manuxi/sulu-dashboard-bundle: locations without coordinates or without a complete address
 * (street, postal code, city). Without findings the tile says so.
 *
 * Locations are not translated, the toolbar language does not matter. Locations and events
 * share one security context in the admin.
 */
final class LocationCheckWidget implements DashboardWidgetInterface
{
    public const KEY = 'sulu_event_locations';

    public function __construct(private readonly EventDashboardReader $reader)
    {
    }

    public function getKey(): string
    {
        return self::KEY;
    }

    public function getTitle(): string
    {
        return 'sulu_event_dashboard.locations_title';
    }

    public function getType(): string
    {
        return WidgetTypes::LIST;
    }

    public function getSecurityContext(): string
    {
        return Event::SECURITY_CONTEXT;
    }

    public function getColumn(): string
    {
        return WidgetColumns::SIDE;
    }

    public function getPriority(): int
    {
        return 10;
    }

    public function getData(string $locale): array
    {
        $rows = [];

        foreach ($this->reader->locations() as $location) {
            $missing = $this->missing($location);
            if ([] === $missing) {
                continue;
            }

            // one finding is named, several are counted (a translation key cannot join other keys)
            $meta = 1 === \count($missing)
                ? 'sulu_event_dashboard.missing_' . $missing[0]
                : ['key' => 'sulu_event_dashboard.missing_several', 'parameters' => ['count' => \count($missing)]];

            $rows[] = [
                'title' => (string) ($location->getName() ?? $location->getId()),
                'meta' => $meta,
                'link' => [
                    'view' => LocationAdmin::EDIT_FORM_VIEW,
                    'attributes' => ['id' => $location->getId()],
                ],
            ];
        }

        return [
            'empty' => 'sulu_event_dashboard.locations_ok',
            'rows' => $rows,
        ];
    }

    /**
     * @return list<string> "coordinates", "street", "postal_code", "city"
     */
    private function missing(Location $location): array
    {
        $missing = [];

        if (!$this->hasCoordinates($location)) {
            $missing[] = 'coordinates';
        }
        if ($this->isBlank($location->getStreet())) {
            $missing[] = 'street';
        }
        if ($this->isBlank($location->getPostalCode())) {
            $missing[] = 'postal_code';
        }
        if ($this->isBlank($location->getCity())) {
            $missing[] = 'city';
        }

        return $missing;
    }

    private function hasCoordinates(Location $location): bool
    {
        // read the array directly: the typed getters would throw for a value stored as text
        $map = $location->getLocation() ?? [];
        $latitude = $map['lat'] ?? null;
        $longitude = $map['long'] ?? null;

        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return false;
        }

        // 0/0 is what an untouched map picker leaves behind
        return 0.0 !== (float) $latitude || 0.0 !== (float) $longitude;
    }

    private function isBlank(?string $value): bool
    {
        return null === $value || '' === trim($value);
    }
}
