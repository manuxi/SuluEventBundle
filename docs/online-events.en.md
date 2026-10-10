# Participation, time zone and tickets

[← Back to README](../README.md)

Besides date and place an event knows how people take part (on site, online or both), in which time zone its times are meant, whether it takes place at all, where to get tickets and who organizes it. All of this is optional; events without these details behave as before.

## In the admin

- **Date & Location** (always visible when the date is switched on): start, end and **All day**, then **Participation**, speaker and location.
- **Participation & tickets**: a section that is closed until you switch on **Participation & tickets** in the section "Other".

| Field | Meaning | Default |
|---|---|---|
| All day | Only the date counts. Start becomes 00:00:00, end 23:59:59 | off |
| Participation | On site, online or on site and online | on site |
| Location | Required unless the participation is "Online" (then the field is hidden) | – |
| Status | Takes place, postponed, rescheduled, cancelled, moved online | takes place |
| Time zone | IANA name of the zone of the start and end, see below | automatic |
| Languages | Language(s) the event is held in | – |
| Tickets / registration | Link to tickets or registration (http/https) | – |
| Link to take part online | Stream or meeting link, only for "Online" and "On site and online" | – |
| Free of charge | Switch for free events | off |
| Price | Short text per language, e.g. "from 99 EUR". Hidden for free events | – |
| Organizer, website of the organizer | | – |

The location is made optional for online events with the [SuluBlockValidationBundle](https://github.com/manuxi/SuluBlockValidationBundle) (a dependency of this bundle; it has to be registered in `config/bundles.php`: `Manuxi\SuluBlockValidationBundle\SuluBlockValidationBundle::class => ['all' => true]`). The check only happens in the admin form: the MCP tools, imports and the console do not demand a location.

## Time zone

The start and end of an event are stored as entered, as a time of day without a zone ("10:00"). The zone they are meant in is, in this order:

1. the **time zone of the event** (section "Participation & tickets"),
2. the **time zone of its location** (location form),
3. the configured zone `sulu_event.timezone`,
4. the PHP default timezone.

Leave "Automatic" selected unless the event takes place in another zone than its location, e.g. an online keynote of a company in California. For events abroad give the **location** its zone once (e.g. "America/Los_Angeles" for the convention center in Las Vegas); every event there then works without further input.

Changing the zone later does not shift the stored time of day: "10:00" stays 10:00, it is just read in another zone. The iCal files and the calendar API use this zone to give the right point in time ([iCal](feeds-ical.en.md), [calendar](calendar.en.md)).

## All-day events

With **All day** the times of day are ignored: the start is set to 00:00:00 and the end, if there is one, to 23:59:59. In the iCal export they are dates (`DTSTART;VALUE=DATE`), in the calendar `allDay` events.

Until this field existed the bundle guessed "all day" from the times. That guess is gone: an event at midnight is only all-day if the box is checked. See [UPGRADE.md](../UPGRADE.md) for the SQL to carry existing events over.

## Status

Cancelled and postponed events stay in the lists and calendars, marked accordingly:

| Status | iCal | Calendar API |
|---|---|---|
| Takes place, rescheduled, moved online | – | `extendedProps.status` |
| Cancelled | `STATUS:CANCELLED` | `extendedProps.status` |
| Postponed | `STATUS:TENTATIVE` | `extendedProps.status` |

## Online events

For "Online" the location is not required. In the iCal file the link to take part is the location of the event if it has no place. The calendar API sends `extendedProps.attendanceMode`, so a front end can mark online events.

## For templates

All fields are available on the content of the event: `event.allDay`, `event.timezone`, `event.status`, `event.attendanceMode`, `event.onlineUrl`, `event.registrationUrl`, `event.isFree`, `event.priceInfo`, `event.organizerName`, `event.organizerUrl`, `event.languages`. The time zone of an event is the service `sulu_event.timezone_resolver` (`resolve()` gives the `DateTimeZone`, `toInstant()` turns a stored time of day into a `DateTimeImmutable` in that zone).

## MCP

`sulu_event_create` and `sulu_event_update` take the same keys in `content` (`allDay`, `timezone`, `status`, `attendanceMode`, `onlineUrl`, `registrationUrl`, `isFree`, `priceInfo`, `organizerName`, `organizerUrl`, `languages`), see [MCP tools](mcp.en.md). Invalid values (an unknown status, an unknown time zone, a url without http/https) are rejected with a message.
