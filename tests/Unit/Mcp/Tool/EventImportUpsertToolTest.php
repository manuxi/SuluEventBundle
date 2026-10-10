<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Mcp\Tool;

use Manuxi\SuluEventBundle\Import\EventCandidate;
use Manuxi\SuluEventBundle\Import\EventImporter;
use Manuxi\SuluEventBundle\Import\ImportReport;
use Manuxi\SuluEventBundle\Mcp\Tool\EventImportUpsertTool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EventImportUpsertToolTest extends TestCase
{
    private EventImporter&MockObject $importer;
    private EventImportUpsertTool $tool;

    protected function setUp(): void
    {
        $this->importer = $this->createMock(EventImporter::class);
        $this->tool     = new EventImportUpsertTool($this->importer);
    }

    public function testBuildsTheCandidateAndReportsTheResult(): void
    {
        $captured = null;
        $report   = new ImportReport();
        $report->add(ImportReport::ACTION_UPDATED, 'editorial', 'https://conf.example.com/', 'Example Conf', 'uuid-1', 'Changed in the draft: startDate.', ['title:en']);
        $report->addWarning('editorial', 'https://conf.example.com/', 'Unknown category keys: nope');
        $report->addNewLocation('Messe Hall 3', 'Europe/Berlin', true);

        $this->importer->expects($this->once())->method('importCandidate')
            ->with('editorial', 30, $this->callback(static function (EventCandidate $candidate) use (&$captured): bool {
                $captured = $candidate;

                return true;
            }), false)
            ->willReturn($report);

        $result = $this->tool->upsert(
            source: 'editorial',
            externalId: 'https://conf.example.com/',
            titles: ['en' => 'Example Conf', 'de' => '  ', 'fr' => 3],
            startDate: '2027-06-12T10:00:00',
            endDate: '2027-06-13T17:00:00',
            timezone: 'Europe/Berlin',
            location: ['name' => 'Messe Hall 3', 'city' => 'Cologne', 'countryCode' => 'DE', 'latitude' => '50.94', 'longitude' => 6.98],
            categories: ['web-php'],
            tags: ['Symfony'],
            texts: ['en' => '<p>Own text</p>'],
            sourceDescription: 'Seen on the page',
            series: 'example-conf',
        );

        $this->assertSame(['en' => 'Example Conf'], $captured->titles, 'empty and non-text titles are dropped');
        $this->assertSame('2027-06-12 10:00:00', $captured->startDate->format('Y-m-d H:i:s'));
        $this->assertSame('Europe/Berlin', $captured->startDate->getTimezone()->getName());
        $this->assertSame('Europe/Berlin', $captured->timezone);
        $this->assertSame('Messe Hall 3', $captured->location->name);
        $this->assertSame(50.94, $captured->location->latitude);
        $this->assertSame(['web-php'], $captured->categoryKeys);
        $this->assertSame(['en' => '<p>Own text</p>'], $captured->texts);
        $this->assertSame('example-conf', $captured->series);
        $this->assertSame('Seen on the page', $captured->sourceDescription);

        $this->assertTrue($result['success']);
        $this->assertSame('updated', $result['action']);
        $this->assertSame('uuid-1', $result['uuid']);
        $this->assertSame(['title:en'], $result['lockedFields']);
        $this->assertSame(['Unknown category keys: nope'], $result['warnings']);
        $this->assertSame('Messe Hall 3', $result['newLocations'][0]['name']);
        $this->assertFalse($result['dryRun']);
    }

    public function testDateWithOffsetIsConvertedIntoTheGivenZone(): void
    {
        $captured = null;
        $this->importer->method('importCandidate')->willReturnCallback(function (string $source, int $priority, EventCandidate $candidate) use (&$captured): ImportReport {
            $captured = $candidate;

            return $this->reportWith(ImportReport::ACTION_CREATED);
        });

        $this->tool->upsert('editorial', 'x', ['en' => 'T'], '2027-06-12T17:00:00Z', timezone: 'America/Los_Angeles', priority: 80, dryRun: true);

        $this->assertSame('2027-06-12 10:00:00', $captured->startDate->format('Y-m-d H:i:s'));
    }

    public function testPriorityAndDryRunArePassedOn(): void
    {
        $this->importer->expects($this->once())->method('importCandidate')
            ->with('editorial', 80, $this->anything(), true)
            ->willReturn($this->reportWith(ImportReport::ACTION_CREATED));

        $result = $this->tool->upsert('editorial', 'x', ['en' => 'T'], '2027-06-12T10:00:00', priority: 80, dryRun: true);

        $this->assertTrue($result['dryRun']);
    }

    public function testErrorEntryBecomesAnError(): void
    {
        $this->importer->method('importCandidate')->willReturn($this->reportWith(ImportReport::ACTION_ERROR, 'The source gives no title or no start date.'));

        $result = $this->tool->upsert('editorial', 'x', [], '2027-06-12T10:00:00');

        $this->assertFalse($result['success']);
        $this->assertSame('The source gives no title or no start date.', $result['error']);
    }

    public function testSourceNameIsChecked(): void
    {
        $this->importer->expects($this->never())->method('importCandidate');

        $this->assertStringContainsString('lower case', $this->tool->upsert('Bad Source!', 'x', ['en' => 'T'], '2027-06-12T10:00:00')['error']);
        $this->assertStringContainsString('externalId', $this->tool->upsert('editorial', ' ', ['en' => 'T'], '2027-06-12T10:00:00')['error']);
    }

    public function testBrokenInputIsReportedAsAnError(): void
    {
        $this->importer->expects($this->never())->method('importCandidate');

        $this->assertStringContainsString('Failed to import', $this->tool->upsert('editorial', 'x', ['en' => 'T'], 'not a date')['error']);
        $this->assertStringContainsString('needs a name', $this->tool->upsert('editorial', 'x', ['en' => 'T'], '2027-06-12T10:00:00', location: ['city' => 'Cologne'])['error']);
        $this->assertStringContainsString('Failed to import', $this->tool->upsert('editorial', 'x', ['en' => 'T'], '2027-06-12T10:00:00', timezone: 'Mars/Olympus')['error']);
    }

    private function reportWith(string $action, string $detail = ''): ImportReport
    {
        $report = new ImportReport();
        $report->add($action, 'editorial', 'x', 'T', 'uuid-9', $detail);

        return $report;
    }
}
