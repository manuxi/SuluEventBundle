# Upgrade

## 3.6.0 (planned)

The final version number is decided at release time. This section describes the iCal and calendar fixes; it will be extended by the following changes of the release.

No database changes.

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
- **Calendar API works again.** `/api/events/calendar/{locale}` answered 500 ("has no container set"): `services-calendar.yaml` redefined the controller without the container and overrode the complete definition in `controller.yaml`. The duplicate is removed. If you overrode the service `Manuxi\SuluEventBundle\Controller\Website\CalendarApiController` yourself, it needs the container (`calls: setContainer`) and the webspace manager as fourth argument.
- **Calendar API `url`** is an absolute URL (was: the path). `tags[]` takes names; numeric-only values were required before, which dropped every name.

### For developers

- `ICalGenerator` takes the `WebspaceManagerInterface`, the `RequestStack` and the timezone as additional constructor arguments (`sulu_event.ical_generator` is updated). `ICalGenerator::resolveTimezone()` returns the zone the stored times are meant in.
- `CalendarApiController` takes the `WebspaceManagerInterface` as fourth constructor argument.
- `EventRepository::findForIcal()` and `findForCalendar()` return only events with a live dimension content in the locale and fetch all their dimension contents. Both accept `categories`, `tags` (and `location` for the calendar) as filters.
- All-day events are still guessed (starts at midnight, no end or end at 23:59:59). A field for it is planned.
