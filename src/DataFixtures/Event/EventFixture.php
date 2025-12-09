<?php
declare(strict_types=1);
namespace Manuxi\SuluEventBundle\DataFixtures\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
/**
 * Sulu 3 compatible fixture for events using ContentManager.
 */
class EventFixture extends Fixture implements FixtureGroupInterface
{
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
            'Data Science Bootcam',
            'Mobile App Launch',
            'SaaS Growth Hacking',
            'E-commerce Trends',
            'Digital Marketing Summit',
            'FinTech Innovation',
            'HealthTech Conference',
            'EduTech Seminar',
            'Green Energy Forum',
            'Smart City Expo'
        ];
        $types = ['conference', 'workshop', 'meeting', 'webinar', 'hackathon'];
        // Create 25 events
        for ($i = 0; $i < 25; ++$i) {
            $this->createEvent($manager, $i, $titles[$i] ?? 'Event ' . ($i + 1), $types[array_rand($types)]);
        }
        $manager->flush();
    }
    private function createEvent(ObjectManager $manager, int $index, string $baseTitle, string $type): void
    {
        $event = new Event();
        $manager->persist($event);
        $manager->flush(); // Flush to get ID
        // Determine dates (mix of past and future)
        $daysOffset = $index * 3 - 30; // Spread over -30 to +45 days roughly
        $startDate = (new \DateTimeImmutable())->modify(sprintf('%+d days', $daysOffset));
        $endDate = $startDate->modify('+2 days');
        // English Content
        $this->contentManager->persist($event, [
            'title' => $baseTitle . ' 2025',
            'subtitle' => 'The ' . $type . ' for professionals',
            'summary' => 'Join us for ' . $baseTitle . ', a premier event in the industry.',
            'text' => '<p>This is the full description for <strong>' . $baseTitle . '</strong>. It includes <ul><li>Talks</li><li>Networking</li><li>Workshops</li></ul></p>',
            'footer' => 'Sponsored by TechCorp',
            'type' => $type,
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s'),
            'email' => 'info@example.com',
            'phoneNumber' => '+1-555-' . str_pad((string) $index, 4, '0', STR_PAD_LEFT),
            'showAuthor' => true,
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
                'description' => 'Don\'t miss out!',
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
            'title' => $baseTitle . ' 2025 (DE)',
            'subtitle' => 'Das ' . $type . ' für Profis',
            'summary' => 'Seien Sie dabei bei ' . $baseTitle . ', einem Top-Event der Branche.',
            'text' => '<p>Dies ist die vollständige Beschreibung für <strong>' . $baseTitle . '</strong>. Es beinhaltet <ul><li>Vorträge</li><li>Networking</li><li>Workshops</li></ul></p>',
            'footer' => 'Gesponsert von TechCorp',
            'type' => $type,
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s'),
            'email' => 'info@example.de',
            'phoneNumber' => '+49-555-' . str_pad((string) $index, 4, '0', STR_PAD_LEFT),
            'showAuthor' => true,
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
                $this->contentManager->applyTransition(
                    $event,
                    [
                        'locale' => $locale,
                        'stage' => DimensionContentInterface::STAGE_DRAFT,
                    ],
                    WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH
                );
            }
        }
    }
}