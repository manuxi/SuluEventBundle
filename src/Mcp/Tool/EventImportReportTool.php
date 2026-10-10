<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Mcp\Tool;

use Manuxi\SuluEventBundle\Entity\Event;
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
        description: 'The result of the last run of the import command: when it ran, the counts (created, updated, unchanged, locked, vanished, rejected, errors, newLocations), the errors, the locations that were created (please check their name and time zone; "certain": false means the zone was guessed), events the source does not deliver any more ("vanished") and events with locked fields. "includeDryRuns" also considers test runs. Returns nothing if the import never ran.',
    )]
    #[RequiresPermission(requirements: [
        new PermissionRequirement(Event::SECURITY_CONTEXT, PermissionTypes::VIEW),
    ])]
    public function report(bool $includeDryRuns = false): array
    {
        try {
            $run = $this->runRepository->findLast($includeDryRuns);
            if (null === $run) {
                return ['run' => null, 'message' => 'The import has not run yet.'];
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
            ];
        } catch (\Throwable $e) {
            return ['error' => \sprintf('Failed to read the import report: %s', $e->getMessage())];
        }
    }
}
