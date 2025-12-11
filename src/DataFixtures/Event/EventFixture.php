<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\DataFixtures\Event;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\Location;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;

/**
 * Sulu 3 compatible fixture for events using ContentManager.
 *
 * Creates locations first, then events with proper location references.
 */
class EventFixture extends Fixture implements FixtureGroupInterface
{
    /** @var Location[] */
    private array $locations = [];

    public function __construct(
        private readonly ContentManagerInterface $contentManager,
    ) {
    }

    public static function getGroups(): array
    {
        return ['events'];
    }

    public function load(ObjectManager $manager): void
    {
        // First create locations
        $this->createLocations($manager);

        $titles = [
            'Summer Conference',
            'Tech Summit',
            'Developer Workshop',
            'Design Sprint',
            'Agile Meetup',
            'Cloud Computing Forum',
            'AI Symposium',
            'Cybersecurity Expo',
            'Startup Pitch Night',
            'Leadership Seminar',
            'Blockchain World',
            'IoT Congress',
            'DevOps Days',
            'Product Management Talk',
            'UX/UI Masterclass',
            'Data Science Bootcamp',
            'Mobile App Launch',
            'SaaS Growth Hacking',
            'E-commerce Trends',
            'Digital Marketing Summit',
            'FinTech Innovation',
            'HealthTech Conference',
            'EduTech Seminar',
            'Green Energy Forum',
            'Smart City Expo',
        ];

        $types = ['conference', 'workshop', 'meeting', 'webinar', 'hackathon'];

        // Create 25 events
        for ($i = 0; $i < 25; ++$i) {
            $this->createEvent(
                $manager,
                $i,
                $titles[$i] ?? 'Event ' . ($i + 1),
                $types[array_rand($types)]
            );
        }

        $manager->flush();
    }

    private function createLocations(ObjectManager $manager): void
    {
        $locationData = [
            ['name' => 'Convention Center', 'street' => 'Main Street', 'number' => '100', 'postalCode' => '10001', 'city' => 'New York', 'countryCode' => 'US'],
            ['name' => 'Tech Hub', 'street' => 'Innovation Way', 'number' => '42', 'postalCode' => '94105', 'city' => 'San Francisco', 'countryCode' => 'US'],
            ['name' => 'Business Park', 'street' => 'Corporate Drive', 'number' => '500', 'postalCode' => '60601', 'city' => 'Chicago', 'countryCode' => 'US'],
            ['name' => 'University Hall', 'street' => 'Academic Lane', 'number' => '1', 'postalCode' => '02138', 'city' => 'Cambridge', 'countryCode' => 'US'],
            ['name' => 'Creative Space', 'street' => 'Art District', 'number' => '25', 'postalCode' => '90012', 'city' => 'Los Angeles', 'countryCode' => 'US'],
        ];

        foreach ($locationData as $data) {
            $location = new Location();
            $location->setName($data['name']);
            $location->setStreet($data['street']);
            $location->setNumber($data['number']);
            $location->setPostalCode($data['postalCode']);
            $location->setCity($data['city']);
            $location->setCountryCode($data['countryCode']);

            $manager->persist($location);
            $this->locations[] = $location;
        }

        // Flush to get IDs
        $manager->flush();
    }

    private function createEvent(ObjectManager $manager, int $index, string $baseTitle, string $type): void
    {
        $event = new Event();
        $manager->persist($event);
        $manager->flush(); // Flush to get ID

        // Select a location (cycle through available locations)
        $location = $this->locations[$index % count($this->locations)];

        // Determine dates (mix of past and future)
        $daysOffset = $index * 3 - 30; // Spread over -30 to +45 days roughly
        $startDate = (new \DateTimeImmutable())->modify(sprintf('%+d days', $daysOffset));
        $endDate = $startDate->modify('+2 days');

        // English Content
        $this->contentManager->persist($event, [
            'title' => $baseTitle . ' 2025',
            'subtitle' => 'The ' . $type . ' for professionals',
            'summary' => 'Join us for ' . $baseTitle . ', a premier event in the industry.',
            'text' => '<p>This is the full description for <strong>' . $baseTitle . '</strong>. '
                . 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. '
                . 'Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>',
            'footer' => 'For more information, contact us.',
            'type' => $type,
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s'),
            'location' => $location->getId(), // Pass location ID
            'email' => 'info@example.com',
            'phoneNumber' => '+1 555 123 4567',
            'showAuthor' => false,
            'showDate' => true,
            'seo' => [
                'title' => $baseTitle . ' - Official',
                'description' => 'Official page for ' . $baseTitle,
                'keywords' => 'event, ' . $type . ', tech',
                'canonicalUrl' => '',
                'noIndex' => false,
                'noFollow' => false,
                'hideInSitemap' => false,
            ],
            'excerpt' => [
                'title' => $baseTitle,
                'description' => 'Don\'t miss it!',
                'more' => 'Read more',
                'categories' => [],
                'tags' => [],
            ],
        ], [
            'locale' => 'en',
            'stage' => DimensionContentInterface::STAGE_DRAFT,
        ]);

        // German Content
        $this->contentManager->persist($event, [
            'title' => $baseTitle . ' 2025',
            'subtitle' => 'Das ' . $this->translateType($type) . ' für Fachleute',
            'summary' => 'Begleiten Sie uns bei ' . $baseTitle . ', einer erstklassigen Veranstaltung der Branche.',
            'text' => '<p>Dies ist die vollständige Beschreibung für <strong>' . $baseTitle . '</strong>. '
                . 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. '
                . 'Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>',
            'footer' => 'Für weitere Informationen kontaktieren Sie uns.',
            'type' => $type,
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s'),
            'location' => $location->getId(), // Pass location ID
            'email' => 'info@example.com',
            'phoneNumber' => '+1 555 123 4567',
            'showAuthor' => false,
            'showDate' => true,
            'seo' => [
                'title' => $baseTitle . ' - Offiziell',
                'description' => 'Offizielle Seite für ' . $baseTitle,
                'keywords' => 'event, ' . $type . ', tech',
                'canonicalUrl' => '',
                'noIndex' => false,
                'noFollow' => false,
                'hideInSitemap' => false,
            ],
            'excerpt' => [
                'title' => $baseTitle,
                'description' => 'Nicht verpassen!',
                'more' => 'Mehr lesen',
                'categories' => [],
                'tags' => [],
            ],
        ], [
            'locale' => 'de',
            'stage' => DimensionContentInterface::STAGE_DRAFT,
        ]);

        // Publish most events, leave some as draft
        if ($index % 5 !== 0) { // Every 5th event stays draft
            foreach (['en', 'de'] as $locale) {
                try {
                    $this->contentManager->applyTransition(
                        $event,
                        [
                            'locale' => $locale,
                            'stage' => DimensionContentInterface::STAGE_DRAFT,
                        ],
                        WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH
                    );
                } catch (\Exception $e) {
                    // Log but continue - some events may fail to publish
                    // This is acceptable for fixture data
                }
            }
        }
    }

    private function translateType(string $type): string
    {
        return match ($type) {
            'conference' => 'Konferenz',
            'workshop' => 'Workshop',
            'meeting' => 'Meeting',
            'webinar' => 'Webinar',
            'hackathon' => 'Hackathon',
            default => $type,
        };
    }
}