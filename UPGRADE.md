# Upgrade

## 3.6.0

Minor release, backwards compatible: existing events and templates keep working. It brings the iCal and calendar fixes, new event fields (participation, time zone, status, tickets), filters, schema.org output, the optional event import with sieve and automatic publishing, the event type `launch`, highlights and a calendar API rate limit that works.

### Upgrade steps

1. `composer update manuxi/sulu-event-bundle` (new requirements: `sabre/vobject`, `manuxi/sulu-block-validation-bundle`, `symfony/http-client`, `symfony/string`).
2. Register `Manuxi\SuluBlockValidationBundle\SuluBlockValidationBundle` in `config/bundles.php` (see below).
3. Run the SQL of [Database](#database), and the SQL of [Event import](#event-import) only if you use the import. Without the import tables admin and website keep working, the tab "Source" is missing then.
4. Optional: set `all_day` for events that were all-day before (see [Database](#database)).
5. Check overrides of the templates in `config/templates/events/` and links to `sulu_event.ical_single` (see below).
6. Clear the caches of admin and website (`bin/adminconsole cache:clear`, `bin/websiteconsole cache:clear`) and rebuild the admin if you use the "Import now" button.
7. Optional: `sulu_event.timezone`, `category_root`, `featured_tag`, `sitemap`, `import`.

### New commands

All new commands belong to the import and do nothing until you use them:

| Command | Purpose |
| --- | --- |
| `sulu:event:import` | creates and updates event drafts from the configured sources (`--dry-run`, `--explain`, `--source=`, `--limit=`) |
| `sulu:event:import:publish-ready` | publishes complete imported events by your rules (`--dry-run`, `--limit=`) |
| `sulu:event:import:prune` | moves never published imported events that the sieve or a rejection left out to the Sulu trash; published ones are only listed (`--dry-run`, `--state=`, `--force`) |
| `sulu:event:fill-images` | gives events and places without an image the best image that is known |

### Database

New columns, all with a default, so existing events keep their behaviour. Run the SQL (or let `doctrine:schema:update --dump-sql` / a Doctrine migration of your project produce it; table names are the ones of the bundle):

```sql
ALTER TABLE app_event_dimension_content
    ADD all_day TINYINT DEFAULT 0 NOT NULL,
    ADD timezone VARCHAR(64) DEFAULT NULL,
    ADD status VARCHAR(20) DEFAULT 'scheduled' NOT NULL,
    ADD attendance_mode VARCHAR(10) DEFAULT 'offline' NOT NULL,
    ADD online_url VARCHAR(2048) DEFAULT NULL,
    ADD registration_url VARCHAR(2048) DEFAULT NULL,
    ADD is_free TINYINT DEFAULT NULL,
    ADD price_info VARCHAR(255) DEFAULT NULL,
    ADD organizer_name VARCHAR(255) DEFAULT NULL,
    ADD organizer_url VARCHAR(2048) DEFAULT NULL,
    ADD languages JSON DEFAULT NULL;

ALTER TABLE app_location ADD timezone VARCHAR(64) DEFAULT NULL;
```

`timezone` stays `NULL` for existing events and locations: that means "the zone of the location, otherwise the one of the website", which is what was assumed so far. Set it only where it is needed (`UPDATE app_event_dimension_content SET timezone = 'Europe/Berlin'` pins every event to one zone).

**All-day events are no longer guessed from the times.** Before, the calendar API treated an event without an end as all-day, and an event from 00:00:00 to 23:59:59 as well. Now only the new flag counts. To keep events that were all-day by that rule, set the flag once:

```sql
UPDATE app_event_dimension_content
SET all_day = 1
WHERE start_date IS NOT NULL
  AND TIME(start_date) = '00:00:00'
  AND (end_date IS NULL OR TIME(end_date) = '23:59:59');
```

### New dependency: SuluBlockValidationBundle

The location is optional for online events. That needs the conditional validation of [manuxi/sulu-block-validation-bundle](https://github.com/manuxi/SuluBlockValidationBundle) (`^1.2`), which Composer installs with the bundle. Register it in `config/bundles.php` if it is not there yet:

```php
Manuxi\SuluBlockValidationBundle\SuluBlockValidationBundle::class => ['all' => true],
```

### New fields in the event templates

`event`, `event_basic` and `event_detailed` have new fields (all day, participation, the section "Participation & tickets" with status, time zone, languages, tickets, online link, price, organizer) and the location is no longer mandatory for online events. If your project **copies or overrides** these templates (`config/templates/events/`), take the new properties over from the bundle's templates. See [Participation, time zone and tickets](docs/online-events.en.md).

### Filters, filter options, structured data

- **Calendar API and iCal feed** accept `types[]`, `attendanceMode[]`, `country[]` and `free=1` as well; lists may be arrays or comma separated (`categories=12,15` works now, a scalar `categories=12` no longer answers 400).
- **Smart content** filters by event type, way to take part, country and free of charge: from the query string (`?type=conference,meetup&attendanceMode=online,mixed&country=DE&free=1`, names changeable with `type_parameter`, `attendance_mode_parameter`, `country_parameter`, `free_parameter`) and as fixed filters in the "types" list of the field in the admin. The constructor of `EventSmartContentProvider` takes the `RequestStack` as sixth argument.
- **`sulu_event_filter_options(locale)`** (Twig) lists topics, types, countries, ways to take part and free events with the number of upcoming published events; cached in `cache.app.taggable` (if it exists) and cleared when an event changes. New option `sulu_event.category_root` (key of a root category) limits the topics to its descendants.
- **`sulu_event_schema(event)`** returns the schema.org `Event` as an array, `sulu_event_datetime(event, 'start'|'end')` and `sulu_event_timezone(event)` the times in the zone of the event, see [docs/schema-org.en.md](docs/schema-org.en.md). Twig's `date` filter converts to the zone of the server: use `|date('c', false)`.
- **Example template** `event_detail_example.html.twig`: status notice, online marker, local time, ticket and stream link, organizer, price, JSON-LD; the calls of `event_og_tags`, `event_twitter_tags`, `event_social_shares` pass `event.resource` and the locale now (the template did not compile before). New translations in `messages.de/en.yaml` (`sulu_event.display.*`, `sulu_event.add_to_calendar`).
- **Sitemap**: new option `sulu_event.sitemap.hide_past_after_days` (default `null`: nothing changes) leaves out events that ended more than that many days ago.
- New services: `sulu_event.url_resolver` (`EventUrlResolver`, used by iCal, calendar API and schema.org), `sulu_event.filter_options`, `sulu_event.schema_builder`. `EventTypeSelect::getTypeName()` takes an optional locale.

### Event import

New and optional: the command `sulu:event:import` creates event drafts from iCal feeds and pages with schema.org data, see [docs/import.en.md](docs/import.en.md). It is **off** until `sulu_event.import.enabled` is set; you only need the steps below if you use it.

Two new tables (names are the ones of the bundle; or let `doctrine:schema:update --dump-sql` / a migration of your project produce them):

```sql
CREATE TABLE app_event_import (
    source VARCHAR(64) NOT NULL,
    external_id VARCHAR(255) NOT NULL,
    match_key VARCHAR(128) DEFAULT NULL,
    last_event_uuid VARCHAR(36) DEFAULT NULL,
    content_hash VARCHAR(64) DEFAULT NULL,
    imported_values JSON NOT NULL,
    locked_fields JSON NOT NULL,
    state VARCHAR(16) NOT NULL,
    state_reason VARCHAR(255) DEFAULT NULL,
    source_url VARCHAR(2048) DEFAULT NULL,
    priority INT NOT NULL,
    last_seen_at DATETIME DEFAULT NULL,
    last_synced_at DATETIME DEFAULT NULL,
    last_error LONGTEXT DEFAULT NULL,
    created_at DATETIME NOT NULL,
    id INT AUTO_INCREMENT NOT NULL,
    event_uuid VARCHAR(36) DEFAULT NULL,
    INDEX idx_event_import_match_key (match_key),
    INDEX idx_event_import_state (state),
    INDEX idx_event_import_last_event (last_event_uuid),
    UNIQUE INDEX uniq_event_import_source_external (source, external_id),
    INDEX IDX_6D59A3CECEB41C0D (event_uuid),
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`;

CREATE TABLE app_event_import_run (
    started_at DATETIME NOT NULL,
    finished_at DATETIME DEFAULT NULL,
    source VARCHAR(64) DEFAULT NULL,
    dry_run TINYINT NOT NULL,
    created INT NOT NULL,
    updated INT NOT NULL,
    unchanged INT NOT NULL,
    locked INT NOT NULL,
    vanished INT NOT NULL,
    rejected INT NOT NULL,
    filtered INT DEFAULT 0 NOT NULL,
    errors INT NOT NULL,
    new_locations INT NOT NULL,
    report JSON NOT NULL,
    id INT AUTO_INCREMENT NOT NULL,
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`;

ALTER TABLE app_event_import ADD CONSTRAINT FK_6D59A3CECEB41C0D FOREIGN KEY (event_uuid) REFERENCES app_event (uuid) ON DELETE SET NULL;
```

The class `Event` has a new read only collection `imports` (the inverse side of `EventImport::event`, no database change). `EventNormalizer` takes the `EventImportInfoProvider` as sixth constructor argument (optional) and adds `importSource` and related keys to the form data; `SettingsController` takes the `EventImportRunRepository` as fifth argument (optional). The field `text` of the event templates (`event`, `event_basic`, `event_detailed`) is **no longer mandatory**: an imported draft has no text until it is written (by hand or by the editorial work), and a form that cannot be saved without it blocks every other change of the draft. If your project has its own copy of these templates and wants the text mandatory, keep `mandatory="true"` there. New option `sulu_event.import.placeholder_collections` (keys of system collections whose images are placeholders, for example generated cards; `ImportSettings` has a new last constructor argument `placeholderCollections`, `MediaResolver` an optional second argument `SystemCollectionManagerInterface` and the method `isInSystemCollections()`, `EventImageChooser` the method `isCard()`). New option `sulu_event.import.series_images` (a photo for the events of a series; the image of an event is now chosen by the new class `EventImageChooser`: photo of the series, photo of the place, placeholder of the topic; `ImportSettings` has a new last constructor argument `seriesImages`, `ImageFiller` a new optional last argument `EventImportRepository`, and a placeholder image of an event is replaced by `sulu:event:fill-images` as soon as a better image is known). New option `sulu_event.import.admin_button` (the button "Import now" in the tab "Import" of the settings, off by default; it needs the JavaScript of the bundle in your admin build, see docs/import.en.md; the constructors of `SettingsAdmin` (optional 3rd argument) and `SettingsController` (optional 6th and 7th arguments) got new arguments, and the service `sulu_event.import.runner` is new). New options `sulu_event.import.location_aliases` (other spellings of a place or city and the one that leads: "Brüssel: Brussels"; the constructor of `LocationResolver` takes them as an optional third argument; a city with a state or country behind it is the same city) and `sulu_event.import.location_image` (image of the places the import creates) and the command `sulu:event:fill-images` (images for events and places that have none). With `sulu_event.import.locales` every listed language gets a draft at once (title of the source, same facts), so that only the texts are missing; `sulu_event_update` takes `categories` (keys) and `tags` (names) as well. New options `sulu_event.import.category_images`, `sulu_event.import.locales` and `image` per source (images of your own for imported events), the tab "Source" of events from the import, the columns and filters in the list of events, the tab "Import" of the settings, and the MCP tools `sulu_event_import_upsert`, `sulu_event_import_pending` and `sulu_event_import_report`: see [docs/import.en.md](docs/import.en.md) and [docs/mcp.en.md](docs/mcp.en.md). If your project overrides the list `events.xml` or the services `sulu_event.event_normalizer` / `Manuxi\SuluEventBundle\Controller\Admin\SettingsController`, take the additions over.

New requirements, installed by Composer with the bundle: `symfony/http-client` and `symfony/string` (`composer update manuxi/sulu-event-bundle`). New services are `sulu_event.import.*` and the command `sulu:event:import`; sources that you tag with `sulu_event.import_source` are picked up. No existing behaviour changes.

### Sieve, rejecting and cleaning up in the import

New and optional (only for projects that use the import): `sulu_event.import.filters`, `sulu_event.import.source_filters` and `filters` per source leave candidates out by rules of the project (countries, online, types, topics, title, host, months ahead; `tags_allowed` limits the tags), see [docs/import.en.md](docs/import.en.md) ("The sieve"). Without rules nothing changes. New: the state `filtered` and the column `state_reason` of an import entry, the counter `filtered` of a run (both columns are part of the `CREATE TABLE` statements above), the option `--explain` of `sulu:event:import`, the command `sulu:event:import:prune` and the MCP tool `sulu_event_import_reject`. The importer also treats the same event twice in one delivery as one candidate and follows a source that changes the id of an event (no second event, no "vanished" for the old one).

The constructor of `EventImporter` takes the sieve (`CandidateFilter`, optional) as 16th argument, `EventUpdateTool` as 8th (optional). The state of an entry deleted in the admin now also carries a reason. If your project overrides `Resources/config/lists/events.xml` or `forms/event_import_source.xml`, take over the new filter options and the field `importStateReason`.

### Automatic publishing of complete events

New and optional (only for projects that use the import): `sulu_event.import.auto_publish` (`enabled`, `when`, `require`, `republish_changes`) and the command `sulu:event:import:publish-ready`, see [docs/import.en.md](docs/import.en.md) ("Publish automatically"). Off by default; without it nothing changes. **No database change:** the result of a run is stored as a row of `app_event_import_run` with the source `auto-publish`; `EventImportRunRepository::findLast()` leaves these rows out, `findLastAutoPublish()` returns them. The constructor of `PendingEventFinder` takes the publisher (`AutoPublisher`, optional) as 4th argument; `sulu_event_import_pending` then adds `autoPublish` to each event. If your project overrides `forms/events_settings_import.xml`, take over the section `import-auto-publish`.

### Highlights

No database change. New option `sulu_event.featured_tag` (name of a tag, default `null` = off): an event that carries this tag is a **highlight**. With it:

- the calendar API (`extendedProps.featured`, `extendedProps.categories` as keys, `extendedProps.tags` as names), the iCal feed, smart content and the filter options (`sulu_event_filter_options(locale).featured`) accept `featured=1` / offer "Highlights" with a count; the normalizer (and so Twig) gives `event.featured`;
- an import source can set the tag: `featured: true` on a source or `sulu_event.import.featured_sources: [source-key]` for sources of your project; `sulu_event_import_upsert` takes `featured`, `sulu_event_update` takes `featured` in `content`.

Without `featured_tag` nothing changes, except that every calendar event now also carries `featured: false`, `categories` and `tags` in `extendedProps` and `sulu_event_filter_options` has the key `featured` (`null`). The cache key of the filter options changed, so old cache entries are not read. New service `sulu_event.featured_tag` (`FeaturedTag`); `CalendarApiController`, `ICalController`, `EventNormalizer`, `EventSmartContentProvider`, `EventFilterOptions`, `EventImporter` and `EventUpdateTool` take it as an optional last constructor argument, `ImportSettings` the list `featuredSources`.

### New default event types

`meetup`, `trade_fair`, `keynote`, `convention`, `hackathon` and `launch` (with translations). Projects that define their own `sulu_event.types` replace the defaults completely and add the ones they want, with a name (translation key) and a colour.

### New dependency: sabre/vobject

The iCal output is built with [sabre/vobject](https://sabre.io/vobject/) (`^5.0`, BSD-3-Clause, needs PHP 8.2+; it brings `sabre/uri` and `sabre/xml` along). Composer installs it with the bundle:

```bash
composer update manuxi/sulu-event-bundle
```

### Changed route of the single event download

`sulu_event.ical_single` takes the **UUID** of the event now. The route required a number and looked the event up by a column that does not exist, so the download never worked.

```
/events/{uuid}/download.{locale}.ics
```

Templates that link to it must pass the UUID. In the event detail template `event` is the content of the event, so use `event.resource.id`, not `event.id` (that is the internal number of the content):

```twig
{# before #}
{{ path('sulu_event.ical_single', {id: event.id, _locale: app.request.locale}) }}

{# after #}
{{ path('sulu_event.ical_single', {id: event.resource.id, _locale: app.request.locale}) }}
```

The bundle's example template (`event_detail_example.html.twig`) is updated. Check your own templates and overrides for `ical_single`.

### New configuration: timezone

Event times are stored as a time of day without a zone. The iCal export reads them in this zone and converts them to UTC:

```yaml
# config/packages/sulu_event.yaml
sulu_event:
    timezone: 'Europe/Berlin'   # optional; the PHP default timezone applies if omitted
```

Without the option nothing changes for installations whose PHP default timezone is the zone the editors work in.

### Behaviour changes you may notice

- **Times in the iCal files are correct now.** Before, the time of day was written with a trailing `Z`, so an event at 10:00 in Berlin showed up one or two hours off in calendar applications. Subscribers see the corrected times after their next refresh.
- **UIDs.** They are `<uuid>@<host>` as before, but the host is the one of the website of the event (webspace and locale) instead of the host header of the request. If a feed was fetched under a different host name before, subscribers may see the events twice once.
- **`URL` and `CATEGORIES`.** The iCal events carry an absolute URL (was: only the path) and the names of their categories in the locale of the feed.
- **Feed and calendar filters work.** `/events/calendar.{locale}.ics` and `/api/events/calendar/{locale}` evaluate `categories[]` (ids), `tags[]` (names) and, for the calendar, `location` (id or exact name). Before, they were ignored and every event was returned. A filter without any usable value returns no events. Only events published in the locale are returned.
- **Calendar range.** `/api/events/calendar/{locale}` returns every event that *overlaps* the requested range (was: events that start inside it), so multi-day events that began earlier are shown.
- **Calendar API works again.** `/api/events/calendar/{locale}` answered 500 ("has no container set"): `services-calendar.yaml` redefined the controller without the container and overrode the complete definition in `controller.yaml`. The duplicate is removed. If you overrode the service `Manuxi\SuluEventBundle\Controller\Website\CalendarApiController` yourself, it needs the container (`calls: setContainer`) and the constructor arguments listed under "For developers".
- **RSS and Atom feeds work again.** `/events/feed.{locale}.rss` and `.atom` answered 500 ("has no container set"): `services-feed.yaml` redefined the `FeedController` without the container. The duplicate is removed (the file is gone). If you overrode the service `Manuxi\SuluEventBundle\Controller\Website\FeedController` yourself, it needs `calls: setContainer`.
- **Calendar API rate limit works.** The limit of 100 requests per minute and IP address never took effect (the controller looked up a limiter service that does not exist and swallowed every error). It does now: the 101st request within a minute gets `429` with `Retry-After`. Needs `symfony/rate-limiter` (already required); scripts or proxies that fetch the API more often must be spread out or the limiter `framework.rate_limiter.sulu_event_calendar_api` raised. If you built the controller service yourself, pass `@?limiter.sulu_event_calendar_api` as sixth argument.
- **Events that are not published answer 404.** The route provider (`EventRouteDefaultsProvider`) and `Website\EventController` fell back to the draft when an event had no live content, so unpublished events (for example imported drafts) could be read on the website without logging in. Only live content is shown now; the preview of the admin is not affected (it has its own provider). If you rely on drafts being visible on a staging website, publish the events there.
- **The import reads the current draft.** `PendingEventFinder` (work list, MCP tool `sulu_event_import_pending`) and `AutoPublisher` took the last draft row of a language, which is an old saved version for events that have saved versions; complete events then looked incomplete and were not published. They look at the current draft (version 0) now, and so does the importer when it compares an event with what the source delivers (`EventImporter`), so a saved version is no longer taken for an edit of the admin.
- **`sulu:event:fill-images` leaves events alone that already have their image.** It compared the image of an event with the template data, where saving never keeps it (the image is a column, the merged content gets it back when it is read), so every run counted all events as "filled", saved them again and gave every published event a new draft. Now an image that is no placeholder stays untouched, a placeholder (the image of a topic, a generated card) only gives way to a photo of the series or the place, and nothing is written when the event has the chosen image already. Nothing to do on your side; if you call the command by a cron job, the next run may still replace some cards with the photo of their place, after that it reports nothing.
- **Calendar API `url`** is an absolute URL (was: the path). `tags[]` takes names; numeric-only values were required before, which dropped every name.

### For developers

- `ICalGenerator` takes the `EventUrlResolver`, the `RequestStack` and the new `EventTimezoneResolver` as additional constructor arguments (`sulu_event.ical_generator` is updated). `ICalGenerator::resolveTimezone()` returns the zone the stored times are meant in: the zone of the event, of its location, `sulu_event.timezone`, the PHP default.
- `CalendarApiController` takes the `EventUrlResolver` and the `EventTimezoneResolver` as fourth and fifth constructor arguments, `EventNormalizer` the `EventStatusSelect` and the `AttendanceModeSelect` as fourth and fifth.
- New services: `sulu_event.status_select`, `sulu_event.attendance_mode_select`, `sulu_event.timezone_select`, `sulu_event.language_select`, `sulu_event.timezone_resolver`. New entity fields come with `EventDetailsTrait` (`allDay`, `timezone`, `status`, `attendanceMode`, `onlineUrl`, `registrationUrl`, `isFree`, `priceInfo`, `organizerName`, `organizerUrl`, `languages`) and `Location::getTimezone()`.
- The enums `EventStatus` and `AttendanceMode` list the allowed values; the entities store the strings and refuse unknown values (and invalid time zones and urls) with an `\InvalidArgumentException`.
- `EventRepository::findForIcal()` and `findForCalendar()` return only events with a live dimension content in the locale and fetch all their dimension contents. Both accept `categories`, `tags` (and `location` for the calendar) as filters.
- Without the `allDay` flag an event is a timed one, in the iCal export and in the calendar API.
- The calendar API sends `start`/`end` of all-day events as dates (end exclusive) and the others with the offset of the time zone of the event; `extendedProps` has `status` and `attendanceMode`.
- iCal: `STATUS:CANCELLED` for cancelled and `STATUS:TENTATIVE` for postponed events; the link to take part is the `LOCATION` of an online event without a place.
