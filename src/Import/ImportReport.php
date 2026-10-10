<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import;

/**
 * What one run of the import did: a counter per kind of result and one entry per candidate (or source) that needs a
 * look. Stored with the run (EventImportRun) so the admin and the MCP tools can show it.
 */
class ImportReport
{
    public const ACTION_CREATED   = 'created';
    public const ACTION_UPDATED   = 'updated';
    public const ACTION_UNCHANGED = 'unchanged';
    public const ACTION_VANISHED  = 'vanished';
    public const ACTION_REJECTED  = 'rejected';
    public const ACTION_ERROR     = 'error';

    /** @var array{created: int, updated: int, unchanged: int, locked: int, vanished: int, rejected: int, errors: int, newLocations: int} */
    private array $counts = [
        'created'      => 0,
        'updated'      => 0,
        'unchanged'    => 0,
        'locked'       => 0,
        'vanished'     => 0,
        'rejected'     => 0,
        'errors'       => 0,
        'newLocations' => 0,
    ];

    /** @var list<array<string, mixed>> */
    private array $entries = [];

    /** @var list<array{name: string, timezone: string|null, certain: bool}> */
    private array $newLocations = [];

    /** @var list<array{source: string, externalId: string|null, message: string}> */
    private array $warnings = [];

    /**
     * @param string[] $lockedFields fields that were not overwritten because someone changed them in the admin
     */
    public function add(
        string $action,
        string $source,
        ?string $externalId,
        ?string $title = null,
        ?string $eventUuid = null,
        string $detail = '',
        array $lockedFields = [],
    ): void {
        $key = self::ACTION_ERROR === $action ? 'errors' : $action;
        ++$this->counts[$key];
        if ([] !== $lockedFields) {
            ++$this->counts['locked'];
        }

        $this->entries[] = [
            'action'       => $action,
            'source'       => $source,
            'externalId'   => $externalId,
            'title'        => $title,
            'eventUuid'    => $eventUuid,
            'detail'       => $detail,
            'lockedFields' => array_values($lockedFields),
        ];
    }

    /**
     * A remark that is no error and not counted (an unknown category key, ...).
     */
    public function addWarning(string $source, ?string $externalId, string $message): void
    {
        $this->warnings[] = ['source' => $source, 'externalId' => $externalId, 'message' => $message];
    }

    public function addNewLocation(string $name, ?string $timezone, bool $certain): void
    {
        ++$this->counts['newLocations'];
        $this->newLocations[] = ['name' => $name, 'timezone' => $timezone, 'certain' => $certain];
    }

    /**
     * @return array{created: int, updated: int, unchanged: int, locked: int, vanished: int, rejected: int, errors: int, newLocations: int}
     */
    public function getCounts(): array
    {
        return $this->counts;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * @return list<array{name: string, timezone: string|null, certain: bool}>
     */
    public function getNewLocations(): array
    {
        return $this->newLocations;
    }

    /**
     * @return list<array{source: string, externalId: string|null, message: string}>
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function hasErrors(): bool
    {
        return $this->counts['errors'] > 0;
    }

    /**
     * @return array<string, mixed> the part stored with the run: entries that are more than "unchanged"
     */
    public function toArray(): array
    {
        return [
            'entries' => array_values(array_filter(
                $this->entries,
                static fn (array $entry): bool => self::ACTION_UNCHANGED !== $entry['action'] || [] !== $entry['lockedFields']
            )),
            'newLocations' => $this->newLocations,
            'warnings'     => $this->warnings,
        ];
    }
}
