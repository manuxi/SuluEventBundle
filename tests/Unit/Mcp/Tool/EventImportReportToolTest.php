<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Mcp\Tool;

use Manuxi\SuluEventBundle\Entity\EventImportRun;
use Manuxi\SuluEventBundle\Import\ImportReport;
use Manuxi\SuluEventBundle\Mcp\Tool\EventImportReportTool;
use Manuxi\SuluEventBundle\Repository\EventImportRunRepository;
use PHPUnit\Framework\TestCase;

class EventImportReportToolTest extends TestCase
{
    public function testNoRunYet(): void
    {
        $repository = $this->createMock(EventImportRunRepository::class);
        $repository->method('findLast')->willReturn(null);

        $result = (new EventImportReportTool($repository))->report();

        $this->assertNull($result['run']);
    }

    public function testLastRunWithErrorsVanishedLockedAndNewLocations(): void
    {
        $report = new ImportReport();
        $report->add(ImportReport::ACTION_ERROR, 'ical.a', null, null, null, 'robots.txt does not allow it');
        $report->add(ImportReport::ACTION_VANISHED, 'ical.a', 'e1', 'Gone Conf', 'uuid-1', 'The source does not deliver the event any more.');
        $report->add(ImportReport::ACTION_UPDATED, 'ical.a', 'e2', 'Locked Conf', 'uuid-2', '', ['title:de']);
        $report->add(ImportReport::ACTION_UNCHANGED, 'ical.a', 'e3', 'Quiet Conf', 'uuid-3');
        $report->addNewLocation('Messe Hall 3', 'Europe/Berlin', false);
        $report->addWarning('ical.a', 'e2', 'Unknown category keys: nope');

        $run = new EventImportRun(new \DateTimeImmutable('2027-03-01 05:00:00'));
        $run->finish(new \DateTimeImmutable('2027-03-01 05:00:09'), $report->getCounts(), $report->toArray());

        $repository = $this->createMock(EventImportRunRepository::class);
        $repository->expects($this->once())->method('findLast')->with(true)->willReturn($run);

        $result = (new EventImportReportTool($repository))->report(true);

        $this->assertSame(1, $result['run']['counts']['errors']);
        $this->assertFalse($result['run']['dryRun']);
        $this->assertSame('robots.txt does not allow it', $result['errors'][0]['detail']);
        $this->assertSame('Gone Conf', $result['vanished'][0]['title']);
        $this->assertSame(['title:de'], $result['locked'][0]['lockedFields']);
        $this->assertSame('Messe Hall 3', $result['newLocations'][0]['name']);
        $this->assertFalse($result['newLocations'][0]['certain']);
        $this->assertSame('Unknown category keys: nope', $result['warnings'][0]['message']);
    }

    public function testAutoPublishResultIsPartOfTheReport(): void
    {
        $counts = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'locked' => 0, 'vanished' => 0, 'rejected' => 0, 'filtered' => 0, 'errors' => 0, 'newLocations' => 0];
        $auto   = new EventImportRun(new \DateTimeImmutable('2027-03-01 09:00:00'), EventImportRun::SOURCE_AUTO_PUBLISH);
        $auto->finish(new \DateTimeImmutable('2027-03-01 09:00:01'), $counts, ['autoPublish' => ['published' => 2, 'waiting' => 1, 'skipped' => 5, 'errors' => 0, 'waitingItems' => [['uuid' => 'u1', 'title' => 'Gap', 'missing' => ['subtitle:en']]]]]);

        $repository = $this->createMock(EventImportRunRepository::class);
        $repository->method('findLast')->willReturn(null);
        $repository->method('findLastAutoPublish')->willReturn($auto);

        $result = (new EventImportReportTool($repository))->report();

        $this->assertNull($result['run']);
        $this->assertSame(2, $result['autoPublish']['published']);
        $this->assertFalse($result['autoPublish']['dryRun']);
        $this->assertSame(['subtitle:en'], $result['autoPublish']['waitingItems'][0]['missing']);
    }
}
