<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration\Import;

use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluEventBundle\Domain\Event\Event\CreatedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\ModifiedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\PublishedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\RemovedEvent;
use Manuxi\SuluEventBundle\Domain\Event\Event\RestoredEvent;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\EventImport;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Import\CandidateLocation;
use Manuxi\SuluEventBundle\Import\EventCandidate;
use Manuxi\SuluEventBundle\Import\EventImporter;
use Manuxi\SuluEventBundle\Import\EventImportStateListener;
use Manuxi\SuluEventBundle\Import\ImportReport;
use Manuxi\SuluEventBundle\Import\ImportSettings;
use Manuxi\SuluEventBundle\Import\ImportUrlGenerator;
use Manuxi\SuluEventBundle\Import\ImportUserSwitcher;
use Manuxi\SuluEventBundle\Import\Resolver\CategoryResolver;
use Manuxi\SuluEventBundle\Import\Resolver\LocationResolver;
use Manuxi\SuluEventBundle\Import\Resolver\MediaResolver;
use Manuxi\SuluEventBundle\Import\Resolver\TagResolver;
use Manuxi\SuluEventBundle\Import\Resolver\TimezoneGuesser;
use Manuxi\SuluEventBundle\Import\SourceRegistry;
use Manuxi\SuluEventBundle\Repository\EventImportRepository;
use Manuxi\SuluEventBundle\Tests\Integration\AbstractEventDatabaseTestCase;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;
use Sulu\Component\Security\Authentication\UserInterface;
use Sulu\Component\Security\Authentication\UserRepositoryInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Sulu\Route\Domain\Repository\RouteRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class EventImporterTest extends AbstractEventDatabaseTestCase
{
    private FakeContentManager $contentManager;
    private FakeContentWorkflow $workflow;
    private EventImportRepository $importRepository;
    private TokenStorage $tokenStorage;

    /** @var list<object> */
    private array $collected = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->contentManager = new FakeContentManager($this->entityManager);
        $this->workflow       = new FakeContentWorkflow();
        $this->collected      = [];
        $this->tokenStorage   = new TokenStorage();

        $registry = $this->createStub(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($this->entityManager);
        $this->importRepository = new EventImportRepository($registry);
    }

    public function testNewCandidateBecomesADraftWithLocationCategoriesAndTags(): void
    {
        $report = $this->import([new ArraySource('ical.a', 50, [$this->candidate()])]);

        $this->assertSame(1, $report->getCounts()['created']);
        $this->assertSame(1, $report->getCounts()['newLocations']);
        $this->assertSame('Messe Hall 3', $report->getNewLocations()[0]['name']);
        $this->assertSame('Europe/Berlin', $report->getNewLocations()[0]['timezone']);

        $import = $this->importRepository->findBySourceAndExternalId('ical.a', 'conf-2027');
        $this->assertNotNull($import);
        $this->assertSame(EventImport::STATE_ACTIVE, $import->getState());
        $this->assertSame('example-conf-2027', $import->getMatchKey());
        $this->assertSame(50, $import->getPriority());
        $this->assertSame('2027-06-12 10:00:00', $import->getImportedValues()['startDate']);
        $this->assertSame('About the conf', $import->getImportedValues()['_sourceDescription']);
        $this->assertSame('https://conf.example.com', $import->getSourceUrl());

        $row = $this->draft($import->getEvent()->getUuid(), 'en');
        $this->assertSame('Example Conf 2027', $row->getTitle());
        $this->assertSame('conference', $row->getType());
        $this->assertSame('2027-06-12 10:00:00', $row->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2027-06-13 17:00:00', $row->getEndDate()->format('Y-m-d H:i:s'));
        $this->assertSame('Europe/Berlin', $row->getTimezone());
        $this->assertSame('Messe Hall 3', $row->getLocation()->getName());
        $this->assertCount(1, $row->getExcerptCategories());
        $this->assertCount(1, $row->getExcerptTags());
        $this->assertSame(WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED, $row->getWorkflowPlace());
        $this->assertNull($row->getText(), 'the description of the source is no text');

        $data = $this->contentManager->calls[0]['data'];
        $this->assertSame('event', $data['template']);
        $this->assertSame('/events/example-conf-2027', $data['url']);
        $this->assertSame([], $this->workflow->transitions, 'nothing is published');
        $this->assertInstanceOf(CreatedEvent::class, $this->collected[0]);
    }

    public function testSecondRunWithTheSameCandidateChangesNothing(): void
    {
        $source = new ArraySource('ical.a', 50, [$this->candidate()]);
        $this->import([$source]);
        $calls = \count($this->contentManager->calls);

        $report = $this->import([$source]);

        $this->assertSame(['created' => 0, 'updated' => 0, 'unchanged' => 1, 'locked' => 0, 'vanished' => 0, 'rejected' => 0, 'errors' => 0, 'newLocations' => 0], $report->getCounts());
        $this->assertCount($calls, $this->contentManager->calls);
        $this->assertSame(1, $this->rowCount(Event::class));
        $this->assertSame(4, $this->rowCount(Location::class), 'the three of the harness and the new one');
    }

    public function testChangedCandidateUpdatesTheDraft(): void
    {
        $source = new ArraySource('ical.a', 50, [$this->candidate()]);
        $this->import([$source]);

        $source->candidates = [$this->candidate(startDate: new \DateTimeImmutable('2027-06-14 09:30:00', new \DateTimeZone('Europe/Berlin')), registrationUrl: 'https://conf.example.com/tickets')];
        $report             = $this->import([$source]);

        $this->assertSame(1, $report->getCounts()['updated']);
        $this->assertSame(0, $report->getCounts()['locked']);
        $import = $this->importRepository->findBySourceAndExternalId('ical.a', 'conf-2027');
        $row    = $this->draft($import->getEvent()->getUuid(), 'en');
        $this->assertSame('2027-06-14 09:30:00', $row->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('https://conf.example.com/tickets', $row->getRegistrationUrl());
        $this->assertSame('2027-06-14 09:30:00', $import->getImportedValues()['startDate']);
        $this->assertInstanceOf(ModifiedEvent::class, $this->collected[\count($this->collected) - 1]);
        $this->assertSame([], $this->workflow->transitions);
    }

    public function testFieldChangedInTheAdminIsLockedAndNotOverwritten(): void
    {
        $source = new ArraySource('ical.a', 50, [$this->candidate()]);
        $this->import([$source]);

        $import = $this->importRepository->findBySourceAndExternalId('ical.a', 'conf-2027');
        $this->draft($import->getEvent()->getUuid(), 'en')->setTitle('Our own title');
        $this->entityManager->flush();

        $source->candidates = [$this->candidate(
            title: 'Example Conf 2027 - new name',
            startDate: new \DateTimeImmutable('2027-06-14 09:30:00', new \DateTimeZone('Europe/Berlin')),
        )];
        $report = $this->import([$source]);

        $this->assertSame(1, $report->getCounts()['updated']);
        $this->assertSame(1, $report->getCounts()['locked']);
        $this->assertSame(['title:en'], $report->getEntries()[0]['lockedFields']);

        $import = $this->importRepository->findBySourceAndExternalId('ical.a', 'conf-2027');
        $row    = $this->draft($import->getEvent()->getUuid(), 'en');
        $this->assertSame('Our own title', $row->getTitle());
        $this->assertSame('2027-06-14 09:30:00', $row->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame(['title:en'], $import->getLockedFields());

        // and it stays locked, the difference is reported again
        $report = $this->import([$source]);
        $this->assertSame(1, $report->getCounts()['locked']);
        $this->assertSame(1, $report->getCounts()['unchanged']);
        $this->assertSame('Our own title', $this->draft($import->getEvent()->getUuid(), 'en')->getTitle());
    }

    public function testTextsCategoriesAndTagsAreOnlyFilledIn(): void
    {
        $source = new ArraySource('ical.a', 50, [$this->candidate(categoryKeys: [], tags: [])]);
        $this->import([$source]);

        $source->candidates = [$this->candidate(texts: ['en' => '<p>First text</p>'], summaries: ['en' => 'Short'])];
        $report             = $this->import([$source]);
        $this->assertSame(1, $report->getCounts()['updated']);
        $this->assertStringContainsString('summary (filled in), text (filled in)', $report->getEntries()[0]['detail']);

        $import = $this->importRepository->findBySourceAndExternalId('ical.a', 'conf-2027');
        $row    = $this->draft($import->getEvent()->getUuid(), 'en');
        $this->assertSame('<p>First text</p>', $row->getText());
        $this->assertSame('Short', $row->getSummary());
        $this->assertCount(1, $row->getExcerptCategories());
        $this->assertCount(1, $row->getExcerptTags());

        $source->candidates = [$this->candidate(texts: ['en' => '<p>Second text</p>'])];
        $report             = $this->import([$source]);
        $this->assertSame(1, $report->getCounts()['unchanged']);
        $this->assertSame('<p>First text</p>', $this->draft($import->getEvent()->getUuid(), 'en')->getText());
    }

    public function testNewLanguageIsAddedToTheEvent(): void
    {
        $source = new ArraySource('ical.a', 50, [$this->candidate()]);
        $this->import([$source]);

        $source->candidates = [$this->candidate(titles: ['en' => 'Example Conf 2027', 'de' => 'Beispiel-Konferenz 2027'])];
        $report             = $this->import([$source]);

        $this->assertSame(1, $report->getCounts()['updated']);
        $import = $this->importRepository->findBySourceAndExternalId('ical.a', 'conf-2027');
        $german = $this->draft($import->getEvent()->getUuid(), 'de');
        $this->assertSame('Beispiel-Konferenz 2027', $german->getTitle());
        $this->assertSame('2027-06-12 10:00:00', $german->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('/events/beispiel-konferenz-2027', $this->contentManager->calls[\count($this->contentManager->calls) - 1]['data']['url']);
    }

    public function testDeletedEventIsNotCreatedAgainAndRestoringBringsItBack(): void
    {
        $source = new ArraySource('ical.a', 50, [$this->candidate()]);
        $this->import([$source]);
        $import   = $this->importRepository->findBySourceAndExternalId('ical.a', 'conf-2027');
        $oldUuid  = $import->getEvent()->getUuid();
        $listener = new EventImportStateListener($this->entityManager);

        // deleted in the admin
        $this->entityManager->remove($this->repository->findByUuid($oldUuid));
        $this->entityManager->flush();
        $listener->onRemoved(new RemovedEvent($oldUuid, 'Example Conf 2027'));
        $this->entityManager->clear();

        $import = $this->importRepository->findBySourceAndExternalId('ical.a', 'conf-2027');
        $this->assertSame(EventImport::STATE_REJECTED, $import->getState());
        $this->assertNull($import->getEvent());
        $this->assertSame($oldUuid, $import->getLastEventUuid());

        $report = $this->import([$source]);
        $this->assertSame(1, $report->getCounts()['rejected']);
        $this->assertSame(0, $report->getCounts()['created']);
        $this->assertSame(0, $this->rowCount(Event::class));

        // restored from the trash: a new uuid
        $restored = new Event();
        $this->entityManager->persist($restored);
        $this->contentManager->persist($restored, ['title' => 'Example Conf 2027', 'startDate' => '2027-06-12 10:00:00', 'type' => 'conference'], ['locale' => 'en', 'stage' => DimensionContentInterface::STAGE_DRAFT]);
        $this->entityManager->flush();
        $listener->onRestored(new RestoredEvent($restored, ['id' => $oldUuid]));
        $this->entityManager->clear();

        $import = $this->importRepository->findBySourceAndExternalId('ical.a', 'conf-2027');
        $this->assertSame(EventImport::STATE_ACTIVE, $import->getState());
        $this->assertSame($restored->getUuid(), $import->getEvent()->getUuid());

        $report = $this->import([$source]);
        $this->assertSame(0, $report->getCounts()['rejected']);
        $this->assertSame(0, $report->getCounts()['created']);
        $this->assertSame(1, $this->rowCount(Event::class));
    }

    public function testCandidateOfAnotherSourceForADeletedEventIsRejectedToo(): void
    {
        $official = new ArraySource('jsonld.official', 100, [$this->candidate('official-1')]);
        $this->import([$official]);
        $import = $this->importRepository->findBySourceAndExternalId('jsonld.official', 'official-1');
        $uuid   = $import->getEvent()->getUuid();

        $this->entityManager->remove($this->repository->findByUuid($uuid));
        $this->entityManager->flush();
        (new EventImportStateListener($this->entityManager))->onRemoved(new RemovedEvent($uuid));
        $this->entityManager->clear();

        $report = $this->import([new ArraySource('ical.cal', 50, [$this->candidate('cal-9')])]);

        $this->assertSame(1, $report->getCounts()['rejected']);
        $this->assertSame(0, $this->rowCount(Event::class));
    }

    public function testCandidateThatIsNotDeliveredAnymoreVanishesButTheEventStays(): void
    {
        $source = new ArraySource('ical.a', 50, [$this->candidate(), $this->candidate('other-2027', title: 'Other event 2027', series: 'other')]);
        $this->import([$source]);

        $source->candidates = [$this->candidate()];
        $report             = $this->import([$source]);

        $this->assertSame(1, $report->getCounts()['vanished']);
        $vanished = array_values(array_filter($report->getEntries(), static fn (array $entry): bool => ImportReport::ACTION_VANISHED === $entry['action']));
        $this->assertSame('Other event 2027', $vanished[0]['title']);
        $import = $this->importRepository->findBySourceAndExternalId('ical.a', 'other-2027');
        $this->assertSame(EventImport::STATE_VANISHED, $import->getState());
        $this->assertNotNull($import->getEvent());
        $this->assertSame(2, $this->rowCount(Event::class));

        // it is not reported again, and comes back to "active" when the source delivers it again
        $this->assertSame(0, $this->import([$source])->getCounts()['vanished']);
        $source->candidates = [$this->candidate(), $this->candidate('other-2027', title: 'Other event 2027', series: 'other')];
        $this->import([$source]);
        $this->assertSame(EventImport::STATE_ACTIVE, $this->importRepository->findBySourceAndExternalId('ical.a', 'other-2027')->getState());
    }

    public function testEventsInThePastDoNotVanish(): void
    {
        $source = new ArraySource('ical.a', 50, [$this->candidate()]);
        $this->import([$source]);

        $source->candidates = [];
        $report             = $this->import([$source], now: '2027-07-01 12:00:00');

        $this->assertSame(0, $report->getCounts()['vanished']);
    }

    public function testFailingSourceIsReportedAndNothingVanishes(): void
    {
        $failing = new ArraySource('ical.a', 50, [$this->candidate()]);
        $this->import([$failing]);

        $failing->failure = new \RuntimeException('robots.txt does not allow it');
        $good             = new ArraySource('jsonld.b', 100, [$this->candidate('b-1', title: 'Another Conf 2027', series: 'another')]);
        $report           = $this->import([$failing, $good]);

        $this->assertSame(1, $report->getCounts()['errors']);
        $this->assertSame(1, $report->getCounts()['created']);
        $this->assertSame(0, $report->getCounts()['vanished']);
        $this->assertTrue($report->hasErrors());
        $this->assertStringContainsString('robots.txt', $report->getEntries()[0]['detail']);
        $this->assertSame(EventImport::STATE_ACTIVE, $this->importRepository->findBySourceAndExternalId('ical.a', 'conf-2027')->getState());
    }

    public function testCandidateWithoutStartDateIsAnError(): void
    {
        $report = $this->import([new ArraySource('ical.a', 50, [$this->candidate(startDate: null), $this->candidate('ok-1', title: 'Fine 2027', series: 'fine')])]);

        $this->assertSame(1, $report->getCounts()['errors']);
        $this->assertSame(1, $report->getCounts()['created']);
    }

    public function testDuplicateFromTwoSourcesIsOneEventMaintainedByTheHigherPriority(): void
    {
        $calendar = new ArraySource('ical.cal', 50, [$this->candidate('cal-1')]);
        $official = new ArraySource('jsonld.official', 100, [$this->candidate('official-1', endDate: new \DateTimeImmutable('2027-06-14 18:00:00', new \DateTimeZone('Europe/Berlin')), registrationUrl: 'https://conf.example.com/tickets')]);

        $report = $this->import([$calendar, $official]);

        $this->assertSame(1, $report->getCounts()['created']);
        $this->assertSame(1, $report->getCounts()['updated'], 'the official page completes the event');
        $this->assertSame(1, $this->rowCount(Event::class));
        $this->assertSame(2, $this->rowCount(EventImport::class));

        $first  = $this->importRepository->findBySourceAndExternalId('ical.cal', 'cal-1');
        $second = $this->importRepository->findBySourceAndExternalId('jsonld.official', 'official-1');
        $this->assertSame($first->getEvent()->getUuid(), $second->getEvent()->getUuid());
        $row = $this->draft($first->getEvent()->getUuid(), 'en');
        $this->assertSame('2027-06-14 18:00:00', $row->getEndDate()->format('Y-m-d H:i:s'));
        $this->assertSame('https://conf.example.com/tickets', $row->getRegistrationUrl());

        // the calendar changes the date: the lower priority does not overwrite the official page
        $calendar->candidates = [$this->candidate('cal-1', startDate: new \DateTimeImmutable('2027-06-20 10:00:00', new \DateTimeZone('Europe/Berlin')))];
        $report               = $this->import([$calendar, $official]);

        $this->assertSame(0, $report->getCounts()['updated']);
        $this->assertSame('2027-06-12 10:00:00', $this->draft($first->getEvent()->getUuid(), 'en')->getStartDate()->format('Y-m-d H:i:s'));
    }

    public function testEventsOfTheSameSourceAndSeriesAreSeparateEvents(): void
    {
        $source = new ArraySource('ical.cal', 50, [
            $this->candidate('week-1', title: 'Weekly meetup'),
            $this->candidate('week-2', title: 'Weekly meetup', startDate: new \DateTimeImmutable('2027-06-19 10:00:00', new \DateTimeZone('Europe/Berlin'))),
        ]);

        $report = $this->import([$source]);

        $this->assertSame(2, $report->getCounts()['created']);
        $this->assertSame(2, $this->rowCount(Event::class));
    }

    public function testDeletedEventOfTheSameSourceDoesNotBlockItsSeries(): void
    {
        $source = new ArraySource('ical.cal', 50, [$this->candidate('week-1', title: 'Weekly meetup')]);
        $this->import([$source]);
        $import = $this->importRepository->findBySourceAndExternalId('ical.cal', 'week-1');
        $uuid   = $import->getEvent()->getUuid();
        $this->entityManager->remove($this->repository->findByUuid($uuid));
        $this->entityManager->flush();
        (new EventImportStateListener($this->entityManager))->onRemoved(new RemovedEvent($uuid));
        $this->entityManager->clear();

        $source->candidates = [$this->candidate('week-2', title: 'Weekly meetup', startDate: new \DateTimeImmutable('2027-06-19 10:00:00', new \DateTimeZone('Europe/Berlin')))];
        $report             = $this->import([$source]);

        $this->assertSame(1, $report->getCounts()['created']);
        $this->assertSame(0, $report->getCounts()['rejected']);
    }

    public function testNewEventGetsTheImageOfItsCategory(): void
    {
        $settings = new ImportSettings(true, 'import', false, '/events', null, 'default', 'event', ['other' => 5, 'web' => 77]);

        $this->import([new ArraySource('ical.a', 50, [$this->candidate(categoryKeys: ['unmapped', 'web'])])], settings: $settings);

        $data = $this->contentManager->calls[0]['data'];
        $this->assertSame(77, $data['image']);
        $this->assertSame(['image' => ['id' => 77]], $data['excerpt']);

        $uuid = $this->importRepository->findBySourceAndExternalId('ical.a', 'conf-2027')->getEvent()->getUuid();
        $this->assertSame(['id' => 77], $this->draft($uuid, 'en')->getExcerptImage());
    }

    public function testImageOfTheSourceWinsOverTheOneOfTheCategory(): void
    {
        $settings = new ImportSettings(true, 'import', false, '/events', null, 'default', 'event', ['web' => 77]);

        $this->import([new ArraySource('ical.a', 50, [$this->candidate(categoryKeys: ['web'], imageMediaId: 55)])], settings: $settings);

        $this->assertSame(55, $this->contentManager->calls[0]['data']['image']);
    }

    public function testNoImageConfiguredMeansNoImageKeys(): void
    {
        $this->import([new ArraySource('ical.a', 50, [$this->candidate()])]);

        $this->assertArrayNotHasKey('image', $this->contentManager->calls[0]['data']);
        $this->assertArrayNotHasKey('excerpt', $this->contentManager->calls[0]['data']);
    }

    public function testMissingMediaIsAWarningAndNotAnError(): void
    {
        $settings = new ImportSettings(true, 'import', false, '/events', null, 'default', 'event', ['web' => 77]);
        $importer = $this->createImporter([new ArraySource('ical.a', 50, [$this->candidate()])], $settings, mediaExists: false);

        $report = $importer->import();

        $this->assertSame(1, $report->getCounts()['created']);
        $this->assertSame(0, $report->getCounts()['errors']);
        $this->assertStringContainsString('image with the id 77 does not exist', $report->getWarnings()[0]['message']);
        $this->assertArrayNotHasKey('image', $this->contentManager->calls[0]['data']);
    }

    public function testImageIsFilledInOnAnExistingEventThatHasNone(): void
    {
        $source = new ArraySource('ical.a', 50, [$this->candidate()]);
        $this->import([$source]);

        $settings = new ImportSettings(true, 'import', false, '/events', null, 'default', 'event', ['web' => 77]);
        $report   = $this->import([$source], settings: $settings);

        $this->assertSame(1, $report->getCounts()['updated']);
        $this->assertStringContainsString('image (filled in)', $report->getEntries()[0]['detail']);
        $uuid = $this->importRepository->findBySourceAndExternalId('ical.a', 'conf-2027')->getEvent()->getUuid();
        $this->assertSame(['id' => 77], $this->draft($uuid, 'en')->getExcerptImage());
    }

    public function testExistingLocationByIdIsUsed(): void
    {
        $id = $this->locations['Messe Berlin']->getId();

        $report = $this->import([new ArraySource('ical.a', 50, [$this->candidate(location: null, locationId: $id)])]);

        $this->assertSame(0, $report->getCounts()['newLocations']);
        $uuid = $this->importRepository->findBySourceAndExternalId('ical.a', 'conf-2027')->getEvent()->getUuid();
        $this->assertSame('Messe Berlin', $this->draft($uuid, 'en')->getLocation()->getName());
    }

    public function testUnknownLocationIdIsAWarningAndTheLocationOfTheCandidateIsUsed(): void
    {
        $importer = $this->createImporter([]);

        $report = $importer->importCandidate('mcp.editorial', 30, $this->candidate(locationId: 99999));

        $this->assertSame(1, $report->getCounts()['created']);
        $this->assertSame(1, $report->getCounts()['newLocations']);
        $this->assertStringContainsString('99999', $report->getWarnings()[0]['message']);
    }

    public function testSingleCandidateRunsThroughTheSameRules(): void
    {
        $importer = $this->createImporter([]);
        $first    = $importer->importCandidate('mcp.editorial', 30, $this->candidate('claude-1'));
        $this->entityManager->clear();
        $again = $this->createImporter([])->importCandidate('mcp.editorial', 30, $this->candidate('claude-1'));
        $this->entityManager->clear();
        $changed = $this->createImporter([])->importCandidate('mcp.editorial', 30, $this->candidate('claude-1', startDate: new \DateTimeImmutable('2027-06-14 09:30:00', new \DateTimeZone('Europe/Berlin'))));

        $this->assertSame(1, $first->getCounts()['created']);
        $this->assertSame(1, $again->getCounts()['unchanged']);
        $this->assertSame(1, $changed->getCounts()['updated']);
        $this->assertNull($this->tokenStorage->getToken(), 'it does not switch the user');
        $this->assertSame('mcp.editorial', $first->getEntries()[0]['source']);
    }

    public function testSingleCandidateErrorIsReportedNotThrown(): void
    {
        $report = $this->createImporter([])->importCandidate('mcp.editorial', 30, $this->candidate('claude-2', startDate: null));

        $this->assertSame(1, $report->getCounts()['errors']);
    }

    public function testSameRankKeepsTheOlderLink(): void
    {
        $first  = new ArraySource('ical.one', 50, [$this->candidate('one-1')]);
        $second = new ArraySource('ical.two', 50, [$this->candidate('two-1', startDate: new \DateTimeImmutable('2027-06-12 11:00:00', new \DateTimeZone('Europe/Berlin')))]);

        $report = $this->import([$first, $second]);

        $this->assertSame(1, $report->getCounts()['created']);
        $this->assertSame(0, $report->getCounts()['updated'], 'the older link of the same rank keeps the event');
        $uuid = $this->importRepository->findBySourceAndExternalId('ical.one', 'one-1')->getEvent()->getUuid();
        $this->assertSame('2027-06-12 10:00:00', $this->draft($uuid, 'en')->getStartDate()->format('Y-m-d H:i:s'));
    }

    public function testChangesToAPublishedEventGoToTheDraftOnly(): void
    {
        $source = new ArraySource('ical.a', 50, [$this->candidate()]);
        $this->import([$source]);
        $uuid = $this->importRepository->findBySourceAndExternalId('ical.a', 'conf-2027')->getEvent()->getUuid();
        $this->draft($uuid, 'en')->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);
        $this->entityManager->flush();

        $source->candidates = [$this->candidate(startDate: new \DateTimeImmutable('2027-06-14 09:30:00', new \DateTimeZone('Europe/Berlin')))];
        $this->import([$source]);

        $this->assertSame([['locale' => 'en', 'transition' => WorkflowInterface::WORKFLOW_TRANSITION_CREATE_DRAFT]], $this->workflow->transitions);
        $this->assertSame(WorkflowInterface::WORKFLOW_PLACE_DRAFT, $this->draft($uuid, 'en')->getWorkflowPlace());
    }

    public function testDateChangeGoesLiveWhenAllowedAndNothingElseIsWaiting(): void
    {
        $source = new ArraySource('ical.a', 50, [$this->candidate()]);
        $this->import([$source]);
        $uuid = $this->importRepository->findBySourceAndExternalId('ical.a', 'conf-2027')->getEvent()->getUuid();
        $this->draft($uuid, 'en')->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);
        $this->entityManager->flush();

        $source->candidates = [$this->candidate(startDate: new \DateTimeImmutable('2027-06-14 09:30:00', new \DateTimeZone('Europe/Berlin')), status: 'postponed')];
        $this->import([$source], settings: new ImportSettings(true, 'import', true));

        $this->assertSame(
            [WorkflowInterface::WORKFLOW_TRANSITION_CREATE_DRAFT, WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH],
            array_column($this->workflow->transitions, 'transition')
        );
        $this->assertSame(WorkflowInterface::WORKFLOW_PLACE_PUBLISHED, $this->draft($uuid, 'en')->getWorkflowPlace());
        $this->assertInstanceOf(PublishedEvent::class, $this->collected[\count($this->collected) - 1]);
    }

    public function testOtherChangesAreNeverPublishedEvenIfDatesMayGoLive(): void
    {
        $source = new ArraySource('ical.a', 50, [$this->candidate()]);
        $this->import([$source]);
        $uuid = $this->importRepository->findBySourceAndExternalId('ical.a', 'conf-2027')->getEvent()->getUuid();
        $this->draft($uuid, 'en')->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);
        $this->entityManager->flush();

        $source->candidates = [$this->candidate(title: 'Example Conf 2027 - renamed', startDate: new \DateTimeImmutable('2027-06-14 09:30:00', new \DateTimeZone('Europe/Berlin')))];
        $this->import([$source], settings: new ImportSettings(true, 'import', true));

        $this->assertSame([WorkflowInterface::WORKFLOW_TRANSITION_CREATE_DRAFT], array_column($this->workflow->transitions, 'transition'));
    }

    public function testDateChangeDoesNotGoLiveIfTheDraftHasOtherChangesWaiting(): void
    {
        $source = new ArraySource('ical.a', 50, [$this->candidate()]);
        $this->import([$source]);
        $uuid = $this->importRepository->findBySourceAndExternalId('ical.a', 'conf-2027')->getEvent()->getUuid();
        $this->draft($uuid, 'en')->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_DRAFT);
        $this->entityManager->flush();

        $source->candidates = [$this->candidate(startDate: new \DateTimeImmutable('2027-06-14 09:30:00', new \DateTimeZone('Europe/Berlin')))];
        $this->import([$source], settings: new ImportSettings(true, 'import', true));

        $this->assertSame([], $this->workflow->transitions);
    }

    public function testCancelledCandidateIsNotCreated(): void
    {
        $report = $this->import([new ArraySource('ical.a', 50, [$this->candidate(status: 'cancelled')])]);

        $this->assertSame(0, $report->getCounts()['created']);
        $this->assertSame(0, $this->rowCount(Event::class));
    }

    public function testCancellationOfAKnownEventIsTakenOver(): void
    {
        $source = new ArraySource('ical.a', 50, [$this->candidate()]);
        $this->import([$source]);

        $source->candidates = [$this->candidate(status: 'cancelled')];
        $this->import([$source]);

        $uuid = $this->importRepository->findBySourceAndExternalId('ical.a', 'conf-2027')->getEvent()->getUuid();
        $this->assertSame('cancelled', $this->draft($uuid, 'en')->getStatus());
    }

    public function testDryRunWritesNothing(): void
    {
        $report = $this->import([new ArraySource('ical.a', 50, [$this->candidate()])], dryRun: true);

        $this->assertSame(1, $report->getCounts()['created']);
        $this->assertSame(1, $report->getCounts()['newLocations']);
        $this->assertSame([], $this->contentManager->calls);
        $this->assertSame(0, $this->rowCount(Event::class));
        $this->assertSame(0, $this->rowCount(EventImport::class));
        $this->assertSame(3, $this->rowCount(Location::class));
        $this->assertSame([], $this->collected);
    }

    public function testLimitStopsEachSourceAndSkipsTheVanishedCheck(): void
    {
        $source = new ArraySource('ical.a', 50, [
            $this->candidate('a-1', title: 'First 2027', series: 'first'),
            $this->candidate('a-2', title: 'Second 2027', series: 'second'),
            $this->candidate('a-3', title: 'Third 2027', series: 'third'),
        ]);

        $report = $this->import([$source], limit: 2);
        $this->assertSame(2, $report->getCounts()['created']);

        $source->candidates = [
            $this->candidate('a-3', title: 'Third 2027', series: 'third'),
            $this->candidate('a-4', title: 'Fourth 2027', series: 'fourth'),
            $this->candidate('a-5', title: 'Fifth 2027', series: 'fifth'),
        ];
        $report = $this->import([$source], limit: 2);
        $this->assertSame(2, $report->getCounts()['created']);
        $this->assertSame(0, $report->getCounts()['vanished'], 'with a limit that was reached the source is not complete');

        $source->candidates = [$this->candidate('a-3', title: 'Third 2027', series: 'third')];
        $this->assertSame(3, $this->import([$source])->getCounts()['vanished']);
    }

    public function testOnlyTheNamedSourceIsRun(): void
    {
        $a = new ArraySource('ical.a', 50, [$this->candidate('a-1')]);
        $b = new ArraySource('ical.b', 50, [$this->candidate('b-1', title: 'Other 2027', series: 'other')]);

        $report = $this->import([$a, $b], sourceKey: 'ical.b');

        $this->assertSame(1, $report->getCounts()['created']);
        $this->assertNull($this->importRepository->findBySourceAndExternalId('ical.a', 'a-1'));
    }

    public function testUnknownSourceIsAClearError(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Known sources: ical.a');

        $this->import([new ArraySource('ical.a', 50)], sourceKey: 'ical.zzz');
    }

    public function testImportRunsAsTheConfiguredUserAndLeavesNoTokenBehind(): void
    {
        $this->import([new ArraySource('ical.a', 50, [$this->candidate()])]);

        $this->assertNull($this->tokenStorage->getToken());
    }

    public function testMissingImportUserIsAClearError(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('sulu_event.import.user');

        $this->import([new ArraySource('ical.a', 50)], settings: new ImportSettings(true, null));
    }

    /**
     * @param list<ArraySource> $sources
     */
    private function import(
        array $sources,
        bool $dryRun = false,
        ?int $limit = null,
        ?string $sourceKey = null,
        ?ImportSettings $settings = null,
        string $now = '2027-03-01 12:00:00',
    ): ImportReport {
        // every run is a new process: nothing is kept from the one before
        $this->entityManager->clear();

        $importer = $this->createImporter($sources, $settings, $now);

        $report = $importer->import($sourceKey, $dryRun, $limit);
        $this->entityManager->clear();

        return $report;
    }

    /**
     * @param list<ArraySource> $sources
     */
    private function createImporter(array $sources, ?ImportSettings $settings = null, string $now = '2027-03-01 12:00:00', bool $mediaExists = true): EventImporter
    {
        $settings ??= new ImportSettings(true, 'import', false);

        $collector = $this->createMock(DomainEventCollectorInterface::class);
        $collector->method('collect')->willReturnCallback(function (object $event): void {
            $this->collected[] = $event;
        });

        $category = $this->createStub(CategoryInterface::class);
        $category->method('getId')->willReturn($this->categories['web']->getId());
        $categoryResolver = $this->createStub(CategoryResolver::class);
        $categoryResolver->method('resolve')->willReturnCallback(static fn (array $keys): array => [[] === $keys ? [] : [$category], []]);

        $tag = $this->createStub(TagInterface::class);
        $tag->method('getId')->willReturn($this->tags['symfony']->getId());
        $tagResolver = $this->createStub(TagResolver::class);
        $tagResolver->method('resolve')->willReturnCallback(static fn (array $names): array => [] === $names ? [] : [$tag]);

        $user           = $this->createStub(UserInterface::class);
        $userRepository = $this->createStub(UserRepositoryInterface::class);
        $userRepository->method('findUserByIdentifier')->willReturnCallback(static fn (string $name): ?UserInterface => 'import' === $name ? $user : null);

        $routes = $this->createStub(RouteRepositoryInterface::class);
        $routes->method('existBy')->willReturn(false);

        $mediaResolver = $this->createStub(MediaResolver::class);
        $mediaResolver->method('exists')->willReturn($mediaExists);

        return new EventImporter(
            new SourceRegistry($sources),
            $this->entityManager,
            $this->importRepository,
            $this->repository,
            $this->contentManager,
            $this->workflow,
            $collector,
            new LocationResolver($this->entityManager, new TimezoneGuesser()),
            $categoryResolver,
            $tagResolver,
            new ImportUrlGenerator($routes, '/events'),
            new ImportUserSwitcher($this->tokenStorage, $userRepository, $settings),
            $settings,
            $mediaResolver,
            static fn (): \DateTimeImmutable => new \DateTimeImmutable($now, new \DateTimeZone('Europe/Berlin')),
        );
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function candidate(string $externalId = 'conf-2027', string $title = 'Example Conf 2027', ?string $series = 'example-conf', mixed ...$overrides): EventCandidate
    {
        $defaults = [
            'externalId'        => $externalId,
            'titles'            => ['en' => $title],
            'startDate'         => new \DateTimeImmutable('2027-06-12 10:00:00', new \DateTimeZone('Europe/Berlin')),
            'endDate'           => new \DateTimeImmutable('2027-06-13 17:00:00', new \DateTimeZone('Europe/Berlin')),
            'timezone'          => 'Europe/Berlin',
            'location'          => new CandidateLocation('Messe Hall 3', city: 'Cologne', countryCode: 'DE', latitude: 50.94, longitude: 6.98),
            'type'              => 'conference',
            'categoryKeys'      => ['web'],
            'tags'              => ['Symfony'],
            'series'            => $series,
            'sourceUrl'         => 'https://conf.example.com',
            'sourceDescription' => 'About the conf',
        ];
        if (isset($overrides['titles'])) {
            unset($defaults['titles']);
        }

        return new EventCandidate(...array_merge($defaults, $overrides));
    }

    /**
     * @param class-string $class
     */
    private function rowCount(string $class): int
    {
        return (int) $this->entityManager->createQuery(\sprintf('SELECT COUNT(e) FROM %s e', $class))->getSingleScalarResult();
    }

    private function draft(string $uuid, string $locale): EventDimensionContent
    {
        $this->entityManager->clear();
        $event = $this->repository->findByUuid($uuid);
        foreach ($event->getDimensionContents() as $row) {
            if (DimensionContentInterface::STAGE_DRAFT === $row->getStage() && $locale === $row->getLocale()) {
                return $row;
            }
        }

        $this->fail(\sprintf('The event %s has no draft in "%s".', $uuid, $locale));
    }
}
