<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Command;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluEventBundle\Command\ImportEventsCommand;
use Manuxi\SuluEventBundle\Entity\EventImportRun;
use Manuxi\SuluEventBundle\Import\EventImporter;
use Manuxi\SuluEventBundle\Import\ImportReport;
use Manuxi\SuluEventBundle\Import\ImportSettings;
use Manuxi\SuluEventBundle\Import\SourceRegistry;
use Manuxi\SuluEventBundle\Repository\EventImportRunRepository;
use Manuxi\SuluEventBundle\Tests\Integration\Import\ArraySource;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ImportEventsCommandTest extends TestCase
{
    private EventImporter&MockObject $importer;
    private EventImportRunRepository&MockObject $runRepository;

    protected function setUp(): void
    {
        $this->importer      = $this->createMock(EventImporter::class);
        $this->runRepository = $this->createMock(EventImportRunRepository::class);
    }

    public function testDisabledImportOnlyTellsSo(): void
    {
        $this->importer->expects($this->never())->method('import');

        $tester = $this->createTester(new ImportSettings(false));
        $exit   = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $exit);
        $this->assertStringContainsString('switched off', $tester->getDisplay());
    }

    public function testNoSourceConfiguredOnlyTellsSo(): void
    {
        $this->importer->expects($this->never())->method('import');

        $tester = $this->createTester(new ImportSettings(true, 'import'), []);
        $exit   = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $exit);
        $this->assertStringContainsString('No import source is configured', $tester->getDisplay());
    }

    public function testRunPrintsTheTableAndStoresTheResult(): void
    {
        $report = new ImportReport();
        $report->add(ImportReport::ACTION_CREATED, 'ical.a', 'e1', 'Example Conf 2027', 'uuid-1', 'Created as a draft.');
        $report->add(ImportReport::ACTION_UPDATED, 'ical.a', 'e2', 'Other Conf', 'uuid-2', 'Changed in the draft: startDate', ['title:en']);
        $report->add(ImportReport::ACTION_UNCHANGED, 'ical.a', 'e3', 'Quiet Conf', 'uuid-3');
        $report->addNewLocation('Messe Hall 3', 'Europe/Berlin', false);

        $this->importer->expects($this->once())->method('import')->with(null, false, null)->willReturn($report);

        $stored = null;
        $this->runRepository->expects($this->once())->method('add')->willReturnCallback(static function (EventImportRun $run) use (&$stored): void {
            $stored = $run;
        });

        $tester = $this->createTester(new ImportSettings(true, 'import'));
        $exit   = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $exit);
        $display = $tester->getDisplay();
        $this->assertStringContainsString('Example Conf 2027', $display);
        $this->assertStringContainsString('Locked (changed in the admin, not overwritten): title:en', $display);
        $this->assertStringContainsString('Messe Hall 3', $display);
        $this->assertStringContainsString('Europe/Berlin (guessed)', $display);
        $this->assertStringNotContainsString('Quiet Conf', $display, 'unchanged events are not listed');

        $this->assertInstanceOf(EventImportRun::class, $stored);
        $this->assertFalse($stored->isDryRun());
        $this->assertNotNull($stored->getFinishedAt());
        $this->assertSame(1, $stored->getCounts()['created']);
        $this->assertSame(1, $stored->getCounts()['newLocations']);
        $this->assertSame('Messe Hall 3', $stored->getReport()['newLocations'][0]['name']);
    }

    public function testDryRunAndOptionsArePassedOn(): void
    {
        $this->importer->expects($this->once())->method('import')->with('ical.a', true, 5)->willReturn(new ImportReport());
        $this->runRepository->expects($this->once())->method('add')->with($this->callback(static fn (EventImportRun $run): bool => $run->isDryRun() && 'ical.a' === $run->getSource()));

        $tester = $this->createTester(new ImportSettings(true, 'import'));
        $exit   = $tester->execute(['--source' => 'ical.a', '--dry-run' => true, '--limit' => '5']);

        $this->assertSame(Command::SUCCESS, $exit);
        $this->assertStringContainsString('dry run', $tester->getDisplay());
    }

    public function testErrorsGiveExitCode1(): void
    {
        $report = new ImportReport();
        $report->add(ImportReport::ACTION_ERROR, 'ical.a', null, null, null, 'robots.txt does not allow it');
        $this->importer->method('import')->willReturn($report);

        $tester = $this->createTester(new ImportSettings(true, 'import'));
        $exit   = $tester->execute([]);

        $this->assertSame(Command::FAILURE, $exit);
        $this->assertStringContainsString('robots.txt does not allow it', $tester->getDisplay());
    }

    public function testUnknownSourceIsRefusedWithTheKnownOnes(): void
    {
        $this->importer->expects($this->never())->method('import');

        $tester = $this->createTester(new ImportSettings(true, 'import'));
        $exit   = $tester->execute(['--source' => 'ical.zzz']);

        $this->assertSame(Command::INVALID, $exit);
        $this->assertStringContainsString('Known sources: ical.a', $tester->getDisplay());
    }

    public function testInvalidLimitIsRefused(): void
    {
        $this->importer->expects($this->never())->method('import');

        $tester = $this->createTester(new ImportSettings(true, 'import'));
        $exit   = $tester->execute(['--limit' => 'many']);

        $this->assertSame(Command::INVALID, $exit);
        $this->assertStringContainsString('--limit', $tester->getDisplay());
    }

    public function testExceptionOfTheImporterIsShownAndFails(): void
    {
        $this->importer->method('import')->willThrowException(new \RuntimeException('The import user "x" does not exist.'));

        $tester = $this->createTester(new ImportSettings(true, 'x'));
        $exit   = $tester->execute([]);

        $this->assertSame(Command::FAILURE, $exit);
        $this->assertStringContainsString('does not exist', $tester->getDisplay());
    }

    /**
     * @param list<ArraySource>|null $sources
     */
    private function createTester(ImportSettings $settings, ?array $sources = null): CommandTester
    {
        $command = new ImportEventsCommand(
            $this->importer,
            new SourceRegistry($sources ?? [new ArraySource('ical.a', 50)]),
            $this->runRepository,
            $this->createStub(EntityManagerInterface::class),
            $settings,
        );

        return new CommandTester($command);
    }
}
