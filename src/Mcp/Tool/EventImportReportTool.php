<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Mcp\Tool;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventImportRun;
use Manuxi\SuluEventBundle\Repository\EventImportRunRepository;
use Mcp\Capability\Attribute\McpTool;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Mcp\Domain\Security\PermissionRequirement;
use Sulu\Mcp\Domain\Security\RequiresPermission;

/**
 * @internal
 */
class EventImportReportTool
{
    public function __construct(private readonly EventImportRunRepository $runRepository)
    {
    }

    /**
     * @return array<string, mixed>
     */
    #[McpTool(
        name: 'sulu_event_import_report',
        title: 'Result of the last import run',
        description: 'The result of the last run of the import command: when it ran, the counts (created, updated, unchanged, locked, vanished, rejected, filtered = left out by the sieve, errors, newLocations), the errors, the locations that were created (please check their name and time zone; "certain": false means the zone was guessed), events the source does not deliver any more ("vanished") and events with locked fields. "includeDryRuns" also considers test runs. "autoPublish" is the result of the last run of the automatic publishing (sulu:event:import:publish-ready, see sulu_event.import.auto_publish): how many events were published, are waiting or were skipped, which ones were published and what the waiting ones still lack. Returns nothing if the import never ran.',
    )]
    #[RequiresPermission(requirements: [
        new PermissionRequirement(Event::SECURITY_CONTEXT, PermissionTypes::VIEW),
    ])]
    public function report(bool $includeDryRuns = false): array
    {
        try {
            $run         = $this->runRepository->findLast($includeDryRuns);
            $autoPublish = $this->runRepository->findLastAutoPublish($includeDryRuns);
            if (null === $run) {
                return ['run' => null, 'message' => 'The import has not run yet.', 'autoPublish' => $this->autoPublish($autoPublish)];
            }

            $report  = $run->getReport();
            $entries = $report['entries'] ?? [];
            $byKind  = static fn (string $action): array => array_values(array_filter($entries, static fn (array $entry): bool => $action === ($entry['action'] ?? null)));

            return [
                'run' => [
                    'startedAt'  => $run->getStartedAt()->format(\DATE_ATOM),
                    'finishedAt' => $run->getFinishedAt()?->format(\DATE_ATOM),
                    'source'     => $run->getSource(),
                    'dryRun'     => $run->isDryRun(),
                    'counts'     => $run->getCounts(),
                ],
                'errors'       => $byKind('error'),
                'vanished'     => $byKind('vanished'),
                'locked'       => array_values(array_filter($entries, static fn (array $entry): bool => [] !== ($entry['lockedFields'] ?? []))),
                'newLocations' => $report['newLocations'] ?? [],
                'warnings'     => $report['warnings']     ?? [],
                'autoPublish'  => $this->autoPublish($autoPublish),
            ];
        } catch (\Throwable $e) {
            return ['error' => \sprintf('Failed to read the import report: %s', $e->getMessage())];
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function autoPublish(?EventImportRun $run): ?array
    {
        if (null === $run) {
            return null;
        }

        return [
            'startedAt' => $run->getStartedAt()->format(\DATE_ATOM),
            'dryRun'    => $run->isDryRun(),
        ] + ($run->getReport()['autoPublish'] ?? []);
    }
}
