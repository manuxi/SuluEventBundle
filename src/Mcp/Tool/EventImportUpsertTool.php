<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Mcp\Tool;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Import\CandidateLocation;
use Manuxi\SuluEventBundle\Import\EventCandidate;
use Manuxi\SuluEventBundle\Import\EventImporter;
use Manuxi\SuluEventBundle\Import\ImportReport;
use Mcp\Capability\Attribute\McpTool;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Mcp\Domain\Security\PermissionRequirement;
use Sulu\Mcp\Domain\Security\RequiresPermission;

/**
 * @internal
 */
class EventImportUpsertTool
{
    public const DEFAULT_PRIORITY = 30;

    public function __construct(private readonly EventImporter $importer)
    {
    }

    /**
     * @param array<string, string>      $titles     locale => title
     * @param array<string, mixed>|null  $location   name (required), street, number, postalCode, city, state, countryCode (ISO), latitude, longitude
     * @param array<string, string>|null $priceInfos locale => price text
     * @param string[]|null              $languages
     * @param string[]|null              $categories keys of Sulu categories (see sulu_category_list)
     * @param string[]|null              $tags       tag names; new tags are created
     * @param array<string, string>|null $subtitles  locale => subtitle
     * @param array<string, string>|null $summaries  locale => short text
     * @param array<string, string>|null $texts      locale => text (HTML)
     *
     * @return array<string, mixed>
     */
    #[McpTool(
        name: 'sulu_event_import_upsert',
        title: 'Import Event (create or update a draft)',
        description: 'Create or update ONE event as a draft, with the same matching and protection rules as the import command. Use it for events found by research. "source" names where the event comes from (for example "editorial"), "externalId" is a stable id inside that source (for example the url of the page): the same source + externalId always updates the same event. If another source delivers the same event, give both the same "series" (for example "ces") and the event is found by series + year of the start: the source with the higher "priority" (default 30) maintains it. Texts, summaries, categories, tags and the image are only filled in when the event has none; fields that someone changed in the admin are locked and reported in "lockedFields", not overwritten. An event that was deleted in the admin is not created again ("rejected"). The result is always a draft, publishing is done in the admin. Pass "titles" (locale => title), "startDate" (e.g. 2027-06-12T10:00:00 or with offset) and "timezone" (IANA name of the zone the times are meant in, e.g. Europe/Berlin; leave out if unknown). Place: "location" object (name required, plus city, countryCode, latitude, longitude; an existing place is found by name and city or coordinates, a new one is created) or "locationId" from sulu_location_list. Texts per locale in "texts"/"summaries"/"subtitles" must be written by you, do not copy the text of a page; put what you read in "sourceDescription" (not published, only a hint for editors). "categories" are category keys, "tags" names. Use dryRun to see the result without saving.',
    )]
    #[RequiresPermission(requirements: [
        new PermissionRequirement(Event::SECURITY_CONTEXT, PermissionTypes::ADD),
        new PermissionRequirement(Event::SECURITY_CONTEXT, PermissionTypes::EDIT),
    ])]
    public function upsert(
        string $source,
        string $externalId,
        array $titles,
        string $startDate,
        ?string $endDate = null,
        ?string $timezone = null,
        bool $allDay = false,
        string $status = 'scheduled',
        string $attendanceMode = 'offline',
        ?array $location = null,
        ?int $locationId = null,
        ?string $onlineUrl = null,
        ?string $registrationUrl = null,
        ?string $organizerName = null,
        ?string $organizerUrl = null,
        ?bool $isFree = null,
        ?array $priceInfos = null,
        ?array $languages = null,
        ?string $type = null,
        ?array $categories = null,
        ?array $tags = null,
        ?array $subtitles = null,
        ?array $summaries = null,
        ?array $texts = null,
        ?string $sourceUrl = null,
        ?string $sourceDescription = null,
        ?string $series = null,
        ?int $priority = null,
        bool $dryRun = false,
    ): array {
        try {
            if (1 !== preg_match('/^[a-z0-9][a-z0-9._-]{0,63}$/', $source)) {
                return ['error' => 'The source may only contain lower case letters, digits, ".", "-" and "_" (for example "editorial").'];
            }
            if ('' === trim($externalId)) {
                return ['error' => 'The externalId must not be empty (use a stable id such as the url of the page).'];
            }

            $candidate = new EventCandidate(
                externalId: $externalId,
                titles: $this->strings($titles),
                startDate: $this->parseDate($startDate, $timezone),
                endDate: null !== $endDate && '' !== $endDate ? $this->parseDate($endDate, $timezone) : null,
                allDay: $allDay,
                timezone: $timezone,
                status: $status,
                attendanceMode: $attendanceMode,
                location: null !== $location ? $this->createLocation($location) : null,
                onlineUrl: $onlineUrl,
                registrationUrl: $registrationUrl,
                organizerName: $organizerName,
                organizerUrl: $organizerUrl,
                isFree: $isFree,
                priceInfos: $this->strings($priceInfos ?? []),
                languages: array_values(array_map('strval', $languages ?? [])),
                type: $type,
                categoryKeys: array_values(array_map('strval', $categories ?? [])),
                tags: array_values(array_map('strval', $tags ?? [])),
                subtitles: $this->strings($subtitles ?? []),
                summaries: $this->strings($summaries ?? []),
                texts: $this->strings($texts ?? []),
                sourceUrl: $sourceUrl,
                sourceDescription: $sourceDescription,
                series: $series,
                locationId: $locationId,
            );

            $report = $this->importer->importCandidate($source, $priority ?? self::DEFAULT_PRIORITY, $candidate, $dryRun);

            return $this->describe($report, $dryRun);
        } catch (\Throwable $e) {
            return ['error' => \sprintf('Failed to import the event: %s', $e->getMessage())];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function describe(ImportReport $report, bool $dryRun): array
    {
        $entry = $report->getEntries()[0] ?? null;
        if (null === $entry) {
            return ['error' => 'The import did not report anything.'];
        }

        $result = [
            'success'      => ImportReport::ACTION_ERROR !== $entry['action'],
            'action'       => $entry['action'],
            'uuid'         => $entry['eventUuid'],
            'detail'       => $entry['detail'],
            'lockedFields' => $entry['lockedFields'],
            'warnings'     => array_column($report->getWarnings(), 'message'),
            'newLocations' => $report->getNewLocations(),
            'dryRun'       => $dryRun,
        ];

        if (ImportReport::ACTION_ERROR === $entry['action']) {
            $result['error'] = $entry['detail'];
        }

        return $result;
    }

    /**
     * @param array<array-key, mixed> $values
     *
     * @return array<string, string>
     */
    private function strings(array $values): array
    {
        $strings = [];
        foreach ($values as $key => $value) {
            if (\is_string($value) && '' !== trim($value)) {
                $strings[(string) $key] = $value;
            }
        }

        return $strings;
    }

    /**
     * A time of day is read in the given zone; a value with an offset is converted into it. Without a zone the clock
     * time as written is kept.
     */
    private function parseDate(string $value, ?string $timezone): \DateTimeImmutable
    {
        $zone = new \DateTimeZone(null !== $timezone && '' !== $timezone ? $timezone : date_default_timezone_get());
        $date = new \DateTimeImmutable($value, $zone);

        if (null !== $timezone && '' !== $timezone && 1 === preg_match('/(Z|[+-]\d{2}:?\d{2})$/i', trim($value))) {
            $date = $date->setTimezone($zone);
        }

        return $date;
    }

    /**
     * @param array<string, mixed> $location
     */
    private function createLocation(array $location): CandidateLocation
    {
        $name = isset($location['name']) && \is_string($location['name']) ? trim($location['name']) : '';
        if ('' === $name) {
            throw new \InvalidArgumentException('The location needs a name.');
        }

        $text = static fn (string $key): ?string => isset($location[$key]) && \is_string($location[$key]) && '' !== trim($location[$key]) ? trim($location[$key]) : null;
        $num  = static fn (string $key): ?float => isset($location[$key]) && is_numeric($location[$key]) ? (float) $location[$key] : null;

        return new CandidateLocation(
            $name,
            $text('street'),
            $text('number'),
            $text('postalCode'),
            $text('city'),
            $text('state'),
            $text('countryCode'),
            $num('latitude'),
            $num('longitude'),
        );
    }
}
