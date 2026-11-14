# MCP tools

If [sulu/mcp-bundle](https://github.com/sulu/SuluMcpBundle) is installed, the bundle adds tools for AI assistants to work with events. Without that bundle nothing is loaded.

| Tool | Purpose | Permission |
|---|---|---|
| `sulu_event_list` | Events of one locale (drafts), paginated | view |
| `sulu_event_get` | One event with all template fields | view |
| `sulu_event_create` | Create an event as a draft | add |
| `sulu_event_update` | Change fields of an event | edit |
| `sulu_location_list` | Locations with their ids | view |
| `sulu_location_create` | Create a location | add |
| `sulu_event_import_upsert` | Create or update one event as a draft with the rules of the import | add and edit |
| `sulu_event_import_pending` | Imported events that still need work | view |
| `sulu_event_import_report` | Result of the last import run | view |
| `sulu_event_import_reject` | Turn down an imported event: the import leaves it alone from now on (not deleted) | edit |

The permissions are those of the events security context (`sulu.events.events`).

## Create an event

`sulu_event_create` takes `locale`, `title`, `template` (`event`, `event_basic` or `event_detailed`) and the template fields in `content`:

```json
{
  "url": "/events/my-event",
  "type": "conference",
  "startDate": "2026-11-05T09:00:00",
  "endDate": "2026-11-05T17:00:00",
  "locationId": 1,
  "summary": "Short text",
  "text": "<p>Text</p>",
  "image": {"id": 12}
}
```

`url` is required. `locationId` is required unless `attendanceMode` is `online`. Create the location first with `sulu_location_create` (it takes an optional IANA `timezone` such as `America/Los_Angeles`) or take an id from `sulu_location_list`.

Further keys in `content`:

| Key | Value |
|---|---|
| `allDay` | `true`: whole days, the times are set to 00:00:00 and 23:59:59 |
| `timezone` | IANA name for the time of day in `startDate`/`endDate`; leave it out to use the zone of the location or of the website |
| `status` | `scheduled`, `postponed`, `rescheduled`, `cancelled`, `moved_online` |
| `attendanceMode` | `offline`, `online`, `mixed` |
| `onlineUrl`, `registrationUrl`, `organizerUrl` | complete http(s) urls |
| `isFree` | `true` / `false` |
| `priceInfo` | short text of the locale, e.g. `from 99 EUR` |
| `organizerName` | text |
| `languages` | e.g. `["de", "en"]` |
| `excerptCategories` | category ids from the Sulu tool `sulu_category_list` |
| `excerptTags` | tag **ids** (not names) from `sulu_tag_list` / `sulu_tag_create`; unknown ids are ignored |
| `excerpt` | `{"image": {"id": 12}, "title": "...", "description": "..."}`, the image for cards and teasers |

Invalid values (an unknown status, an unknown time zone, a url without `http://` or `https://`) are rejected with a message and nothing is saved. `sulu_event_get` shows the time zone as `auto` for an event without one of its own. More: [Participation, time zone and tickets](online-events.en.md).

Publishing is not part of the tools. Publish in the admin, or with the [bulk actions](https://github.com/manuxi/SuluBulkActionsBundle).

## Update an event

`sulu_event_update` takes `uuid`, `locale`, optionally `title` and the fields to change in `content`:

```json
{"summary": "New short text", "startDate": "2026-11-06T09:00:00"}
```

Only the given fields are changed. Everything else (image, location, dates, categories and so on) stays as it is. A published event becomes a draft again and has to be published again.

Categories and tags are easy to name: `"categories": ["ai", "web-php"]` (keys of the categories) and `"tags": ["Symfony", "AI"]` (names; a tag that does not exist is created). They replace the topics and tags of the locale; unknown category keys are listed in `warnings` of the answer. If the project sets `sulu_event.import.filters.tags_allowed`, only these tags are taken over (case does not matter) and the others are named in `warnings` too. `excerptCategories` and `excerptTags` with ids still work.

## Tools for the import

The four tools belong to the [event import](import.en.md). They are meant for research and editing work: an assistant finds events on the web, enters them, and later completes the ones the import created.

**`sulu_event_import_upsert`** creates or updates **one** event as a draft with the same matching and protection rules as the import command. It takes `source` (where the event comes from, e.g. `editorial`), `externalId` (a stable id inside that source, e.g. the url of the page) and the facts: `titles` (locale => title), `startDate`, `endDate`, `timezone`, `allDay`, `status`, `attendanceMode`, `location` (object) or `locationId`, `onlineUrl`, `registrationUrl`, `organizerName`, `organizerUrl`, `isFree`, `priceInfos`, `languages`, `type`, `categories` (**keys**), `tags` (**names**), `subtitles`, `summaries`, `texts`, `sourceUrl`, `sourceDescription`, `series`, `priority` (default 30) and `dryRun`.

```json
{
  "source": "editorial",
  "externalId": "https://conf.example.com/2027",
  "titles": {"de": "Example Conf 2027"},
  "startDate": "2027-06-12T10:00:00",
  "endDate": "2027-06-13T17:00:00",
  "timezone": "Europe/Berlin",
  "location": {"name": "Messe Hall 3", "city": "Cologne", "countryCode": "DE"},
  "categories": ["web-php"],
  "tags": ["Symfony"],
  "summaries": {"de": "Zwei Tage rund um PHP."},
  "sourceUrl": "https://conf.example.com/2027",
  "series": "example-conf"
}
```

The answer has `action` (`created`, `updated`, `unchanged`, `rejected`, `filtered`), `uuid`, `detail`, `lockedFields` (fields someone changed in the admin: they are not overwritten), `warnings` (an unknown category key, a missing image) and `newLocations`. Calling it again with the same `source` and `externalId` updates the same event. An event that was deleted in the admin or turned down with `sulu_event_import_reject` is not created again (`rejected`). If the project set rules for the import (the [sieve](import.en.md)) and the event does not meet them, nothing is created: the action is `filtered` and `detail` gives the reason. Of `tags` only those on the list `tags_allowed` (if the project sets one) are taken, the others are named in `warnings`. Texts, summaries, categories, tags and the image are only filled in when the event has none. Write the texts yourself: the text of a source is not yours to copy; what you read goes into `sourceDescription`, which is not published.

**`sulu_event_import_pending`** lists imported events that lack something: `text:de`, `summary:en` (empty), `locale:en` (no draft in that language), `categories` (no topic), `image`. Each entry has `sourceUrl` (the page of the event) and `sourceDescription` as an aid for research, and `published`. `locales` limits the languages that are checked (default: `sulu_event.import.locales`, otherwise the languages the event has). Paginated with `page` and `limit`. When `sulu_event.import.auto_publish` is on and covers the event, `autoPublish` says what it still lacks for the automatic publishing: `state` (`ready`, `waiting`, `skipped`), `missing` (`title:de`, `subtitle:en`, `summary:de`, `text:en`, `locale:en`, `categories`, `image`, `place`) and `note`; events that only lack that are listed, too. Complete exactly what is named.

**`sulu_event_import_reject`** says that an imported event is not wanted: `reason` (required, short and factual, e.g. "outside the target region") and either `uuid` or `source` + `externalId` (this also works for a candidate that is not an event yet, so it is never created). The event is **not deleted** (the tools never delete); the import never creates or changes it again, and it no longer shows up in `sulu_event_import_pending`. Drafts that were never published can be moved to the trash later with `sulu:event:import:prune`.

**`sulu_event_import_report`** returns the last run of the import command: counts (including `filtered`: left out by the sieve), errors, vanished events, events with protected fields, new locations (`certain: false` means the time zone was guessed). `includeDryRuns` also considers test runs.

The permissions are those of the events security context. The tools run as the user who is logged in to the MCP server, not as `sulu_event.import.user`.
