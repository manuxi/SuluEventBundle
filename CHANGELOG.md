# Changelog

## Unreleased (3.6.0)

Minor release. It needs SQL for new columns and tables, and `SuluBlockValidationBundle` registered in `config/bundles.php`: follow the steps in [UPGRADE.md](UPGRADE.md) (section 3.6.0) before you clear the caches.

### Added

- Dashboard tiles for [manuxi/sulu-dashboard-bundle](https://github.com/manuxi/SuluDashboardBundle) (^1.1): the next five published events with the number of unpublished ones, and locations without coordinates or a complete address. The dashboard is only suggested; the tile services are loaded only when its interface is available. Rights as in the event admin (`sulu.events.events`, view). Texts in German and English. See `docs/dashboard.en.md`.
- PHPStan (level 6) for the dashboard classes and their tests; `manuxi/sulu-dashboard-bundle` and `phpstan/phpstan` as dev dependencies.
- Online events, time zones, tickets: way to take part (on site, online, both), time zone per event or location, all-day flag, status (cancelled, postponed ...), ticket, registration and stream links, price, organizer, languages.
- Filters by topic, tag, type, way to take part, country, free of charge and highlights in the calendar API, the iCal feed and smart content; `sulu_event_filter_options()` for a filter bar.
- schema.org `Event` as an array (`sulu_event_schema()`), times in the zone of the event; sitemap option `hide_past_after_days`.
- New event types: meetup, trade fair, keynote, convention, hackathon, launch.
- Highlights through a configurable tag (`sulu_event.featured_tag`).
- Optional event import (off by default) from iCal feeds and pages with schema.org data (`sulu:event:import`): polite HTTP client (robots.txt, cache, rate limit), duplicate detection, rule sieve, rejecting and pruning, images for events and places (`sulu:event:fill-images`), tab "Source" and "Import now" button in the admin, MCP tools for editorial work.
- Automatic publishing of complete imported events (`sulu:event:import:publish-ready`, off by default).

### Fixed

- Events that are not published answer 404 (also in 3.5.4).
- The admin list shows events that are missing in a locale as ghosts with the data of their fallback locale.
- Times in iCal files are written in the zone of the event (they were one or two hours off); absolute URLs and category names in the feed.
- Calendar API and RSS/Atom feeds answered 500; the calendar returns events that overlap the range; its rate limit works.
- The single event download (`sulu_event.ical_single`) takes the UUID of the event (it never worked).
- The import reads the current draft, not a saved version; `fill-images` leaves events alone that already have their image.

## 3.5.4

### Fixed

- Events that are not published answer 404. The route provider and the website controller fell back to the draft when an event had no live content in the requested locale, so saved but unpublished events were visible on the website without login. The admin preview still shows drafts.
