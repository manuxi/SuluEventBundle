<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Import;

use Manuxi\SuluEventBundle\Import\ImportReport;
use PHPUnit\Framework\TestCase;

class ImportReportTest extends TestCase
{
    public function testSievedCandidatesAreCountedByReasonAndNotKept(): void
    {
        $report = new ImportReport();
        $report->addFiltered('ical.a', 'e1', 'One', 'US', 'country_not_included', 'The country US is not one of the allowed countries.');
        $report->addFiltered('ical.a', 'e2', 'Two', 'US', 'country_not_included', 'The country US is not one of the allowed countries.');
        $report->addFiltered('ical.a', 'e3', 'Three', 'JP', 'country_not_included', 'The country JP is not one of the allowed countries.');
        $report->addFiltered('ical.a', 'e4', 'Four', null, 'online', 'The event takes place online only, without a place.');
        $report->addFiltered('ical.a', 'e5', 'Five', 'DE', 'too_far_ahead', 'The event starts on 2029-01-01, more than 18 months ahead.');
        $report->addFiltered('ical.a', 'e6', 'Six', 'DE', 'too_far_ahead', 'The event starts on 2029-02-01, more than 18 months ahead.');
        $report->addFiltered('ical.a', 'e7', 'Seven', 'DE', 'too_far_ahead', 'The event starts on 2029-03-01, more than 18 months ahead.');

        $this->assertSame(7, $report->getCounts()['filtered']);
        $this->assertSame([
            ['code' => 'too_far_ahead', 'country' => null, 'count' => 3],
            ['code' => 'country_not_included', 'country' => 'US', 'count' => 2],
            ['code' => 'country_not_included', 'country' => 'JP', 'count' => 1],
            ['code' => 'online', 'country' => null, 'count' => 1],
        ], $report->getFilteredReasons());

        $stored = $report->toArray();
        $this->assertSame([], $stored['entries'], 'the sieved candidates are not stored with the run');
        $this->assertSame($report->getFilteredReasons(), $stored['filteredReasons']);
    }

    public function testNothingSievedGivesAnEmptyList(): void
    {
        $this->assertSame([], (new ImportReport())->toArray()['filteredReasons']);
    }
}
