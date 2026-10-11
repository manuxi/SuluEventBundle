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
| `category_images` | Images of your own: category key => file name or id of a media, see "Images" | – |
| `location_image` | Image (id or file name) for places the import creates: no source knows a photo of the place. Places without an image get it with `sulu:event:fill-images`; the editors replace it with a real photo | – |
| `location_aliases` | Other spellings of a place or a city and the one that leads, for example `Brüssel: Brussels`, `Köln: Cologne`. The import finds and creates the place under the leading name, so a source that writes the city in German does not make a second place (a city with a state or country, "San Francisco CA", is the same city anyway) | – |
| `locales` | Locales an imported event should have a text in. Each gets a draft with the title of the source and all the facts right away (also for events that exist already); while the text is missing the event is in the list of events that need work | – |

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
| `image` | Id of a media (an image of your own) every event of the source gets, see "Images" |

Mistakes in the configuration are reported when the cache is built, with the path of the option.

## Sources

**iCal** (`ical`): any `.ics` feed, for example calendars of a conference or a provider. Recurring events are expanded for the next 12 months (at most 60 dates per event). Times with a time zone keep it; times in UTC get the zone of the place (from the coordinates) or the configured `timezone`; all-day events are all-day. Cancelled events are marked cancelled.

**Pages with structured data** (`jsonld`): reads `<script type="application/ld+json">` with `Event` or one of its subtypes (also inside `@graph` and lists). The time zone comes from the offset together with the country or coordinates of the place, or from `timezone`.

Own sources: implement `Manuxi\SuluEventBundle\Import\EventSourceInterface` (`getKey()`, `getPriority()`, `fetch()` returning `EventCandidate` objects) and tag the service `sulu_event.import_source` (autoconfigured).

## Images

Imported events get **images of your own**, never the images of a source (they belong to the source). Without an image an event looks empty in lists and cards, so set them:

```yaml
sulu_event:
    import:
        category_images:          # category key => file name (or id) of a media
            web-php: event-topic-web-php.jpg
            ai: event-topic-ai.jpg
        sources:
            jsonld:
                -   key: 'example-conf'
                    url: 'https://conf.example.com/'
                    image: 130    # wins over the image of the category
```

Use **file names** rather than ids where you can: the ids of the media differ between a test platform and the live site, the names do not (the first media whose current file has the name is taken). A new event gets the image of its source, otherwise the one of its first category that is listed; it is set as the image and as the excerpt image (cards and teasers). An event that has no image yet gets one on the next run as well; an image someone chose is never replaced. A media id that does not exist is reported as a warning. "Image missing" also shows in the list of events that need work (MCP tool `sulu_event_import_pending`).

**Places** have no photo from a source. With `location_image` (file name or id) every place the import creates gets an image; the editors replace it with a real photo. **Afterwards** `php bin/adminconsole sulu:event:fill-images` (with `--dry-run` to look) fills all events and places without an image: an event takes the image of its first topic from `category_images`, a place the one of `location_image`. An image that exists is never replaced; a published event becomes a draft and is live with the image once it is published again.

## In the admin

- **Tab "Source"** of an event (only for events from the import): the sources, the state, the last comparison, the page of the event at the source, the description of the source (a hint for whoever writes the text, not published), the last error, and the protected fields. The switch **Release all protected fields** gives them free: after saving, the next import writes the values of the source into them again.
- **List of events**: the column **Source** (empty for events entered by hand) and the filters **State of the import** (active / no longer in the source) and **Publication state** in the filter of the list. With "imported" and "not published" you get the list of events that wait for approval.
- **Settings > Import**: the last run (time, numbers, errors, new locations), read only.

### The button "Import now" (optional)

With `sulu_event.import.admin_button: true` the tab **Settings > Import** has a button **Import now**: it starts the import of all sources in the background (after the answer is sent to the browser; it takes a minute or two), then fills the images that are missing (`sulu:event:fill-images`) and shows the result in the same tab. While a run is going the button is locked and the tab says "Import running"; a run that is not done after 30 minutes counts as dead. A failed run is shown with its error message. What the button does **not** do: the report mail, coordinates for the map and other jobs of your project (your daily run does that).

The button is a piece of JavaScript of the bundle and therefore **off** until it is part of the admin build, otherwise the tab breaks with "toolbar action not registered". To add it: the dependency `"sulu-event-bundle": "file:../../vendor/manuxi/sulu-event-bundle/src/Resources/js"` in `assets/admin/package.json`, the line `import 'sulu-event-bundle';` in `assets/admin/app.js`, then `npm install` and `npm run build` in `assets/admin`. The permission is "edit" of the settings. The import runs in the PHP process that sent the answer (`kernel.terminate`); with PHP-FPM (`fastcgi_finish_request`) that just works, other servers may make the browser wait for the end of the run.

## The user of the import

`sulu_event.import.user` is the user whose name appears as creator of imported events. The command needs **no role**: it writes as that user without logging in, and no permission is checked. A role matters only if somebody logs in with this user, for example to use the MCP tools (those check the permissions). Give such a user a role that may **view, add and edit** events (`sulu.events.events`), locations, tags and categories (view) and media (view) and **not** the permission "live" (publish) of events, so that everything stays a draft until an editor publishes it.

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
