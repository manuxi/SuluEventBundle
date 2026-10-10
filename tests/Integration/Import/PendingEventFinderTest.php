<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Integration\Import;

use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventImport;
use Manuxi\SuluEventBundle\Import\ImportSettings;
use Manuxi\SuluEventBundle\Import\PendingEventFinder;
use Manuxi\SuluEventBundle\Mcp\Tool\EventImportPendingTool;
use Manuxi\SuluEventBundle\Repository\EventImportRepository;
use Manuxi\SuluEventBundle\Tests\Integration\AbstractEventDatabaseTestCase;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;

class PendingEventFinderTest extends AbstractEventDatabaseTestCase
{
    private FakeContentManager $contentManager;
    private EventImportRepository $importRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contentManager = new FakeContentManager($this->entityManager);
        $registry             = $this->createStub(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($this->entityManager);
        $this->importRepository = new EventImportRepository($registry);
    }

    public function testListsWhatIsMissingWithTheHintsOfTheSource(): void
    {
        $this->importedEvent('a', 'Needs text', '2027-06-01 10:00:00', ['en' => ['text' => '', 'summary' => 'Short']], 'https://a.example.com/', 'About a');
        $this->importedEvent('b', 'Complete', '2027-06-02 10:00:00', ['en' => ['text' => '<p>Text</p>', 'summary' => 'Short', 'excerptCategories' => [$this->categories['web']->getId()]]], 'https://b.example.com/');

        $found   = $this->finder(['en'])->find();
        $byTitle = array_column($found['items'], 'missing', 'title');

        $this->assertSame(2, $found['total']);
        $this->assertSame(['text:en', 'categories', 'image'], $byTitle['Needs text']);
        $this->assertSame(['image'], $byTitle['Complete']);
    }

    public function testMissingTextSummaryLocaleCategoryAndImage(): void
    {
        $this->importedEvent('a', 'Needs text', '2027-06-01 10:00:00', ['en' => ['text' => '<p> </p>', 'summary' => 'Short']], 'https://a.example.com/', 'About a');

        $item = $this->finder(['en', 'de'])->find()['items'][0];

        $this->assertSame(['text:en', 'locale:de', 'categories', 'image'], $item['missing']);
        $this->assertSame('Needs text', $item['title']);
        $this->assertSame('https://a.example.com/', $item['sourceUrl']);
        $this->assertSame('About a', $item['sourceDescription']);
        $this->assertSame('ical.a', $item['source']);
        $this->assertFalse($item['published']);
    }

    public function testWithoutConfiguredLocalesTheLocalesOfTheEventAreChecked(): void
    {
        $this->importedEvent('a', 'Event', '2027-06-01 10:00:00', ['en' => ['summary' => 'S'], 'de' => ['text' => 'T']]);

        $item = $this->finder(null)->find()['items'][0];

        $this->assertContains('text:en', $item['missing']);
        $this->assertContains('summary:de', $item['missing']);
        $this->assertNotContains('locale:de', $item['missing']);
    }

    public function testExcerptImageAndCategoryCountAsDone(): void
    {
        $this->importedEvent('a', 'Event', '2027-06-01 10:00:00', ['en' => [
            'text'              => 'T',
            'summary'           => 'S',
            'excerptCategories' => [$this->categories['web']->getId()],
            'excerpt'           => ['image' => ['id' => 5]],
        ]]);

        $this->assertSame(0, $this->finder(['en'])->find()['total']);
    }

    public function testOrderedByStartAndPaginated(): void
    {
        $this->importedEvent('late', 'Late', '2027-09-01 10:00:00', ['en' => []]);
        $this->importedEvent('early', 'Early', '2027-03-01 10:00:00', ['en' => []]);
        $this->importedEvent('mid', 'Mid', '2027-06-01 10:00:00', ['en' => []]);

        $first = $this->finder(['en'])->find(null, 2, 0);
        $rest  = $this->finder(['en'])->find(null, 2, 2);

        $this->assertSame(3, $first['total']);
        $this->assertSame(['Early', 'Mid'], array_column($first['items'], 'title'));
        $this->assertSame(['Late'], array_column($rest['items'], 'title'));
    }

    public function testPublishedFlagAndRejectedEntriesAreSkipped(): void
    {
        $this->importedEvent('live', 'Live', '2027-06-01 10:00:00', ['en' => []], place: WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);
        $this->importedEvent('gone', 'Gone', '2027-06-02 10:00:00', ['en' => []], state: EventImport::STATE_REJECTED);

        $found = $this->finder(['en'])->find();

        $this->assertSame(['Live'], array_column($found['items'], 'title'));
        $this->assertTrue($found['items'][0]['published']);
    }

    public function testToolReturnsPagesAndClampsTheLimit(): void
    {
        $this->importedEvent('a', 'A', '2027-06-01 10:00:00', ['en' => []]);

        $tool   = new EventImportPendingTool($this->finder(['en']));
        $result = $tool->pending(null, 0, 5000);

        $this->assertSame(1, $result['page']);
        $this->assertSame(100, $result['limit']);
        $this->assertSame(1, $result['total']);
        $this->assertSame('A', $result['events'][0]['title']);
    }

    /**
     * @param string[]|null $locales
     */
    private function finder(?array $locales): PendingEventFinder
    {
        $this->entityManager->clear();

        return new PendingEventFinder($this->importRepository, $this->repository, new ImportSettings(true, 'import', false, '/events', null, 'default', 'event', [], $locales ?? []));
    }

    /**
     * @param array<string, array<string, mixed>> $locales locale => data of the draft
     */
    private function importedEvent(
        string $id,
        string $title,
        string $start,
        array $locales,
        ?string $sourceUrl = null,
        ?string $description = null,
        string $state = EventImport::STATE_ACTIVE,
        string $place = WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED,
    ): void {
        $event = new Event();
        $this->entityManager->persist($event);
        foreach ($locales as $locale => $data) {
            $row = $this->contentManager->persist($event, ['title' => $title, 'startDate' => $start] + $data, ['locale' => $locale, 'stage' => DimensionContentInterface::STAGE_DRAFT]);
            $row->setWorkflowPlace($place);
        }

        $import = new EventImport('ical.a', $id);
        $import->setEvent($event)->setState($state)->setSourceUrl($sourceUrl)->setPriority(50)->setImportedValues(null !== $description ? ['_sourceDescription' => $description] : []);
        $this->entityManager->persist($import);
        $this->entityManager->flush();
    }
}
