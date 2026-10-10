<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Import;

use Manuxi\SuluEventBundle\Entity\EventImportRun;
use Manuxi\SuluEventBundle\Import\ImportReport;
use Manuxi\SuluEventBundle\Import\ImportRunSummary;
use PHPUnit\Framework\TestCase;

class ImportRunSummaryTest extends TestCase
{
    public function testRunAsText(): void
    {
        $report = new ImportReport();
        $report->add(ImportReport::ACTION_CREATED, 'ical.a', 'e1', 'New Conf', 'u1');
        $report->add(ImportReport::ACTION_ERROR, 'jsonld.b', 'e2', 'Broken Conf', null, 'No start date');
        $report->addNewLocation('Hall 3', 'Europe/Berlin', true);
        $report->addNewLocation('Pub', null, false);

        $run = new EventImportRun(new \DateTimeImmutable('2027-03-01 05:00:00'), 'ical.a', true);
        $run->finish(new \DateTimeImmutable('2027-03-01 05:00:03'), $report->getCounts(), $report->toArray());

        $text = ImportRunSummary::fromRun($run);

        $this->assertSame('2027-03-01 05:00 (dry run) - ical.a', $text['importLastRunWhen']);
        $this->assertSame('new 1, changed 0, unchanged 0, locked 0, vanished 0, rejected 0, errors 1, new locations 2', $text['importLastRunSummary']);
        $this->assertSame('jsonld.b Broken Conf: No start date', $text['importLastRunErrors']);
        $this->assertSame("Hall 3 (Europe/Berlin)\nPub (?, guessed)", $text['importLastRunNewLocations']);
    }

    public function testQuietRunHasNoErrorsAndNoLocations(): void
    {
        $report = new ImportReport();
        $run    = new EventImportRun(new \DateTimeImmutable('2027-03-01 05:00:00'));
        $run->finish(new \DateTimeImmutable('2027-03-01 05:00:01'), $report->getCounts(), $report->toArray());

        $text = ImportRunSummary::fromRun($run);

        $this->assertSame('2027-03-01 05:00', $text['importLastRunWhen']);
        $this->assertNull($text['importLastRunErrors']);
        $this->assertNull($text['importLastRunNewLocations']);
    }
}
