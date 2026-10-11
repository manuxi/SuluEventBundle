<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Import;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Entity\EventImportRun;
use Manuxi\SuluEventBundle\Import\EventImporter;
use Manuxi\SuluEventBundle\Import\ImageFiller;
use Manuxi\SuluEventBundle\Import\ImportReport;
use Manuxi\SuluEventBundle\Import\ImportRunner;
use Manuxi\SuluEventBundle\Import\ImportSettings;
use Manuxi\SuluEventBundle\Import\SourceRegistry;
use Manuxi\SuluEventBundle\Repository\EventImportRunRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * The button "Import now": one run at a time, the run is stored, a failed run is stored as one with an error.
 */
class ImportRunnerTest extends TestCase
{
    private EventImporter&MockObject $importer;
    private ImageFiller&MockObject $imageFiller;
    private SourceRegistry&MockObject $sources;
    private EventImportRunRepository&MockObject $runs;
    private EntityManagerInterface&MockObject $entityManager;
    private ArrayAdapter $cache;

    protected function setUp(): void
    {
        $this->importer      = $this->createMock(EventImporter::class);
        $this->imageFiller   = $this->createMock(ImageFiller::class);
        $this->sources       = $this->createMock(SourceRegistry::class);
        $this->runs          = $this->createMock(EventImportRunRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->cache         = new ArrayAdapter();
        $this->sources->method('all')->willReturn(['ical.a' => new \stdClass()]);
    }

    public function testOnlyOneRunAtATime(): void
    {
        $runner = $this->runner();

        $this->assertNull($runner->runningSince());
        $this->assertTrue($runner->reserve());
        $this->assertNotNull($runner->runningSince());
        $this->assertFalse($runner->reserve(), 'a run is going');
    }

    public function testSwitchedOffImportCannotBeStarted(): void
    {
        $runner = $this->runner(enabled: false);

        $this->assertFalse($runner->isAvailable());
        $this->assertFalse($runner->reserve());
    }

    public function testRunImportsStoresTheRunFillsTheImagesAndFreesTheFlag(): void
    {
        $report = new ImportReport();
        $this->importer->expects($this->once())->method('import')->with(null, false, null)->willReturn($report);
        $this->imageFiller->expects($this->once())->method('fill');
        $stored = null;
        $this->runs->expects($this->once())->method('add')->willReturnCallback(static function (EventImportRun $run) use (&$stored): void {
            $stored = $run;
        });
        $this->runs->expects($this->once())->method('prune');
        $this->entityManager->expects($this->once())->method('flush');
        $runner = $this->runner();
        $runner->reserve();

        $run = $runner->run();

        $this->assertSame($stored, $run);
        $this->assertNotNull($run->getFinishedAt());
        $this->assertSame(0, $run->getCounts()['errors']);
        $this->assertNull($runner->runningSince());
    }

    public function testFailedRunIsStoredWithItsErrorAndFreesTheFlag(): void
    {
        $this->importer->method('import')->willThrowException(new \RuntimeException('Source is down'));
        $this->runs->expects($this->once())->method('add');
        $runner = $this->runner();
        $runner->reserve();

        $run = $runner->run();

        $this->assertSame(1, $run->getCounts()['errors']);
        $this->assertSame('Source is down', $run->getReport()['entries'][0]['detail']);
        $this->assertNull($runner->runningSince());
    }

    private function runner(bool $enabled = true): ImportRunner
    {
        return new ImportRunner(
            $this->importer,
            $this->imageFiller,
            $this->sources,
            new ImportSettings($enabled, 'import', false, '/events', null, 'default', 'event', [], [], null),
            $this->runs,
            $this->entityManager,
            $this->cache,
        );
    }
}
