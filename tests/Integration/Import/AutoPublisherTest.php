<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration\Import;

use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluEventBundle\Command\PublishReadyCommand;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventImport;
use Manuxi\SuluEventBundle\Entity\EventImportRun;
use Manuxi\SuluEventBundle\Import\AutoPublish\AutoPublisher;
use Manuxi\SuluEventBundle\Import\AutoPublish\AutoPublishRules;
use Manuxi\SuluEventBundle\Import\ImportRunSummary;
use Manuxi\SuluEventBundle\Import\ImportSettings;
use Manuxi\SuluEventBundle\Import\ImportUserSwitcher;
use Manuxi\SuluEventBundle\Import\PendingEventFinder;
use Manuxi\SuluEventBundle\Repository\EventImportRepository;
use Manuxi\SuluEventBundle\Repository\EventImportRunRepository;
use Manuxi\SuluEventBundle\Tests\Integration\AbstractEventDatabaseTestCase;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * The automatic publishing of complete imported events: every condition of "when" and "require", the languages, a published
 * event with a new draft, events that must never be touched, the dry run and the stored result.
 */
class AutoPublisherTest extends AbstractEventDatabaseTestCase
{
    private const COMPLETE = ['subtitle' => 'Sub', 'summary' => 'Short', 'text' => '<p>Text</p>'];

    private FakeContentManager $contentManager;
    private FakeContentWorkflow $workflow;
    private EventImportRepository $importRepository;
    private EventImportRunRepository $runRepository;

    /** @var list<object> */
    private array $collected = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->contentManager = new FakeContentManager($this->entityManager);
        $this->workflow       = new FakeContentWorkflow();
        $this->collected      = [];

        $registry = $this->createStub(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($this->entityManager);
        $this->importRepository = new EventImportRepository($registry);
        $this->runRepository    = new EventImportRunRepository($registry);
    }

    public function testCompleteEventOfAListedSourceIsPublishedInAllLocales(): void
    {
        $uuid = $this->importedEvent('a', 'Complete', ['en' => $this->complete(), 'de' => $this->complete()]);
        $this->importedEvent('b', 'Other source', ['en' => $this->complete()], source: 'ical.other');

        $result = $this->publisher(['sources' => ['ical.a'], 'locales' => ['en', 'de']])->publishReady();

        $this->assertSame(1, $result['published']);
        $this->assertSame(0, $result['waiting']);
        $this->assertSame(1, $result['skipped'], 'the event of the other source is not covered');
        $this->assertSame(
            [['locale' => 'en', 'transition' => 'publish'], ['locale' => 'de', 'transition' => 'publish']],
            $this->workflow->transitions
        );
        $this->assertCount(2, $this->collected, 'a domain event for each language');
        $this->assertSame($uuid, $this->collected[0]->getEvent()->getUuid());
    }

    public function testNothingWithoutEnabledOrConditions(): void
    {
        $this->importedEvent('a', 'Complete', ['en' => $this->complete()]);

        $this->assertSame(0, $this->publisher(['sources' => ['ical.a']], enabled: false)->publishReady()['published']);
        $this->assertFalse(AutoPublishRules::fromArray([])->hasConditions());
        $this->assertSame([], $this->workflow->transitions);

        $result = $this->publisher([])->publishReady();
        $this->assertSame(0, $result['published'], 'without a condition in "when" no event qualifies');
    }

    public function testWhenConditionsAreOred(): void
    {
        $this->importedEvent('series', 'In a series', ['en' => $this->complete()], source: 'ical.x', matchKey: 'conf-2027');
        $this->importedEvent('typed', 'Of a type', ['en' => $this->complete() + ['type' => 'keynote']], source: 'ical.y');
        $this->importedEvent('plain', 'Plain', ['en' => $this->complete()], source: 'ical.z');

        $bySeries = $this->publisher(['series' => true])->evaluateAll();
        $this->assertSame(['In a series' => 'ready', 'Of a type' => 'skipped', 'Plain' => 'skipped'], array_column($bySeries, 'state', 'title'));

        $byType = $this->publisher(['types' => ['keynote']])->evaluateAll();
        $this->assertSame(['In a series' => 'skipped', 'Of a type' => 'ready', 'Plain' => 'skipped'], array_column($byType, 'state', 'title'));

        $any = $this->publisher(['series' => true, 'types' => ['keynote'], 'sources' => ['ical.z']])->evaluateAll();
        $this->assertSame(['ready'], array_values(array_unique(array_column($any, 'state'))));
    }

    public function testTheTypeFallsBackToTheDefaultType(): void
    {
        $this->importedEvent('a', 'Untyped', ['en' => $this->complete()]);

        $items = $this->publisher(['types' => ['default']])->evaluateAll();

        $this->assertSame('ready', $items[0]['state']);
    }

    public function testEveryRequiredTextOfEveryLanguageMustBeFilled(): void
    {
        $this->importedEvent('a', 'No subtitle', ['en' => ['subtitle' => ' ', 'summary' => 'S', 'text' => 'T'], 'de' => $this->complete()]);
        $this->importedEvent('b', 'No text de', ['en' => $this->complete(), 'de' => ['subtitle' => 'S', 'summary' => 'S', 'text' => '<p> </p>']]);
        $this->importedEvent('c', 'No de', ['en' => $this->complete()]);
        $this->importedEvent('d', 'No summary', ['en' => ['subtitle' => 'S', 'summary' => null, 'text' => 'T'], 'de' => $this->complete()]);

        $items   = $this->publisher(['sources' => ['ical.a'], 'locales' => ['en', 'de']])->evaluateAll();
        $missing = array_column($items, 'missing', 'title');

        $this->assertSame(['subtitle:en'], $missing['No subtitle']);
        $this->assertSame(['text:de'], $missing['No text de']);
        $this->assertSame(['locale:de'], $missing['No de']);
        $this->assertSame(['summary:en'], $missing['No summary']);
        $this->assertSame(['waiting'], array_values(array_unique(array_column($items, 'state'))));
    }

    public function testWithoutLocalesTheLanguagesOfTheEventAreChecked(): void
    {
        $this->importedEvent('a', 'Only en', ['en' => $this->complete()]);

        $item = $this->publisher(['sources' => ['ical.a']])->evaluateAll()[0];

        $this->assertSame('ready', $item['state']);
        $this->assertSame(['en'], $item['locales']);
    }

    public function testCategoryImageAndPlaceAreRequiredUnlessSwitchedOff(): void
    {
        $this->importedEvent('a', 'Bare', ['en' => ['subtitle' => 'S', 'summary' => 'S', 'text' => 'T']]);

        $strict = $this->publisher(['sources' => ['ical.a']])->evaluateAll()[0];
        $this->assertSame('waiting', $strict['state']);
        $this->assertSame(['categories', 'image', 'place'], $strict['missing']);

        $relaxed = $this->publisher(['sources' => ['ical.a']], require: ['category' => false, 'image' => false, 'place_or_online' => false])->evaluateAll()[0];
        $this->assertSame('ready', $relaxed['state']);
    }

    public function testAnOnlineLinkCountsAsPlace(): void
    {
        $this->importedEvent('a', 'Online', ['en' => ['subtitle' => 'S', 'summary' => 'S', 'text' => 'T', 'excerptCategories' => [$this->categories['web']->getId()], 'excerpt' => ['image' => ['id' => 5]], 'onlineUrl' => 'https://stream.example.com/live', 'attendanceMode' => 'online']]);

        $this->assertSame('ready', $this->publisher(['sources' => ['ical.a']])->evaluateAll()[0]['state']);
    }

    public function testFilteredRejectedAndVanishedAreNeverPublished(): void
    {
        $this->importedEvent('f', 'Filtered', ['en' => $this->complete()], state: EventImport::STATE_FILTERED);
        $this->importedEvent('r', 'Rejected', ['en' => $this->complete()], state: EventImport::STATE_REJECTED);
        $this->importedEvent('v', 'Vanished', ['en' => $this->complete()], state: EventImport::STATE_VANISHED);

        $result = $this->publisher(['sources' => ['ical.a']])->publishReady();

        $this->assertSame(0, $result['published']);
        $this->assertSame([], $this->workflow->transitions);
        $titles = array_column($result['items'], 'title');
        $this->assertNotContains('Filtered', $titles);
        $this->assertNotContains('Rejected', $titles);
        $this->assertContains('Vanished', $titles);
    }

    public function testAnEventThatIsPublishedAlreadyIsLeftAlone(): void
    {
        $this->importedEvent('a', 'Live', ['en' => $this->complete()], place: WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);

        $result = $this->publisher(['sources' => ['ical.a']])->publishReady();

        $this->assertSame(0, $result['published']);
        $this->assertSame('already published', $result['items'][0]['note']);
    }

    public function testAnEventThatIsOverIsNeverPublished(): void
    {
        $this->importedEvent('a', 'Over', ['en' => $this->complete() + ['startDate' => '2020-06-01 10:00:00']]);
        $this->importedEvent('b', 'Running', ['en' => $this->complete() + ['startDate' => '2020-06-01 10:00:00', 'endDate' => '2099-06-02 10:00:00']]);

        $items = array_column($this->publisher(['sources' => ['ical.a']])->evaluateAll(), 'note', 'title');

        $this->assertSame('the event is over', $items['Over']);
        $this->assertNull($items['Running']);
    }

    public function testAnEditorsDecisionIsRespected(): void
    {
        $this->importedEvent('a', 'Unpublished by hand', ['en' => $this->complete()], place: WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED, wasPublished: true);
        $this->importedEvent('b', 'In review', ['en' => $this->complete()], place: WorkflowInterface::WORKFLOW_PLACE_REVIEW);

        $items = array_column($this->publisher(['sources' => ['ical.a']])->evaluateAll(), 'state', 'title');

        $this->assertSame(['Unpublished by hand' => 'skipped', 'In review' => 'skipped'], $items);
    }

    public function testRepublishOnlyWhenSwitchedOnAndNothingIsLocked(): void
    {
        $this->importedEvent('a', 'Changed', ['en' => $this->complete()], place: WorkflowInterface::WORKFLOW_PLACE_DRAFT, wasPublished: true);
        $this->importedEvent('b', 'Changed but locked', ['en' => $this->complete()], place: WorkflowInterface::WORKFLOW_PLACE_DRAFT, wasPublished: true, locked: ['title:en']);

        $off = array_column($this->publisher(['sources' => ['ical.a']])->evaluateAll(), 'state', 'title');
        $this->assertSame(['Changed' => 'skipped', 'Changed but locked' => 'skipped'], $off);

        $publisher = $this->publisher(['sources' => ['ical.a']], republish: true);
        $on        = $publisher->evaluateAll();
        $this->assertSame(['Changed' => 'ready', 'Changed but locked' => 'skipped'], array_column($on, 'state', 'title'));
        $this->assertSame('republish', array_column($on, 'action', 'title')['Changed']);
        $this->assertStringContainsString('title:en', array_column($on, 'note', 'title')['Changed but locked']);

        $this->assertSame(1, $publisher->publishReady()['published']);
        $this->assertSame([['locale' => 'en', 'transition' => 'publish']], $this->workflow->transitions);
    }

    public function testRepublishWaitsWhenTheEventNoLongerMeetsTheRules(): void
    {
        $this->importedEvent('a', 'Lost its text', ['en' => ['text' => ''] + $this->complete()], place: WorkflowInterface::WORKFLOW_PLACE_DRAFT, wasPublished: true);

        $item = $this->publisher(['sources' => ['ical.a']], republish: true)->evaluateAll()[0];

        $this->assertSame('waiting', $item['state']);
        $this->assertSame(['text:en'], $item['missing']);
    }

    public function testDryRunChangesNothingButIsStored(): void
    {
        $this->importedEvent('a', 'Complete', ['en' => $this->complete()]);
        $this->importedEvent('b', 'Incomplete', ['en' => ['subtitle' => 'S']]);

        $publisher = $this->publisher(['sources' => ['ical.a']]);
        $result    = $publisher->publishReady(true);

        $this->assertSame(1, $result['published']);
        $this->assertSame(1, $result['waiting']);
        $this->assertSame([], $this->workflow->transitions);
        $this->assertSame([], $this->collected);

        $this->entityManager->clear();
        $run = $this->runRepository->findLastAutoPublish(true);
        $this->assertNotNull($run);
        $this->assertTrue($run->isDryRun());
        $this->assertNull($this->runRepository->findLastAutoPublish(false), 'dry runs only on request');
        $this->assertNull($this->runRepository->findLast(true), 'it is no run of the import');
        $this->assertStringStartsWith('would publish 1, waiting 1', ImportRunSummary::fromAutoPublishRun($run)['importAutoPublishSummary']);
    }

    public function testLimitAndStoredResult(): void
    {
        $this->importedEvent('a', 'One', ['en' => $this->complete()]);
        $this->importedEvent('b', 'Two', ['en' => $this->complete()]);
        $this->importedEvent('c', 'Gap', ['en' => ['subtitle' => 'S']]);

        $result = $this->publisher(['sources' => ['ical.a']])->publishReady(false, 1);

        $this->assertSame(1, $result['published']);
        $this->assertSame(1, $result['waiting']);
        $this->assertSame(1, $result['skipped']);

        $this->entityManager->clear();
        $run    = $this->runRepository->findLastAutoPublish();
        $report = $run->getReport()['autoPublish'];
        $this->assertSame(EventImportRun::SOURCE_AUTO_PUBLISH, $run->getSource());
        $this->assertSame(1, $report['published']);
        $this->assertSame('Gap', $report['waitingItems'][0]['title']);
        $this->assertContains('text:en', $report['waitingItems'][0]['missing']);
        $this->assertMatchesRegularExpression('/^published 1, waiting 1, skipped 1, errors 0$/', ImportRunSummary::fromAutoPublishRun($run)['importAutoPublishSummary']);
    }

    public function testPendingListSaysWhatIsMissingForTheAutoPublishing(): void
    {
        $this->importedEvent('a', 'No subtitle', ['en' => ['summary' => 'S', 'text' => 'T']]);
        $this->importedEvent('b', 'Other source', ['en' => ['summary' => 'S', 'text' => 'T']], source: 'ical.other');

        $this->entityManager->clear();
        $finder = new PendingEventFinder($this->importRepository, $this->repository, $this->settings(), $this->publisher(['sources' => ['ical.a']]));
        $found  = $finder->find(['en']);
        $byName = array_column($found['items'], 'autoPublish', 'title');

        $this->assertSame('waiting', $byName['No subtitle']['state']);
        $this->assertContains('subtitle:en', $byName['No subtitle']['missing']);
        $this->assertNull($byName['Other source'], 'not covered by "when": no statement');
    }

    public function testEventsThatOnlyLackSomethingForTheAutoPublishingAreListedToo(): void
    {
        $this->importedEvent('a', 'Only the subtitle', ['en' => ['summary' => 'S', 'text' => 'T', 'excerptCategories' => [$this->categories['web']->getId()], 'excerpt' => ['image' => ['id' => 5]]]]);

        $this->entityManager->clear();
        $withRules    = new PendingEventFinder($this->importRepository, $this->repository, $this->settings(), $this->publisher(['sources' => ['ical.a']], require: ['category' => false, 'image' => false, 'place_or_online' => false]));
        $withoutRules = new PendingEventFinder($this->importRepository, $this->repository, $this->settings());

        $this->assertSame(1, $withRules->find(['en'])['total']);
        $this->assertSame(0, $withoutRules->find(['en'])['total'], 'the old behaviour without the rules');
    }

    public function testCommandDryRunAndRun(): void
    {
        $this->importedEvent('a', 'Complete', ['en' => $this->complete()]);
        $this->importedEvent('b', 'Incomplete', ['en' => ['subtitle' => 'S']]);

        $tester = new CommandTester(new PublishReadyCommand($this->publisher(['sources' => ['ical.a']])));

        $this->assertSame(Command::SUCCESS, $tester->execute(['--dry-run' => true]));
        $display = $tester->getDisplay();
        $this->assertStringContainsString('would publish', $display);
        $this->assertStringContainsString('missing: summary:en', $display);
        $this->assertSame([], $this->workflow->transitions);

        $this->assertSame(Command::SUCCESS, $tester->execute([]));
        $this->assertCount(1, $this->workflow->transitions);

        $this->assertSame(Command::INVALID, $tester->execute(['--limit' => 'x']));
    }

    public function testCommandTellsWhenSwitchedOff(): void
    {
        $tester = new CommandTester(new PublishReadyCommand($this->publisher(['sources' => ['ical.a']], enabled: false)));

        $this->assertSame(Command::SUCCESS, $tester->execute([]));
        $this->assertStringContainsString('switched off', $tester->getDisplay());
    }

    public function testConfigurationIsReadIntoRules(): void
    {
        $rules = AutoPublishRules::fromArray([
            'enabled'           => true,
            'when'              => ['series' => true, 'sources' => ['a'], 'types' => ['keynote']],
            'require'           => ['locales' => ['de', 'en'], 'category' => false, 'image' => true, 'place_or_online' => false],
            'republish_changes' => true,
        ]);

        $this->assertTrue($rules->enabled);
        $this->assertTrue($rules->series);
        $this->assertSame(['a'], $rules->sources);
        $this->assertSame(['de', 'en'], $rules->locales);
        $this->assertFalse($rules->requireCategory);
        $this->assertTrue($rules->requireImage);
        $this->assertFalse($rules->requirePlaceOrOnline);
        $this->assertTrue($rules->republishChanges);
    }

    /**
     * @return array<string, mixed> subtitle, summary, text, category, image and place: nothing missing
     */
    private function complete(): array
    {
        return self::COMPLETE + [
            'excerptCategories' => [$this->categories['web']->getId()],
            'excerpt'           => ['image' => ['id' => 5]],
            'locationId'        => $this->locations['Messe Berlin']->getId(),
        ];
    }

    /**
     * @param array<string, mixed> $when    conditions of "when" and, for convenience, "locales" of "require"
     * @param array<string, mixed> $require
     */
    private function publisher(array $when, bool $enabled = true, array $require = [], bool $republish = false): AutoPublisher
    {
        $this->entityManager->clear();

        $collector = $this->createStub(DomainEventCollectorInterface::class);
        $collector->method('collect')->willReturnCallback(function (object $event): void {
            $this->collected[] = $event;
        });

        $switcher = $this->createStub(ImportUserSwitcher::class);
        $switcher->method('runAsImportUser')->willReturnCallback(static fn (callable $callback): mixed => $callback());

        $locales = $when['locales'] ?? [];
        unset($when['locales']);

        $rules = AutoPublishRules::fromArray([
            'enabled'           => $enabled,
            'when'              => $when    + ['series' => false, 'sources' => [], 'types' => []],
            'require'           => $require + ['locales' => $locales],
            'republish_changes' => $republish,
        ]);

        return new AutoPublisher(
            $this->importRepository,
            $this->repository,
            $this->entityManager,
            $this->workflow,
            $collector,
            $switcher,
            $this->runRepository,
            $rules,
            $this->settings(),
        );
    }

    private function settings(): ImportSettings
    {
        return new ImportSettings(true, 'import', false, '/events', null, 'default', 'event', [], []);
    }

    /**
     * @param array<string, array<string, mixed>> $locales locale => data of the draft
     * @param string[]                            $locked
     */
    private function importedEvent(
        string $id,
        string $title,
        array $locales,
        string $source = 'ical.a',
        string $state = EventImport::STATE_ACTIVE,
        string $place = WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED,
        bool $wasPublished = false,
        ?string $matchKey = null,
        array $locked = [],
    ): string {
        $event = new Event();
        $this->entityManager->persist($event);
        foreach ($locales as $locale => $data) {
            $row = $this->contentManager->persist($event, $data + ['title' => $title, 'startDate' => '2099-06-01 10:00:00'], ['locale' => $locale, 'stage' => DimensionContentInterface::STAGE_DRAFT]);
            $row->setWorkflowPlace($place);
            if ($wasPublished) {
                $row->setWorkflowPublished(new \DateTimeImmutable('2027-01-01 10:00:00'));
            }
        }

        $import = new EventImport($source, $id);
        $import->setEvent($event)->setState($state)->setPriority(50)->setMatchKey($matchKey)->setLockedFields($locked);
        $this->entityManager->persist($import);
        $this->entityManager->flush();

        return $event->getUuid();
    }
}
