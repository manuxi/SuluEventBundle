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

`url` and `locationId` are required in the default template. Create the location first with `sulu_location_create` or take an id from `sulu_location_list`.

Publishing is not part of the tools. Publish in the admin, or with the [bulk actions](https://github.com/manuxi/SuluBulkActionsBundle).
