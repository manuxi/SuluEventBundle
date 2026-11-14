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
    public const ACTION_FILTERED  = 'filtered';
    public const ACTION_ERROR     = 'error';

    /** @var array{created: int, updated: int, unchanged: int, locked: int, vanished: int, rejected: int, filtered: int, errors: int, newLocations: int} */
    private array $counts = [
        'created'      => 0,
        'updated'      => 0,
        'unchanged'    => 0,
        'locked'       => 0,
        'vanished'     => 0,
        'rejected'     => 0,
        'filtered'     => 0,
        'errors'       => 0,
        'newLocations' => 0,
    ];

    /** @var list<array<string, mixed>> */
    private array $entries = [];

    /** @var array<string, array{code: string, country: string|null, count: int}> why candidates were sieved out, grouped (see addFiltered) */
    private array $filteredReasons = [];

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
     * A candidate the sieve did not let pass (sulu_event.import.filters). Counted, and listed by "--explain", but not kept
     * in the stored report: there are many of them in every run.
     */
    public function addFiltered(string $source, string $externalId, ?string $title, ?string $country, string $code, string $reason, ?string $eventUuid = null): void
    {
        ++$this->counts['filtered'];
        $this->entries[] = [
            'action'       => self::ACTION_FILTERED,
            'source'       => $source,
            'externalId'   => $externalId,
            'title'        => $title,
            'eventUuid'    => $eventUuid,
            'detail'       => $reason,
            'lockedFields' => [],
            'country'      => $country,
            'code'         => $code,
        ];

        // the reasons by kind: the code, and for the country rules the country (the text of the reason holds dates and lists)
        $byCountry = str_starts_with($code, 'country_');
        $group     = $code . ($byCountry ? ':' . $country : '');
        $this->filteredReasons[$group] ??= ['code' => $code, 'country' => $byCountry ? $country : null, 'count' => 0];
        ++$this->filteredReasons[$group]['count'];
    }

    /**
     * Why candidates were sieved out, the most frequent reason first. Small (a few dozen lines at most), so it is stored
     * with the run: the report mail and the admin can say what the sieve did without keeping every candidate.
     *
     * @return list<array{code: string, country: string|null, count: int}>
     */
    public function getFilteredReasons(): array
    {
        $reasons = array_values($this->filteredReasons);
        usort($reasons, static fn (array $a, array $b): int => [$b['count'], $a['code'], (string) $a['country']] <=> [$a['count'], $b['code'], (string) $b['country']]);

        return $reasons;
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
     * @return array{created: int, updated: int, unchanged: int, locked: int, vanished: int, rejected: int, filtered: int, errors: int, newLocations: int}
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
     * @return array<string, mixed> the part stored with the run: entries that are more than "unchanged" (and not sieved out)
     */
    public function toArray(): array
    {
        return [
            'entries'         => array_values(array_filter(
                $this->entries,
                static fn (array $entry): bool => self::ACTION_FILTERED !== $entry['action'] && (self::ACTION_UNCHANGED !== $entry['action'] || [] !== $entry['lockedFields'])
            )),
            'newLocations'    => $this->newLocations,
            'warnings'        => $this->warnings,
            // only the sum per reason: the sieved candidates themselves are not kept
            'filteredReasons' => $this->getFilteredReasons(),
        ];
    }
}
