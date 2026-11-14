<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration\Import;

use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluEventBundle\Command\PruneImportCommand;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\EventImport;
use Manuxi\SuluEventBundle\Import\ImportPruner;
use Manuxi\SuluEventBundle\Import\ImportSettings;
use Manuxi\SuluEventBundle\Import\PendingEventFinder;
use Manuxi\SuluEventBundle\Mcp\Tool\EventImportRejectTool;
use Manuxi\SuluEventBundle\Repository\EventImportRepository;
use Manuxi\SuluEventBundle\Tests\Integration\AbstractEventDatabaseTestCase;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * The tool that rejects an imported event, the pending list that leaves sieved out events out, and the command that moves
 * the unpublished ones among them to the trash.
 */
class ImportRejectAndPruneTest extends AbstractEventDatabaseTestCase
{
    private FakeContentManager $contentManager;
    private EventImportRepository $importRepository;

    /** @var list<string> */
    private array $trashed = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->contentManager = new FakeContentManager($this->entityManager);
        $registry             = $this->createStub(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($this->entityManager);
        $this->importRepository = new EventImportRepository($registry);
        $this->trashed          = [];
    }

    public function testRejectByUuidKeepsTheEventAndTheReason(): void
    {
        $uuid = $this->importedEvent('a', 'Event A');

        $result = $this->tool()->reject('Outside the target region.', uuid: $uuid);

        $this->assertTrue($result['success']);
        $this->assertSame('ical.a', $result['rejected'][0]['source']);
        $this->entityManager->clear();
        $import = $this->importRepository->findBySourceAndExternalId('ical.a', 'a');
        $this->assertSame(EventImport::STATE_REJECTED, $import->getState());
        $this->assertSame('Outside the target region.', $import->getStateReason());
        $this->assertSame($uuid, $import->getEvent()->getUuid(), 'the event is not deleted');
        $this->assertNotNull($this->repository->findByUuid($uuid));
    }

    public function testRejectBySourceAndExternalIdAlsoWorksForACandidateThatIsNoEventYet(): void
    {
        $result = $this->tool()->reject('Not for us.', source: 'editorial', externalId: 'https://conf.example.com/');

        $this->assertTrue($result['success']);
        $this->entityManager->clear();
        $import = $this->importRepository->findBySourceAndExternalId('editorial', 'https://conf.example.com/');
        $this->assertSame(EventImport::STATE_REJECTED, $import->getState());
        $this->assertNull($import->getEvent());
    }

    public function testRejectNeedsAReasonAndExactlyOneWayToNameTheEvent(): void
    {
        $uuid = $this->importedEvent('a', 'Event A');

        $this->assertStringContainsString('reason', $this->tool()->reject('  ', uuid: $uuid)['error']);
        $this->assertStringContainsString('either "uuid" or both', $this->tool()->reject('Why', uuid: $uuid, source: 'ical.a', externalId: 'a')['error']);
        $this->assertStringContainsString('either "uuid" or both', $this->tool()->reject('Why')['error']);
        $this->assertStringContainsString('does not come from the import', $this->tool()->reject('Why', uuid: 'f00')['error']);
        $this->assertSame(EventImport::STATE_ACTIVE, $this->importRepository->findBySourceAndExternalId('ical.a', 'a')->getState());
    }

    public function testPendingListLeavesOutSievedOutAndRejectedEvents(): void
    {
        $this->importedEvent('open', 'Open');
        $this->importedEvent('filtered', 'Filtered', state: EventImport::STATE_FILTERED, reason: 'The country FR is excluded.');
        $this->importedEvent('rejected', 'Rejected', state: EventImport::STATE_REJECTED, reason: 'No.');

        $this->entityManager->clear();
        $finder = new PendingEventFinder($this->importRepository, $this->repository, new ImportSettings(true, 'import', false, '/events', null, 'default', 'event', [], ['en']));

        $this->assertSame(['Open'], array_column($finder->find()['items'], 'title'));
    }

    public function testPruneListsAndMovesOnlyNeverPublishedEventsToTheTrash(): void
    {
        $draft     = $this->importedEvent('draft', 'Draft only', state: EventImport::STATE_FILTERED, reason: 'The country FR is excluded.');
        $published = $this->importedEvent('live', 'Was published', state: EventImport::STATE_FILTERED, reason: 'The country FR is excluded.', place: WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);
        $liveRow   = $this->importedEvent('liverow', 'Has a live version', state: EventImport::STATE_REJECTED, reason: 'No.', withLiveRow: true);
        $shared    = $this->importedEvent('shared', 'Maintained elsewhere', state: EventImport::STATE_FILTERED, reason: 'x', alsoActiveFrom: 'jsonld.official');
        $active    = $this->importedEvent('open', 'Open');

        $items   = $this->pruner()->find([EventImport::STATE_FILTERED, EventImport::STATE_REJECTED]);
        $byTitle = array_column($items, null, 'title');

        $this->assertCount(4, $items, 'the active one is no candidate for cleaning up');
        $this->assertTrue($byTitle['Draft only']['removable']);
        $this->assertFalse($byTitle['Was published']['removable']);
        $this->assertStringContainsString('published', $byTitle['Was published']['note']);
        $this->assertFalse($byTitle['Has a live version']['removable']);
        $this->assertFalse($byTitle['Maintained elsewhere']['removable']);
        $this->assertStringContainsString('another source', $byTitle['Maintained elsewhere']['note']);
        $this->assertSame('The country FR is excluded.', $byTitle['Draft only']['reason']);

        $this->assertCount(1, $this->pruner()->find([EventImport::STATE_REJECTED]));

        $this->assertTrue($this->pruner()->remove($draft));

        $this->assertSame([$draft], $this->trashed);
        $this->entityManager->clear();
        $this->assertNull($this->repository->findByUuid($draft));
        foreach ([$published, $liveRow, $shared, $active] as $uuid) {
            $this->assertNotNull($this->repository->findByUuid($uuid));
        }

        // the entry says what it said before, and it is free again: a later run may create the event if it passes
        $import = $this->importRepository->findBySourceAndExternalId('ical.a', 'draft');
        $this->assertSame(EventImport::STATE_FILTERED, $import->getState());
        $this->assertSame('The country FR is excluded.', $import->getStateReason());
        $this->assertNull($import->getEvent());
        $this->assertNull($import->getLastEventUuid());
    }

    public function testPruneCommandNeedsAConfirmationAndHonoursDryRunAndState(): void
    {
        $draft = $this->importedEvent('draft', 'Draft only', state: EventImport::STATE_FILTERED, reason: 'Out.');
        $this->importedEvent('live', 'Was published', state: EventImport::STATE_FILTERED, reason: 'Out.', place: WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);
        $rejected = $this->importedEvent('no', 'Turned down', state: EventImport::STATE_REJECTED, reason: 'No.');

        $tester = new CommandTester(new PruneImportCommand($this->pruner()));

        // a dry run lists and changes nothing
        $this->assertSame(Command::SUCCESS, $tester->execute(['--dry-run' => true]));
        $display = $tester->getDisplay();
        $this->assertStringContainsString('would go to the trash', $display);
        $this->assertStringContainsString('stays: it was published', $display);
        $this->assertSame([], $this->trashed);

        // without a terminal and without --force nothing is removed
        $this->assertSame(Command::FAILURE, $tester->execute([], ['interactive' => false]));
        $this->assertSame([], $this->trashed);

        // an answer "no" removes nothing
        $tester->setInputs(['no']);
        $this->assertSame(Command::SUCCESS, $tester->execute([]));
        $this->assertSame([], $this->trashed);

        // only one of the states
        $this->assertSame(Command::SUCCESS, $tester->execute(['--state' => 'rejected', '--force' => true], ['interactive' => false]));
        $this->assertSame([$rejected], $this->trashed);

        $this->assertSame(Command::INVALID, $tester->execute(['--state' => 'active']));

        // with --force
        $this->assertSame(Command::SUCCESS, $tester->execute(['--force' => true], ['interactive' => false]));
        $this->assertSame([$rejected, $draft], $this->trashed);
        $this->assertStringContainsString('1 event(s) moved to the trash', $tester->getDisplay());
    }

    private function tool(): EventImportRejectTool
    {
        return new EventImportRejectTool($this->importRepository, $this->entityManager);
    }

    private function pruner(): ImportPruner
    {
        $this->entityManager->clear();

        $trash = $this->createStub(TrashManagerInterface::class);
        $trash->method('store')->willReturnCallback(function (string $resourceKey, object $event): \Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface {
            $this->trashed[] = $event->getUuid();

            return $this->createStub(\Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface::class);
        });

        return new ImportPruner($this->importRepository, $this->repository, $this->entityManager, $trash, $this->createStub(DomainEventCollectorInterface::class));
    }

    private function importedEvent(
        string $id,
        string $title,
        string $state = EventImport::STATE_ACTIVE,
        ?string $reason = null,
        string $place = WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED,
        bool $withLiveRow = false,
        ?string $alsoActiveFrom = null,
    ): string {
        $event = new Event();
        $this->entityManager->persist($event);
        $row = $this->contentManager->persist($event, ['title' => $title, 'startDate' => '2027-06-01 10:00:00'], ['locale' => 'en', 'stage' => DimensionContentInterface::STAGE_DRAFT]);
        $row->setWorkflowPlace($place);

        if ($withLiveRow) {
            $live = new EventDimensionContent($event);
            $live->setStage(DimensionContentInterface::STAGE_LIVE);
            $live->setLocale('en');
            $live->setTitle($title);
            $event->addDimensionContent($live);
        }

        $import = new EventImport('ical.a', $id);
        $import->setEvent($event)->setState($state, $reason)->setPriority(50);
        $this->entityManager->persist($import);

        if (null !== $alsoActiveFrom) {
            $other = new EventImport($alsoActiveFrom, $id);
            $other->setEvent($event)->setPriority(100);
            $this->entityManager->persist($other);
        }

        $this->entityManager->flush();

        return $event->getUuid();
    }
}
