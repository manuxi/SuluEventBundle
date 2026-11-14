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
| `series_images` | A photo of a series: series key (`series` of a source) => id or file name. The events of the series take it when they have nothing better | – |
| `location_aliases` | Other spellings of a place or a city and the one that leads, for example `Brüssel: Brussels`, `Köln: Cologne`. The import finds and creates the place under the leading name, so a source that writes the city in German does not make a second place (a city with a state or country, "San Francisco CA", is the same city anyway) | – |
| `filters` | The sieve: which candidates become events at all, see "The sieve" | no rules |
| `source_filters` | Rules of single sources by source key (also for sources of your project) | – |
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
| `filters` | Rules of the sieve for this source, replace the global ones rule by rule, see "The sieve" |
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

**Which image an event gets**, from the best to the worst: (1) a photo of the series (`series_images`), (2) the photo of the place, if the place has a real photo (the image of all places, `location_image`, is only a placeholder), (3) the placeholder of its first topic (`category_images`). Images of the sources are never copied, an image chosen by the editors is never replaced; only a placeholder gives way to a better image (for example a photo of the place that comes later) - `sulu:event:fill-images` does that for existing events.

## In the admin

- **Tab "Source"** of an event (only for events from the import): the sources, the state, the last comparison, the page of the event at the source, the description of the source (a hint for whoever writes the text, not published), the last error, and the protected fields. The switch **Release all protected fields** gives them free: after saving, the next import writes the values of the source into them again.
- **List of events**: the column **Source** (empty for events entered by hand) and the filters **State of the import** (active / no longer in the source) and **Publication state** in the filter of the list. With "imported" and "not published" you get the list of events that wait for approval.
- **Settings > Import**: the last run (time, numbers, errors, new locations), read only.

### The button "Import now" (optional)

With `sulu_event.import.admin_button: true` the tab **Settings > Import** has a button **Import now**: it starts the import of all sources in the background (after the answer is sent to the browser; it takes a minute or two), then fills the images that are missing (`sulu:event:fill-images`) and shows the result in the same tab. While a run is going the button is locked, **the button shows the progress** ("Imported: 120/345", it stays for an hour after the run) and the tab says "Running since … , source 2 of 20"; a run that is not done after 30 minutes counts as dead. A failed run is shown with its error message. What the button does **not** do: the report mail, coordinates for the map and other jobs of your project (your daily run does that).

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
| Rejected | The event was deleted in the admin or rejected with `sulu_event_import_reject`. It is not created again. Restoring it from the trash lifts this |
| Filtered | The rules of the project (the sieve) do not let the candidate pass: no event is created, an existing one is not touched, see "The sieve" |
| Errors | A source or an event failed. The exit code of the command is 1 |
| New locations | A place was created (name, time zone; "guessed" if the country has several zones). Please have a look |

Changes always go to the **draft**. If the event is published, the live version stays as it is until an editor publishes the draft. With `auto_update_published_dates: true` a change that only concerns date, time, time zone or status goes live by itself, but only if the draft has nothing else waiting.

**Lock detection**: the import remembers what it wrote. If the value in the draft differs from that, someone changed it in the admin; the field is locked and the source no longer overwrites it.

**Locations** are found by name and city, or by coordinates (closer than 300 m); otherwise a new one is created with the time zone of its country or coordinates.

**Several sources for one event**: if two sources deliver the same event (a conference page and a calendar), give both the same `series` (for example `example-conf`). With the year of the start it makes the key `example-conf-2027`; the second source then links to the existing event instead of creating another. The source with the higher `priority` maintains the event. Use `series` only for sources that deliver *one event per year*; events of one source are never joined.

**The same event twice in one delivery** (a list names a conference under every topic it belongs to) is one candidate: the first stays, categories and tags of the others are added. **A source that changes the id of an event** (a page that gave no id and has an address now) keeps its event: if a source delivers exactly one candidate for a key (`series` and year), has exactly one active entry with that key, and the id of that entry is no longer delivered, the entry follows the event to the new id (the report says so in `warnings`) instead of creating a second event and marking the first one "vanished".

The result of each run is stored (table `app_event_import_run`), the command prints it as a table:

```
 New   Changed   Unchanged   Locked   Vanished   Rejected   Filtered   Errors   New locations
```

## The sieve

Sources deliver more than you want: events in regions you do not cover, webinars, far-away dates. The **sieve** decides before anything is created. The bundle is neutral and brings **no lists of countries or regions**; every rule is empty until your project sets it, so by default everything passes.

```yaml
# config/packages/sulu_event.yaml
sulu_event:
    import:
        filters:
            countries:
                include: ['DE', 'AT', 'CH', 'NL']   # ISO 3166-1 alpha-2; only these pass
                exclude: []                         # always out
            unknown_country: allow                  # allow (default) | deny: a place without a country
            online: allow                           # allow (default) | deny: online only, without a place
            types: []                               # allowed event types; empty = all
            topics: []                              # category keys, one must match; empty = all
            title_exclude: ['^Webinar', 'free trial']   # regular expressions, case does not matter
            host_exclude: ['tickets.spam.example']  # hosts of the event link, subdomains included
            max_months_ahead: 18                    # later events are not imported yet; null = no limit
            series_bypass: [countries, unknown_country, online]   # see below (this is the default)
            tags_allowed: []                        # not a rule, see "Allowed tags"
        # rules of single sources, any source (also those of your project): they replace the global rules rule by rule
        source_filters:
            my-list:
                max_months_ahead: 12
        sources:
            jsonld:
                - key: conf
                  url: https://conf.example.com/
                  filters:
                      online: deny
```

- **Country**: the country code of the place of the candidate. With `include` only these pass, `exclude` is always out. A candidate **with** a place but **without** a country is decided by `unknown_country`; an event that takes place **online only** (no place) is judged by `online`, not by its country.
- **Series**: a candidate that has a series (the source names one, or gives the key that joins it with the same event of other sources, see "Several sources for one event") bypasses the rules in `series_bypass`. Default: `countries`, `unknown_country`, `online`; also possible are `topics`, `types` and `max_months_ahead`. Whoever curates a series (a flagship conference in another region) should not lose it to a regional rule. `title_exclude` and `host_exclude` **always** apply.
- **Per source**: `filters` of a source (or `source_filters.<key>` for any source) replace the global rules rule by rule; what is not set stays as it is globally. `countries.include` and `countries.exclude` count as one rule each.
- The rules are **checked when the container is built**: a wrong country code, an invalid pattern or an unknown type stops with a clear message.

**What happens to a candidate that does not pass:**

- A **new** candidate gets **no event**. Its entry in `app_event_import` gets the state `filtered` and the reason in `state_reason` ("The country US is not one of the allowed countries."). If the rules change and it passes, the next run imports it like any other candidate.
- An event that **exists** is **not touched** (no change, no deletion). Its entry becomes `filtered`; what happens to the event is decided by `sulu:event:import:prune` (below). If it passes again, the entry is `active` again and the changes of the source arrive.
- The run counts them (column **Filtered**, also stored with the run), but does not keep the list. To see what a rule does, ask for it: `bin/adminconsole sulu:event:import --dry-run --explain` lists source, title, country and reason of every candidate that was left out.
- `sulu_event_import_pending` does not show events that are `filtered` or `rejected`. In the admin, the tab "Source" shows state and reason, and the list of events can be filtered by "left out by the rules" and "rejected".

### Allowed tags

`tags_allowed` (global or per source) is a fixed list of the tags an event may get; the comparison ignores case. When it is set, the import and the MCP tools `sulu_event_import_upsert` and `sulu_event_update` take over only these tags and name the others in the report (`warnings`), so the tags stay manageable for visitors. Empty (default) = all tags.

### Rejecting and cleaning up

- **`sulu_event_import_reject`** (MCP tool, [docs/mcp.en.md](mcp.en.md)): says that an event is not wanted, with a reason. The event is not deleted; the import never creates or changes it again (state `rejected`).
- **`sulu:event:import:prune [--dry-run] [--state=filtered|rejected] [--force]`**: moves events whose entry is `filtered` or `rejected` and that were **never published** to the trash of Sulu (like "delete" in the admin, they can be restored there). Events that were published, or that another source still maintains, are only listed ("please decide in the admin"). Without `--dry-run` the command asks first; with `--no-interaction` it stops unless `--force` is given. The entry keeps its state, so a `filtered` candidate is imported again if it passes later.

## Publish automatically

Imported events stay drafts until someone releases them. If you do not want to do that by hand for certain sources, switch on the automatic publishing: an event is published as soon as it is **complete**. Everything else stays a draft. Default: off.

```yaml
# config/packages/sulu_event.yaml
sulu_event:
    import:
        auto_publish:
            enabled: true
            when:                       # OR: one matching condition is enough
                series: true            # the event belongs to a series
                sources: [confs-tech]   # or comes from this source
                types: [keynote]        # or has this type
            require:
                locales: [de, en]       # per language: title, subtitle, summary, text not empty
                category: true          # at least one category (default: true)
                image: true             # an image (default: true)
                place_or_online: true   # a location or an online link (default: true)
            republish_changes: false    # default: false
```

- **`when`** needs at least one condition, otherwise the configuration is refused (no event would qualify). "Series" means: the source or the import knows a series or a match key (as with the sieve). Sources are named as in the reports (`ical.my-calendar`, `jsonld.my-page`, `confs-tech`), types are the keys of `sulu_event.types`.
- **`require.locales`**: these languages must be complete and are published. Empty = the languages the event has. Title, subtitle, summary and text must not be empty (HTML without content counts as empty). Category, image and location/online link apply to the event as a whole; an online link is the online URL.
- **Never touched**: events that are over already (end or start in the past), events whose import entry is `filtered`, `rejected` or `vanished`, a language an editor took back by hand (state "unpublished" after it had been live), and languages in review. Languages that are published already and have no new changes are skipped.
- **`republish_changes: true`**: a published event with a new draft (the import changed the date, place or price, for example) is published again if it still meets the rules **and** none of its fields is locked by hand (tab "Source"). Otherwise the new draft waits until an editor releases it.

### The command

```
bin/adminconsole sulu:event:import:publish-ready [--dry-run] [--limit=N] [-v]
```

It publishes like the "Publish" button of the admin (workflow, as the import user, with search, activity log and cache) and prints three numbers: **published**, **waiting** (with what each event still lacks, for example `subtitle:en`, `text:de`, `locale:en`, `categories`, `image`, `place`) and **skipped** (with the reason under `-v`). `--dry-run` changes nothing; look there first before you put the command in cron. The result is stored; `sulu_event_import_report` and the tab "Import" of the settings ("Last automatic publishing") show it.

Cron example, after the import and the editorial run:

```
0 9 * * * cd /var/www/project && bin/adminconsole sulu:event:import:publish-ready
```

`sulu_event_import_pending` names under `autoPublish` what an event still lacks for it, and also lists events that only lack that (the subtitle, for example), so the editorial run can complete them on purpose.

## Running it regularly

Once a day is enough. Example for cron:

```
0 5 * * * cd /var/www/project && bin/adminconsole sulu:event:import
```

`--source=ical.dev-calendar` runs a single source, `--limit=20` imports at most 20 events per source, `--explain` lists the candidates the sieve left out with the reason (best with `--dry-run`).

## Politeness, terms of use and privacy

- The import identifies itself with `user_agent`, fetches at most one page per second and host, caches `ETag` / `Last-Modified` (an unchanged page costs next to nothing) and obeys `robots.txt` (the answer is kept for 24 hours; if it cannot be read, the site is left alone).
- **Check the terms of use of every source** before you add it. Some providers do not allow automated retrieval or commercial use of their data; for these, enter events by hand or ask for permission. The import only reads what a site publishes for this purpose (feeds, structured data).
- Do not add feeds that contain personal data. Names and addresses of persons are not taken over, but you stay responsible for what your sources contain. The imported text is yours to write, not the source's.
