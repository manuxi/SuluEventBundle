# Event import

[← Back to README](../README.md)

The import reads events from calendar feeds (iCal) and from web pages with structured data (schema.org / JSON-LD) and creates them as **drafts**. It never publishes anything: an editor looks at every event, writes the text and publishes it. It is switched off until you configure it.

```bash
bin/adminconsole sulu:event:import --dry-run   # show what would happen
bin/adminconsole sulu:event:import             # do it
```

## What is imported (and what is not)

Only **facts** are taken over: title, date and time, time zone, place, status, links for tickets and the stream, price, organizer (organizations only), language. The **description** of a source is *not* used as the text of the event: the text of a page or feed belongs to its author. It is kept with the import entry as a hint for whoever writes the text. Names and addresses of persons (organizers, attendees in calendar feeds) are dropped.

Each event is a draft in the locale of the source (`locale`). It gets the type, categories and tags configured for the source, a url made of title and year (`/events/example-conf-2027`, a number is appended if it exists), and the location (found or created, see below).

## Setup

1. Update the database, see [UPGRADE.md](../UPGRADE.md) (tables `app_event_import` and `app_event_import_run`).
2. Create a Sulu user for the import (for example `event-import`). The imported events show this user as creator.
3. Configure the sources:

```yaml
# config/packages/sulu_event.yaml
sulu_event:
    import:
        enabled: true
        user: 'event-import'
        user_agent: 'MyProject-EventImport (+https://example.com/contact)'
        auto_update_published_dates: false
        sources:
            ical:
                -   key: 'dev-calendar'
                    url: 'https://example.com/calendar/events.ics'
                    type: 'meetup'
                    categories: ['web-php']
                    tags: ['Symfony']
                    locale: 'en'
            jsonld:
                -   key: 'example-conf'
                    url: 'https://conf.example.com/'
                    type: 'conference'
                    series: 'example-conf'
                    timezone: 'Europe/Berlin'
```

| Option | Meaning | Default |
|---|---|---|
| `enabled` | Switches the import on | `false` |
| `user` | User name of the Sulu user who is creator and changer of imported events. **Required** when enabled | – |
| `user_agent` | How the import introduces itself. Put a contact in it | `SuluEventBundle-Import (+https://github.com/manuxi/SuluEventBundle)` |
| `timeout` | Seconds to wait for a source | `15` |
| `auto_update_published_dates` | A change of date, time or status of a **published** event goes live by itself, if nothing else is waiting in its draft | `false` |
| `url_prefix` | Path the urls of imported events start with | `/events` |
| `webspace` | Webspace of imported events | default webspace |

Options of a source (`ical` and `jsonld` have the same):

| Option | Meaning |
|---|---|
| `key` | Short name (`a-z`, digits, `-`, `_`), used in reports. Unique per kind |
| `url` | Address of the feed or page (http/https) |
| `type` | Event type (key from `sulu_event.types`) |
| `categories` | Keys of Sulu categories; unknown keys are reported |
| `tags` | Tag names; missing tags are created |
| `locale` | Language of title and texts of the source (default `en`). Use a locale your webspace has |
| `timezone` | Time zone for events whose source gives none. Optional |
| `series` | Name of an event series, see "Several sources" |
| `priority` | Which source wins, see "Several sources". Default 100 for pages, 50 for calendars |
| `languages` | Language codes the events are held in |

Mistakes in the configuration are reported when the cache is built, with the path of the option.

## Sources

**iCal** (`ical`): any `.ics` feed, for example calendars of a conference or a provider. Recurring events are expanded for the next 12 months (at most 60 dates per event). Times with a time zone keep it; times in UTC get the zone of the place (from the coordinates) or the configured `timezone`; all-day events are all-day. Cancelled events are marked cancelled.

**Pages with structured data** (`jsonld`): reads `<script type="application/ld+json">` with `Event` or one of its subtypes (also inside `@graph` and lists). The time zone comes from the offset together with the country or coordinates of the place, or from `timezone`.

Own sources: implement `Manuxi\SuluEventBundle\Import\EventSourceInterface` (`getKey()`, `getPriority()`, `fetch()` returning `EventCandidate` objects) and tag the service `sulu_event.import_source` (autoconfigured).

## What happens in a run

| Result | Meaning |
|---|---|
| New | A draft was created (cancelled events that are not known yet are not created) |
| Changed | The draft of a known event was updated. Texts, categories and tags are only **filled in** when empty, never replaced |
| Unchanged | Nothing to do |
| Locked | A field was changed in the admin after the last import. It is **not overwritten**, the run reports the difference |
| Vanished | A future event is not in the source any more. It stays, but is marked; it is never deleted |
| Rejected | The event was deleted in the admin. It is not created again. Restoring it from the trash lifts this |
| Errors | A source or an event failed. The exit code of the command is 1 |
| New locations | A place was created (name, time zone; "guessed" if the country has several zones). Please have a look |

Changes always go to the **draft**. If the event is published, the live version stays as it is until an editor publishes the draft. With `auto_update_published_dates: true` a change that only concerns date, time, time zone or status goes live by itself, but only if the draft has nothing else waiting.

**Lock detection**: the import remembers what it wrote. If the value in the draft differs from that, someone changed it in the admin; the field is locked and the source no longer overwrites it.

**Locations** are found by name and city, or by coordinates (closer than 300 m); otherwise a new one is created with the time zone of its country or coordinates.

**Several sources for one event**: if two sources deliver the same event (a conference page and a calendar), give both the same `series` (for example `example-conf`). With the year of the start it makes the key `example-conf-2027`; the second source then links to the existing event instead of creating another. The source with the higher `priority` maintains the event. Use `series` only for sources that deliver *one event per year*; events of one source are never joined.

The result of each run is stored (table `app_event_import_run`), the command prints it as a table:

```
 New   Changed   Unchanged   Locked   Vanished   Rejected   Errors   New locations
```

## Running it regularly

Once a day is enough. Example for cron:

```
0 5 * * * cd /var/www/project && bin/adminconsole sulu:event:import
```

`--source=ical.dev-calendar` runs a single source, `--limit=20` imports at most 20 events per source.

## Politeness, terms of use and privacy

- The import identifies itself with `user_agent`, fetches at most one page per second and host, caches `ETag` / `Last-Modified` (an unchanged page costs next to nothing) and obeys `robots.txt` (the answer is kept for 24 hours; if it cannot be read, the site is left alone).
- **Check the terms of use of every source** before you add it. Some providers do not allow automated retrieval or commercial use of their data; for these, enter events by hand or ask for permission. The import only reads what a site publishes for this purpose (feeds, structured data).
- Do not add feeds that contain personal data. Names and addresses of persons are not taken over, but you stay responsible for what your sources contain. The imported text is yours to write, not the source's.
