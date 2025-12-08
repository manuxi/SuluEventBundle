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
        // Create multiple events
        $this->createEvent1($manager);
        $this->createEvent2($manager);
        $this->createEvent3($manager);

        $manager->flush();
    }

    private function createEvent1(ObjectManager $manager): void
    {
        // Step 1: Create Event entity (NO properties set here!)
        $event = new Event();
        $manager->persist($event);
        $manager->flush(); // Flush to get ID

        // Step 2: Persist dimension content via ContentManager (English)
        $this->contentManager->persist($event, [
            'title' => 'Summer Conference 2025',
            'subtitle' => 'The Future of Technology',
            'summary' => 'Join us for three days of inspiring talks and networking.',
            'text' => '<p>This is the main conference text with <strong>HTML</strong> content.</p>',
            'footer' => 'Sponsored by TechCorp',
            'type' => 'conference',
            'startDate' => (new \DateTimeImmutable('+7 days'))->format('Y-m-d H:i:s'),
            'endDate' => (new \DateTimeImmutable('+7 days +3 hours'))->format('Y-m-d H:i:s'),
            'email' => 'info@summerconf.com',
            'phoneNumber' => '+1-555-0100',
            'showAuthor' => true,
            'showDate' => true,
            'seo' => [
                'title' => 'Summer Conference 2025 - Tech Event',
                'description' => 'Join the biggest tech conference of the year.',
                'keywords' => 'conference, technology, networking',
                'canonicalUrl' => '',
                'noIndex' => false,
                'noFollow' => false,
                'hideInSitemap' => false,
            ],
            'excerpt' => [
                'title' => 'Summer Conference',
                'description' => 'Three days of tech talks',
                'more' => 'Learn more',
                'categories' => [],
                'tags' => [],
            ],
        ], [
            'locale' => 'en',
            'stage' => DimensionContentInterface::STAGE_DRAFT,
        ]);

        $this->contentManager->applyTransition(
            $event,
            [
                'locale' => 'en',
                'stage' => DimensionContentInterface::STAGE_DRAFT,
            ],
            WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH
        );

        // Add German translation
        $this->contentManager->persist($event, [
            'title' => 'Sommerkonferenz 2025',
            'subtitle' => 'Die Zukunft der Technologie',
            'summary' => 'Nehmen Sie an drei Tagen inspirierender Vorträge und Networking teil.',
            'text' => '<p>Dies ist der Haupttext der Konferenz mit <strong>HTML</strong>-Inhalt.</p>',
            'footer' => 'Gesponsert von TechCorp',
            'type' => 'conference',
            'startDate' => (new \DateTimeImmutable('+7 days'))->format('Y-m-d H:i:s'),
            'endDate' => (new \DateTimeImmutable('+7 days +3 hours'))->format('Y-m-d H:i:s'),
            'email' => 'info@sommerkonferenz.de',
            'phoneNumber' => '+49-555-0100',
            'showAuthor' => true,
            'showDate' => true,
            'seo' => [
                'title' => 'Sommerkonferenz 2025 - Tech Event',
                'description' => 'Nehmen Sie an der größten Tech-Konferenz des Jahres teil.',
                'keywords' => 'konferenz, technologie, networking',
                'canonicalUrl' => '',
                'noIndex' => false,
                'noFollow' => false,
                'hideInSitemap' => false,
            ],
            'excerpt' => [
                'title' => 'Sommerkonferenz',
                'description' => 'Drei Tage Tech-Vorträge',
                'more' => 'Mehr erfahren',
                'categories' => [],
                'tags' => [],
            ],
        ], [
            'locale' => 'de',
            'stage' => DimensionContentInterface::STAGE_DRAFT,
        ]);

        $this->contentManager->applyTransition(
            $event,
            [
                'locale' => 'de',
                'stage' => DimensionContentInterface::STAGE_DRAFT,
            ],
            WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH
        );
    }

    private function createEvent2(ObjectManager $manager): void
    {
        $event = new Event();
        $manager->persist($event);
        $manager->flush();

        $this->contentManager->persist($event, [
            'title' => 'Workshop: Modern Web Development',
            'subtitle' => 'Hands-on Coding Session',
            'summary' => 'Learn the latest web technologies in this practical workshop.',
            'text' => '<p>Build a complete web application from scratch.</p>',
            'footer' => '',
            'type' => 'workshop',
            'startDate' => (new \DateTimeImmutable('+14 days'))->format('Y-m-d H:i:s'),
            'endDate' => (new \DateTimeImmutable('+14 days +2 hours'))->format('Y-m-d H:i:s'),
            'email' => 'workshop@example.com',
            'phoneNumber' => '+1-555-0200',
            'showAuthor' => false,
            'showDate' => true,
            'seo' => [
                'title' => 'Web Development Workshop',
                'description' => 'Hands-on workshop for modern web development.',
                'keywords' => 'workshop, web development, coding',
                'canonicalUrl' => '',
                'noIndex' => false,
                'noFollow' => false,
                'hideInSitemap' => false,
            ],
            'excerpt' => [
                'title' => 'Web Dev Workshop',
                'description' => 'Practical coding session',
                'more' => '',
                'categories' => [],
                'tags' => [],
            ],
        ], [
            'locale' => 'en',
            'stage' => DimensionContentInterface::STAGE_DRAFT,
        ]);

        $this->contentManager->applyTransition(
            $event,
            [
                'locale' => 'en',
                'stage' => DimensionContentInterface::STAGE_DRAFT,
            ],
            WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH
        );
    }

    private function createEvent3(ObjectManager $manager): void
    {
        // Draft-only event (not published)
        $event = new Event();
        $manager->persist($event);
        $manager->flush();

        $this->contentManager->persist($event, [
            'title' => 'Annual Meeting 2025',
            'subtitle' => 'Internal Event',
            'summary' => 'Annual company meeting for all employees.',
            'text' => '<p>This is an internal event.</p>',
            'footer' => '',
            'type' => 'meeting',
            'startDate' => (new \DateTimeImmutable('+30 days'))->format('Y-m-d H:i:s'),
            'endDate' => null,
            'email' => 'internal@company.com',
            'phoneNumber' => null,
            'showAuthor' => false,
            'showDate' => false,
            'seo' => [
                'title' => 'Annual Meeting',
                'description' => 'Company annual meeting',
                'keywords' => 'meeting, annual, company',
                'canonicalUrl' => '',
                'noIndex' => true,
                'noFollow' => true,
                'hideInSitemap' => true,
            ],
            'excerpt' => [
                'title' => 'Annual Meeting',
                'description' => 'Internal event',
                'more' => '',
                'categories' => [],
                'tags' => [],
            ],
        ], [
            'locale' => 'en',
            'stage' => DimensionContentInterface::STAGE_DRAFT,
        ]);

        // Don't publish - leave as draft
    }
}