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
use Sulu\Route\Domain\Model\Route;

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
        // Check for existing locations first
        $repository = $manager->getRepository(Location::class);
        $existingLocations = $repository->findAll();

        if (count($existingLocations) > 0) {
            $this->locations = $existingLocations;
            echo "Using " . count($existingLocations) . " existing locations.\n";
            return;
        }

        $locationData = [
            ['name' => 'Convention Center', 'street' => 'Main Street', 'number' => '100', 'postalCode' => '10001', 'city' => 'New York', 'countryCode' => 'US'],
            ['name' => 'Tech Hub', 'street' => 'Innovation Way', 'number' => '42', 'postalCode' => '94105', 'city' => 'San Francisco', 'countryCode' => 'US'],
            ['name' => 'Business Park', 'street' => 'Corporate Drive', 'number' => '500', 'postalCode' => '60601', 'city' => 'Chicago', 'countryCode' => 'US'],
            ['name' => 'University Hall', 'street' => 'Academic Lane', 'number' => '1', 'postalCode' => '02138', 'city' => 'Cambridge', 'countryCode' => 'US'],
            ['name' => 'Creative Space', 'street' => 'Art District', 'number' => '25', 'postalCode' => '90012', 'city' => 'Los Angeles', 'countryCode' => 'US'],
            ['name' => 'River Side Hall', 'street' => 'Scenic Route', 'number' => '9', 'postalCode' => '78701', 'city' => 'Austin', 'countryCode' => 'US'],
            ['name' => 'Mountain View Center', 'street' => 'Peak Road', 'number' => '33', 'postalCode' => '80302', 'city' => 'Boulder', 'countryCode' => 'US'],
            ['name' => 'Harbor Point', 'street' => 'Ocean Drive', 'number' => '12', 'postalCode' => '02210', 'city' => 'Boston', 'countryCode' => 'US'],
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

        // Determine dates (Now + 0 to 730 days)
        $currentYear = (int) date('Y');
        $daysOffset = mt_rand(0, 730);

        $baseDate = (new \DateTimeImmutable())->modify(sprintf('+%d days', $daysOffset));

        // Get Homepage URL for parent
        $homeRoute = $manager->getRepository(Route::class)->findOneBy(['slug' => '/']);
        $homeUuid = $homeRoute ? $homeRoute->getResourceId() : '00000000-0000-0000-0000-000000000000';

        // Vary times
        $hour = 8 + ($index % 10); // Start times between 08:00 and 17:00
        $minute = ($index % 2) * 30; // 00 or 30

        $startDate = $baseDate->setTime($hour, $minute);

        // End date logic
        if ($index % 3 === 0) {
            // Multi-day event
            $endDate = $startDate->modify('+2 days')->setTime(17, 0);
        } elseif ($index % 3 === 1) {
            // Full day (handled by time, but let's say same day late)
            $endDate = $startDate->setTime($hour + 4, 30);
        } else {
            // Same day, short event
            $endDate = $startDate->modify('+2 hours');
        }

        // Base Title with Year (of the event start date)
        $eventYear = $startDate->format('Y');
        $titleWithYear = $baseTitle . ' ' . $eventYear;

        // Better subtitles
        $subtitles = [
            'Unlock the future of ' . $type,
            'Connect, Collaborate, Create',
            'Where innovation meets execution',
            'The ultimate gathering for ' . $type . ' enthusiasts',
            'Defining the new standard in ' . $type,
            'Strategies for success in the modern era',
            'Deep dive into emerging trends',
            'Scaling your potential',
            'A masterclass in ' . $type . ' excellence',
            'Transforming ideas into reality'
        ];
        $subtitle = $subtitles[$index % count($subtitles)];

        // English Content
        $this->contentManager->persist($event, [
            'title' => $titleWithYear,
            'subtitle' => $subtitle,
            'summary' => 'Join us for ' . $baseTitle . ', a premier event in the industry.',
            'text' => '<p>This is the full description for <strong>' . $titleWithYear . '</strong>. '
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
            'url' => ['page' => ['path' => '/', 'uuid' => $homeUuid], 'suffix' => '/' . str_replace(' ', '-', strtolower($titleWithYear))],
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
            'title' => $titleWithYear,
            'subtitle' => 'Das ' . $this->translateType($type) . ' Event, das Sie nicht verpassen sollten',
            'summary' => 'Begleiten Sie uns bei ' . $baseTitle . ', einer erstklassigen Veranstaltung der Branche.',
            'text' => '<p>Dies ist die vollständige Beschreibung für <strong>' . $titleWithYear . '</strong>. '
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
            'url' => ['page' => ['path' => '/', 'uuid' => $homeUuid], 'suffix' => '/de/' . str_replace(' ', '-', strtolower($titleWithYear))],
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

        // Flush content to ensure it can be loaded for transition
        $manager->flush();

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
                    echo "Error publishing event: " . $e->getMessage() . "\n";
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