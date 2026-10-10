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
