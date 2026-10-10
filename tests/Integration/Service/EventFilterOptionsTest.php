<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration\Service;

use Manuxi\SuluEventBundle\Service\EventFilterOptions;
use Manuxi\SuluEventBundle\Service\EventTypeSelect;
use Manuxi\SuluEventBundle\Tests\Integration\AbstractEventDatabaseTestCase;
use Manuxi\SuluEventBundle\Tests\Integration\Stub\StubCategory;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventFilterOptionsTest extends AbstractEventDatabaseTestCase
{
    private TranslatorInterface $translator;
    private EventTypeSelect $typeSelect;
    private string $tomorrow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translator = $this->createStub(TranslatorInterface::class);
        $this->translator->method('trans')->willReturnCallback(
            static fn (string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string => '[' . $id . '|' . $locale . ']'
        );
        $this->typeSelect = new EventTypeSelect($this->translator, [
            'default'    => ['name' => 'type.default', 'color' => '#cccccc'],
            'conference' => ['name' => 'type.conference', 'color' => '#6610f2'],
            'meetup'     => ['name' => 'type.meetup', 'color' => '#00a6a6'],
            'hackathon'  => ['name' => 'type.hackathon', 'color' => '#2e7d32'],
        ], 'default');

        // a tree: root > [ai, web > [symfony]], and a category outside of the root
        $root    = new StubCategory('event-topics', 1, 10, 0, ['de' => 'Event-Themen']);
        $ai      = new StubCategory('ai', 2, 3, 1, ['de' => 'KI', 'en' => 'AI'], $root);
        $web     = new StubCategory('web', 4, 7, 1, ['de' => 'Web'], $root);
        $symfony = new StubCategory('symfony', 5, 6, 2, [], $web);
        $misc    = new StubCategory('misc', 11, 12, 0, ['de' => 'Sonstiges']);
        foreach ([$root, $ai, $web, $symfony, $misc] as $category) {
            $this->entityManager->persist($category);
        }
        $this->entityManager->flush();
        $this->categories = ['root' => $root, 'ai' => $ai, 'web' => $web, 'symfony' => $symfony, 'misc' => $misc];

        $this->tomorrow = (new \DateTimeImmutable('+1 day'))->format('Y-m-d 10:00:00');
        $past           = (new \DateTimeImmutable('-5 days'))->format('Y-m-d 10:00:00');

        $this->createEvent('a', 'de', $this->tomorrow, type: 'conference', categories: ['ai', 'symfony'], location: 'Messe Berlin', isFree: true);
        $this->createEvent('b', 'de', $this->tomorrow, type: 'conference', categories: ['ai'], attendanceMode: 'online', isFree: false);
        $this->createEvent('c', 'de', $this->tomorrow, type: 'meetup', categories: ['misc'], attendanceMode: 'mixed', location: 'Convention Center');
        $this->createEvent('past', 'de', $past, type: 'hackathon', categories: ['web'], location: 'Messe Berlin', isFree: true);
        $this->createEvent('draft', 'de', $this->tomorrow, type: 'hackathon', stage: 'draft');
        $this->createEvent('english', 'en', $this->tomorrow, type: 'meetup', location: 'Convention Center');
    }

    public function testListsOnlyValuesOfUpcomingPublishedEventsWithTheirCounts(): void
    {
        $options = $this->createOptions()->getOptions('de');

        $this->assertSame(
            [
                ['key' => 'conference', 'name' => '[type.conference|de]', 'color' => '#6610f2', 'count' => 2],
                ['key' => 'meetup', 'name' => '[type.meetup|de]', 'color' => '#00a6a6', 'count' => 1],
            ],
            $options['types'],
            'the hackathons are past or a draft'
        );
        $this->assertSame(
            [
                ['code' => 'DE', 'name' => 'Deutschland', 'count' => 1],
                ['code' => 'US', 'name' => 'Vereinigte Staaten', 'count' => 1],
            ],
            $options['countries']
        );
        $this->assertSame(
            [
                ['key' => 'offline', 'name' => '[sulu_event.attendance_mode.offline|de]', 'count' => 1],
                ['key' => 'online', 'name' => '[sulu_event.attendance_mode.online|de]', 'count' => 1],
                ['key' => 'mixed', 'name' => '[sulu_event.attendance_mode.mixed|de]', 'count' => 1],
            ],
            $options['attendanceModes']
        );
        $this->assertSame(['count' => 1], $options['free'], 'only upcoming events count');
    }

    public function testTopicsAreCategoriesOfUpcomingEventsInTreeOrderWithNamesOfTheLocale(): void
    {
        $topics = $this->createOptions()->getOptions('de')['topics'];

        // no root configured: every category in use, "web" only has a past event
        $this->assertSame(['ai', 'symfony', 'misc'], array_column($topics, 'key'));
        $this->assertSame('KI', $topics[0]['name']);
        $this->assertSame(2, $topics[0]['count']);
        // a category without a translation shows its key
        $this->assertSame('symfony', $topics[1]['name']);
        $this->assertSame($this->categories['web']->getId(), $topics[1]['parent']);

        $english = $this->createOptions()->getOptions('en')['topics'];
        $this->assertSame([], $english, 'the English event has no categories');
    }

    public function testTheCategoryRootLimitsTheTopicsToItsDescendants(): void
    {
        $topics = $this->createOptions('event-topics')->getOptions('de')['topics'];

        $this->assertSame(['ai', 'symfony'], array_column($topics, 'key'), '"misc" is outside of the root');
        $this->assertSame([0, 1], array_column($topics, 'depth'), 'depth is relative to the root');
    }

    public function testAnUnknownRootListsNoTopics(): void
    {
        $this->assertSame([], $this->createOptions('does-not-exist')->getOptions('de')['topics']);
    }

    public function testNoUpcomingEventsNoOptions(): void
    {
        $options = $this->createOptions()->getOptions('fr');

        $this->assertSame(['topics' => [], 'types' => [], 'countries' => [], 'attendanceModes' => [], 'free' => null], $options);
    }

    public function testResultIsCachedPerLocaleUntilCleared(): void
    {
        $cache   = new TagAwareAdapter(new ArrayAdapter());
        $options = $this->createOptions(null, $cache);

        $this->assertCount(2, $options->getOptions('de')['types']);

        // a new event does not show up before the cache is cleared
        $this->createEvent('d', 'de', $this->tomorrow, type: 'hackathon');
        $this->assertCount(2, $options->getOptions('de')['types']);

        $options->clear();
        $this->assertCount(3, $options->getOptions('de')['types']);
    }

    private function createOptions(?string $root = null, ?TagAwareAdapter $cache = null): EventFilterOptions
    {
        return new EventFilterOptions($this->entityManager, $this->typeSelect, $this->translator, $root, $cache);
    }
}
