<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\DataFixtures\Location;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Manuxi\SuluEventBundle\Entity\Location;

/**
 * Fixture for creating sample locations.
 *
 * Usage: bin/console doctrine:fixtures:load --group=locations
 */
class LocationFixture extends Fixture implements FixtureGroupInterface
{
    public const REFERENCE_PREFIX = 'location-';

    public static function getGroups(): array
    {
        return ['locations', 'events'];
    }

    public function load(ObjectManager $manager): void
    {
        $locationData = [
            [
                'name' => 'Convention Center',
                'street' => 'Main Street',
                'number' => '100',
                'postalCode' => '10001',
                'city' => 'New York',
                'state' => 'NY',
                'countryCode' => 'US',
                'notes' => 'Large convention center with multiple halls',
                'email' => 'info@convention-center.example.com',
                'phoneNumber' => '+1 212 555 0100',
            ],
            [
                'name' => 'Tech Hub',
                'street' => 'Innovation Way',
                'number' => '42',
                'postalCode' => '94105',
                'city' => 'San Francisco',
                'state' => 'CA',
                'countryCode' => 'US',
                'notes' => 'Modern tech campus with co-working spaces',
                'email' => 'contact@techhub.example.com',
                'phoneNumber' => '+1 415 555 0042',
            ],
            [
                'name' => 'Business Park Conference Center',
                'street' => 'Corporate Drive',
                'number' => '500',
                'postalCode' => '60601',
                'city' => 'Chicago',
                'state' => 'IL',
                'countryCode' => 'US',
                'notes' => 'Professional conference facilities',
                'email' => 'events@businesspark.example.com',
                'phoneNumber' => '+1 312 555 0500',
            ],
            [
                'name' => 'University Hall',
                'street' => 'Academic Lane',
                'number' => '1',
                'postalCode' => '02138',
                'city' => 'Cambridge',
                'state' => 'MA',
                'countryCode' => 'US',
                'notes' => 'Historic academic venue',
                'email' => 'hall@university.example.edu',
                'phoneNumber' => '+1 617 555 0001',
            ],
            [
                'name' => 'Creative Space',
                'street' => 'Art District',
                'number' => '25',
                'postalCode' => '90012',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'countryCode' => 'US',
                'notes' => 'Artistic venue for creative events',
                'email' => 'hello@creativespace.example.com',
                'phoneNumber' => '+1 213 555 0025',
            ],
            [
                'name' => 'Messezentrum',
                'street' => 'Messestraße',
                'number' => '1',
                'postalCode' => '80339',
                'city' => 'München',
                'state' => 'Bayern',
                'countryCode' => 'DE',
                'notes' => 'Großes Messegelände mit mehreren Hallen',
                'email' => 'info@messe-muenchen.example.de',
                'phoneNumber' => '+49 89 555 0001',
            ],
            [
                'name' => 'Kongresshalle',
                'street' => 'Kongressplatz',
                'number' => '10',
                'postalCode' => '10117',
                'city' => 'Berlin',
                'state' => 'Berlin',
                'countryCode' => 'DE',
                'notes' => 'Zentraler Veranstaltungsort in Berlin',
                'email' => 'events@kongresshalle.example.de',
                'phoneNumber' => '+49 30 555 0010',
            ],
            [
                'name' => 'Online Event',
                'street' => null,
                'number' => null,
                'postalCode' => null,
                'city' => null,
                'state' => null,
                'countryCode' => null,
                'notes' => 'Virtual event location for webinars and online conferences',
                'email' => 'support@virtual-events.example.com',
                'phoneNumber' => null,
            ],
        ];

        foreach ($locationData as $index => $data) {
            $location = new Location();
            $location->setName($data['name']);
            $location->setStreet($data['street']);
            $location->setNumber($data['number']);
            $location->setPostalCode($data['postalCode']);
            $location->setCity($data['city']);
            $location->setState($data['state']);
            $location->setCountryCode($data['countryCode']);
            $location->setNotes($data['notes']);
            $location->setEmail($data['email']);
            $location->setPhoneNumber($data['phoneNumber']);

            $manager->persist($location);

            // Add reference so other fixtures can use these locations
            $this->addReference(self::REFERENCE_PREFIX . $index, $location);
        }

        $manager->flush();
    }
}