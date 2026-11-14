<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration\Import;

use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluEventBundle\Content\DataMapper\EventImportLockDataMapper;
use Manuxi\SuluEventBundle\Content\Normalizer\EventNormalizer;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\EventImport;
use Manuxi\SuluEventBundle\Import\EventImportInfoProvider;
use Manuxi\SuluEventBundle\Repository\EventImportRepository;
use Manuxi\SuluEventBundle\Service\AttendanceModeSelect;
use Manuxi\SuluEventBundle\Service\EventStatusSelect;
use Manuxi\SuluEventBundle\Service\EventTypeSelect;
use Manuxi\SuluEventBundle\Tests\Integration\AbstractEventDatabaseTestCase;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The "Source" tab of the event form: the data the normalizer shows and the command to release the locks.
 */
class EventImportAdminTest extends AbstractEventDatabaseTestCase
{
    private EventImportRepository $importRepository;
    private FakeContentManager $contentManager;
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $registry = $this->createStub(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($this->entityManager);
        $this->importRepository = new EventImportRepository($registry);
        $this->contentManager   = new FakeContentManager($this->entityManager);

        $this->event = new Event();
        $this->entityManager->persist($this->event);
        $this->contentManager->persist($this->event, ['title' => 'Imported', 'startDate' => '2027-06-01 10:00:00'], ['locale' => 'de', 'stage' => DimensionContentInterface::STAGE_DRAFT]);

        $main = new EventImport('jsonld.official', 'a');
        $main->setEvent($this->event)->setPriority(100)->setSourceUrl('https://conf.example.com/')->setLockedFields(['title:de', 'startDate'])
            ->setImportedValues(['_sourceDescription' => 'About the conf', 'title:de' => 'Old title', 'startDate' => '2027-06-01 09:00:00', 'endDate' => 'x'])
            ->setLastSyncedAt(new \DateTimeImmutable('2027-03-01 05:00:00'))->setLastError('Something odd');
        $other = new EventImport('ical.cal', 'b');
        $other->setEvent($this->event)->setPriority(50)->setLockedFields(['status'])->setLastSyncedAt(new \DateTimeImmutable('2027-03-02 05:00:00'));
        $this->entityManager->persist($main);
        $this->entityManager->persist($other);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    public function testInfoOfAnImportedEvent(): void
    {
        $info = (new EventImportInfoProvider($this->importRepository))->getInfo($this->event->getUuid());

        $this->assertSame('jsonld.official, ical.cal', $info['source']);
        $this->assertSame('active', $info['state']);
        $this->assertSame('https://conf.example.com/', $info['sourceUrl']);
        $this->assertSame('2027-03-02 05:00', $info['lastSyncedAt'], 'the newest comparison of all sources');
        $this->assertSame(['title:de', 'startDate', 'status'], $info['lockedFields']);
        $this->assertSame('Something odd', $info['lastError']);
        $this->assertSame('About the conf', $info['sourceDescription']);
    }

    public function testInfoAndNormalizerShowTheReasonOfASievedOutEvent(): void
    {
        $filtered = new Event();
        $this->entityManager->persist($filtered);
        $this->contentManager->persist($filtered, ['title' => 'Faraway'], ['locale' => 'de', 'stage' => DimensionContentInterface::STAGE_DRAFT]);
        $import = new EventImport('confs-tech', 'far');
        $import->setEvent($filtered)->setState(EventImport::STATE_FILTERED, 'The country NZ is not one of the allowed countries.');
        $this->entityManager->persist($import);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $provider = new EventImportInfoProvider($this->importRepository);
        $info     = $provider->getInfo($filtered->getUuid());
        $this->assertSame('filtered', $info['state']);
        $this->assertSame('The country NZ is not one of the allowed countries.', $info['stateReason']);

        $this->assertNull($provider->getInfo($this->event->getUuid())['stateReason'] ?? null);

        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(static fn (string $id): string => $id);
        $normalized = $this->normalize($translator, $provider, $filtered);
        $this->assertSame('sulu_event.import.state.filtered', $normalized['importState']);
        $this->assertSame('The country NZ is not one of the allowed countries.', $normalized['importStateReason']);
    }

    public function testInfoIsNullForAnEventThatIsNotImported(): void
    {
        $provider = new EventImportInfoProvider($this->importRepository);

        $this->assertNull($provider->getInfo('00000000-0000-7000-8000-000000000000'));
    }

    public function testInfoIsNullIfTheTablesDoNotExistYet(): void
    {
        $repository = $this->createStub(EventImportRepository::class);
        $repository->method('findByEventUuid')->willThrowException(new \RuntimeException('Table app_event_import does not exist'));

        $this->assertNull((new EventImportInfoProvider($repository))->getInfo('x'));
    }

    public function testNormalizerShowsTheSourceOfAnImportedEvent(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(static fn (string $id): string => match ($id) {
            'sulu_event.import.state.active'    => 'Active',
            'sulu_event.import.field.startDate' => 'Start',
            'sulu_event.import.field.status'    => 'Status',
            'sulu_event.import.field.title'     => 'Title',
            default                             => $id,
        });

        $normalized = $this->normalize($translator, new EventImportInfoProvider($this->importRepository), $this->event);

        $this->assertSame('jsonld.official, ical.cal', $normalized['importSource']);
        $this->assertSame('Active', $normalized['importState']);
        $this->assertSame('Title (de), Start, Status', $normalized['importLockedFields']);
        $this->assertTrue($normalized['importHasLocks']);
        $this->assertSame('https://conf.example.com/', $normalized['importSourceUrl']);
        $this->assertSame('About the conf', $normalized['importSourceDescription']);
    }

    public function testNormalizerLeavesTheSourceEmptyForOtherEvents(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturn('x');
        $other = new Event();
        $this->entityManager->persist($other);
        $this->contentManager->persist($other, ['title' => 'Manual'], ['locale' => 'de', 'stage' => DimensionContentInterface::STAGE_DRAFT]);
        $this->entityManager->flush();

        $normalized = $this->normalize($translator, new EventImportInfoProvider($this->importRepository), $other);

        $this->assertArrayHasKey('importSource', $normalized);
        $this->assertNull($normalized['importSource']);
        $this->assertArrayNotHasKey('importLockedFields', $normalized);
    }

    public function testUnlockAllForgetsTheLocksAndTheRememberedValues(): void
    {
        $mapper = new EventImportLockDataMapper($this->importRepository);
        $row    = $this->row();

        $mapper->map($row, $row, ['importUnlockAll' => true]);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $main = $this->importRepository->findBySourceAndExternalId('jsonld.official', 'a');
        $this->assertSame([], $main->getLockedFields());
        $this->assertSame(['_sourceDescription' => 'About the conf', 'endDate' => 'x'], $main->getImportedValues(), 'only the values of the released fields are forgotten');
        $this->assertNull($main->getContentHash());
        $this->assertSame([], $this->importRepository->findBySourceAndExternalId('ical.cal', 'b')->getLockedFields());
    }

    public function testNothingHappensWithoutTheFlag(): void
    {
        $mapper = new EventImportLockDataMapper($this->importRepository);
        $row    = $this->row();

        foreach ([[], ['importUnlockAll' => false], ['importUnlockAll' => 'false'], ['importUnlockAll' => null]] as $data) {
            $mapper->map($row, $row, $data);
        }
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->assertSame(['title:de', 'startDate'], $this->importRepository->findBySourceAndExternalId('jsonld.official', 'a')->getLockedFields());
    }

    private function row(): EventDimensionContent
    {
        $event = $this->repository->findByUuid($this->event->getUuid());
        foreach ($event->getDimensionContents() as $row) {
            return $row;
        }

        $this->fail('no row');
    }

    /**
     * @return array<string, mixed>
     */
    private function normalize(TranslatorInterface $translator, EventImportInfoProvider $provider, Event $event): array
    {
        $normalizer = new EventNormalizer(
            $translator,
            $this->createStub(EventTypeSelect::class),
            $this->createStub(MediaManagerInterface::class),
            $this->createStub(EventStatusSelect::class),
            $this->createStub(AttendanceModeSelect::class),
            $provider,
        );

        $this->entityManager->clear();
        $row = null;
        foreach ($this->repository->findByUuid($event->getUuid())->getDimensionContents() as $row) {
            break;
        }

        return $normalizer->enhance($row, []);
    }
}
