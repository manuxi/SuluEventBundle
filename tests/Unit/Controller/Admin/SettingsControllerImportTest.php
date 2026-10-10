<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Manuxi\SuluEventBundle\Controller\Admin\SettingsController;
use Manuxi\SuluEventBundle\Entity\EventImportRun;
use Manuxi\SuluEventBundle\Entity\EventSettings;
use Manuxi\SuluEventBundle\Import\ImportReport;
use Manuxi\SuluEventBundle\Repository\EventImportRunRepository;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;

/**
 * The read only tab "Import" of the settings shows the last run.
 */
class SettingsControllerImportTest extends TestCase
{
    public function testLastRunIsPartOfTheSettingsData(): void
    {
        $report = new ImportReport();
        $report->add(ImportReport::ACTION_CREATED, 'ical.a', 'e1', 'New Conf', 'u1');
        $run = new EventImportRun(new \DateTimeImmutable('2027-03-01 05:00:00'));
        $run->finish(new \DateTimeImmutable('2027-03-01 05:00:03'), $report->getCounts(), $report->toArray());

        $repository = $this->createMock(EventImportRunRepository::class);
        $repository->expects($this->once())->method('findLast')->with(true)->willReturn($run);

        $data = $this->settingsData($repository);

        $this->assertSame('2027-03-01 05:00', $data['importLastRunWhen']);
        $this->assertStringStartsWith('new 1,', $data['importLastRunSummary']);
        $this->assertArrayHasKey('toggleHeader', $data, 'the other settings are still there');
    }

    public function testNoRunYet(): void
    {
        $repository = $this->createStub(EventImportRunRepository::class);
        $repository->method('findLast')->willReturn(null);

        $this->assertNull($this->settingsData($repository)['importLastRunWhen']);
    }

    public function testMissingTablesDoNotBreakTheSettings(): void
    {
        $repository = $this->createStub(EventImportRunRepository::class);
        $repository->method('findLast')->willThrowException(new \RuntimeException('Table app_event_import_run does not exist'));

        $data = $this->settingsData($repository);

        $this->assertNull($data['importLastRunWhen']);
        $this->assertArrayHasKey('toggleHeader', $data);
    }

    public function testWithoutTheRepositoryEverythingIsEmpty(): void
    {
        $this->assertNull($this->settingsData(null)['importLastRunSummary']);
    }

    /**
     * @return array<string, mixed>
     */
    private function settingsData(?EventImportRunRepository $repository): array
    {
        $controller = new SettingsController(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(DomainEventCollectorInterface::class),
            $this->createStub(ViewHandlerInterface::class),
            null,
            $repository,
        );

        $method = new \ReflectionMethod($controller, 'getDataForEntity');

        return $method->invoke($controller, new EventSettings());
    }
}
