# Upgrade

## 3.6.0 (planned)

The final version number is decided at release time. This section describes the iCal and calendar fixes and the new event fields (participation, time zone, status, tickets).

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

### Filters, filter options, structured data (B2b)

- **Calendar API and iCal feed** accept `types[]`, `attendanceMode[]`, `country[]` and `free=1` as well; lists may be arrays or comma separated (`categories=12,15` works now, a scalar `categories=12` no longer answers 400).
- **Smart content** filters by way to take part, country and free of charge: from the query string (`?attendanceMode=online,mixed&country=DE&free=1`, names changeable with `attendance_mode_parameter`, `country_parameter`, `free_parameter`) and as fixed filters in the "types" list of the field in the admin. The constructor of `EventSmartContentProvider` takes the `RequestStack` as sixth argument.
- **`sulu_event_filter_options(locale)`** (Twig) lists topics, types, countries, ways to take part and free events with the number of upcoming published events; cached in `cache.app.taggable` (if it exists) and cleared when an event changes. New option `sulu_event.category_root` (key of a root category) limits the topics to its descendants.
- **`sulu_event_schema(event)`** returns the schema.org `Event` as an array, `sulu_event_datetime(event, 'start'|'end')` and `sulu_event_timezone(event)` the times in the zone of the event, see [docs/schema-org.en.md](docs/schema-org.en.md). Twig's `date` filter converts to the zone of the server: use `|date('c', false)`.
- **Example template** `event_detail_example.html.twig`: status notice, online marker, local time, ticket and stream link, organizer, price, JSON-LD; the calls of `event_og_tags`, `event_twitter_tags`, `event_social_shares` pass `event.resource` and the locale now (the template did not compile before). New translations in `messages.de/en.yaml` (`sulu_event.display.*`, `sulu_event.add_to_calendar`).
- **Sitemap**: new option `sulu_event.sitemap.hide_past_after_days` (default `null`: nothing changes) leaves out events that ended more than that many days ago.
- New services: `sulu_event.url_resolver` (`EventUrlResolver`, used by iCal, calendar API and schema.org), `sulu_event.filter_options`, `sulu_event.schema_builder`. `EventTypeSelect::getTypeName()` takes an optional locale.

### New default event types

`meetup`, `trade_fair`, `keynote`, `convention` and `hackathon` (with translations). Projects that define their own `sulu_event.types` replace the defaults completely and add the ones they want, with a name (translation key) and a colour.

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
