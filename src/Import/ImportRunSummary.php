<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import;

use Manuxi\SuluEventBundle\Entity\EventImportRun;

/**
 * A run of the import as plain text for the read only "Import" tab of the settings.
 */
final class ImportRunSummary
{
    /**
     * @return array{importLastRunWhen: string, importLastRunSummary: string, importLastRunErrors: string|null, importLastRunNewLocations: string|null}
     */
    public static function fromRun(EventImportRun $run): array
    {
        $counts = $run->getCounts();
        $report = $run->getReport();

        $when = $run->getStartedAt()->format('Y-m-d H:i');
        if ($run->isDryRun()) {
            $when .= ' (dry run)';
        }
        if (null !== $run->getSource()) {
            $when .= ' - ' . $run->getSource();
        }

        $errors = [];
        foreach ($report['entries'] ?? [] as $entry) {
            if (ImportReport::ACTION_ERROR === ($entry['action'] ?? null)) {
                $errors[] = trim(($entry['source'] ?? '') . ' ' . ($entry['title'] ?? $entry['externalId'] ?? '') . ': ' . ($entry['detail'] ?? ''));
            }
        }

        $locations = [];
        foreach ($report['newLocations'] ?? [] as $location) {
            $locations[] = \sprintf('%s (%s%s)', $location['name'], $location['timezone'] ?? '?', ($location['certain'] ?? true) ? '' : ', guessed');
        }

        return [
            'importLastRunWhen'         => $when,
            'importLastRunSummary'      => \sprintf(
                'new %d, changed %d, unchanged %d, locked %d, vanished %d, rejected %d, errors %d, new locations %d',
                $counts['created'],
                $counts['updated'],
                $counts['unchanged'],
                $counts['locked'],
                $counts['vanished'],
                $counts['rejected'],
                $counts['errors'],
                $counts['newLocations'],
            ),
            'importLastRunErrors'       => [] === $errors ? null : implode("\n", $errors),
            'importLastRunNewLocations' => [] === $locations ? null : implode("\n", $locations),
        ];
    }
}
