# RSS/Atom Feeds & iCal Export

## Overview

The SuluEventBundle provides multiple ways for users to subscribe to your events:
- **RSS/Atom Feeds**: Keep users updated about new events
- **iCal Single Event**: Download individual events to calendar applications
- **iCal Calendar Feed**: Subscribe to all events via webcal://

## RSS/Atom Feed

### Available URLs

The feed is available in two formats:

```
RSS 2.0:  /events/feed.{locale}.rss
Atom 1.0: /events/feed.{locale}.atom
```

**Examples:**
```
https://example.com/events/feed.en.rss
https://example.com/events/feed.de.atom
```

### Feed Content

Each feed includes:
- Event title
- Event link (to detail page)
- Event description (summary field)
- Publication date (if published)
- Unique event ID (GUID)

### Usage in Templates

Add feed discovery links to your HTML `<head>`:

```html
<link rel="alternate" type="application/rss+xml" 
      title="Events RSS Feed" 
      href="{{ path('sulu_event.feed', {_format: 'rss', _locale: app.request.locale}) }}" />

<link rel="alternate" type="application/atom+xml" 
      title="Events Atom Feed" 
      href="{{ path('sulu_event.feed', {_format: 'atom', _locale: app.request.locale}) }}" />
```

Or provide direct links:

```html
<a href="{{ path('sulu_event.feed', {_format: 'rss', _locale: app.request.locale}) }}">
    <i class="fa fa-rss"></i> Subscribe to RSS Feed
</a>
```

### Feed Validation

You can validate your feeds using:
- **RSS**: https://validator.w3.org/feed/
- **Atom**: https://validator.w3.org/feed/

## iCal Export

### Single Event Download

Download an individual event as `.ics` file:

```
/events/{uuid}/download.{locale}.ics
```

**Example:**
```
https://example.com/events/019bf796-423c-7e1f-969c-5c4ece5e9b73/download.en.ics
```

`{uuid}` is the UUID of the event (no longer a number as in older versions; the route never worked that way). In the detail template `event` is the content of the event, so the UUID is `event.resource.id`. Events that are not published in the locale return 404.

### Calendar Feed (Subscription)

Subscribe to all events using a calendar application:

```
/events/calendar.{locale}.ics
```

**Examples:**
```
https://example.com/events/calendar.en.ics
webcal://example.com/events/calendar.de.ics
```

**Note:** Use `webcal://` protocol for direct calendar subscriptions.

### Usage in Templates

#### Single Event Download Button

```html
<a href="{{ path('sulu_event.ical_single', {id: event.resource.id, _locale: app.request.locale}) }}" 
   download="event-{{ event.resource.id }}.ics"
   class="btn btn-primary">
    <i class="fa fa-calendar-plus"></i> Add to Calendar
</a>
```

#### Calendar Subscription Link

```html
<a href="webcal://{{ app.request.host }}{{ path('sulu_event.ical_feed', {_locale: app.request.locale}) }}" 
   class="btn btn-outline">
    <i class="fa fa-calendar"></i> Subscribe to Calendar
</a>
```

Or provide both protocols:

```html
<div class="calendar-subscription">
    <h3>Subscribe to Calendar</h3>
    <p>Click one of the following links to subscribe:</p>
    <ul>
        <li>
            <a href="webcal://{{ app.request.host }}{{ path('sulu_event.ical_feed', {_locale: app.request.locale}) }}">
                Subscribe via webcal:// (recommended)
            </a>
        </li>
        <li>
            <a href="{{ path('sulu_event.ical_feed', {_locale: app.request.locale}) }}" download="events.ics">
                Download .ics file
            </a>
        </li>
    </ul>
</div>
```

### iCal Content

The file is built with [sabre/vobject](https://sabre.io/vobject/); special characters are escaped and long lines are folded after 75 octets. Each iCal event includes:
- **UID**: Stable identifier `<uuid>@<host>`. The host is the one of the event's website (webspace and locale), not the one the feed was requested with, so a subscription does not create duplicates when the site is fetched under another name.
- **DTSTAMP** and **LAST-MODIFIED**: Time of the last change of the event
- **DTSTART**: Event start, written as UTC (`…Z`), see timezone below
- **DTEND**: Event end (if available and after the start)
- **SUMMARY**: Event title
- **DESCRIPTION**: Event summary/description (without HTML)
- **LOCATION**: Venue name (if assigned)
- **URL**: Absolute link to the event detail page, built with the website URL of Sulu (webspace and locale of the event). If there is no page or no URL for the locale, the line is left out.
- **CATEGORIES**: The categories of the event (excerpt tab) with their names in the locale of the feed; if a category has no name there, the one in its default locale is used.

#### Timezone

Times entered in the admin are stored as a time of day without a timezone ("10:00"). The export reads them in the timezone of the application and converts them to UTC, so an appointment at 10:00 in Berlin appears as `DTSTART:…T080000Z` in summer and `…T090000Z` in winter. Calendar applications therefore show it correctly in the user's own timezone, and no `VTIMEZONE` component is needed.

The timezone of the application is the PHP default timezone (`date.timezone`). It can be set separately for the events:

```yaml
# config/packages/sulu_event.yaml
sulu_event:
    timezone: 'Europe/Berlin'   # IANA name; the PHP default timezone applies if omitted
```

#### All-day events

Until there is a field of its own for it, an event counts as all-day if it starts at 00:00:00 and either has no end or ends at 23:59:59. It is then written as a date without time (`DTSTART;VALUE=DATE:…`); `DTEND` is, as iCal intends, the day after the last day.

### Filtering Calendar Feed

You can filter the calendar feed using query parameters:

```
/events/calendar.en.ics?categories[]=1&categories[]=2&tags[]=important
```

**Available filters:**
- `categories[]`: Filter by category IDs
- `tags[]`: Filter by tag names

Several values of one filter are combined with OR, categories and tags together with AND. A subscription for a single topic is therefore a link like:

```
webcal://example.com/events/calendar.en.ics?categories[]=12
```

As with Smart Content and the calendar API, only events that are published in the locale are included. A filter that only contains invalid values (e.g. `categories[]=abc`) yields an empty calendar instead of all events.

**Example:**
```html
<a href="{{ path('sulu_event.ical_feed', {
    _locale: app.request.locale,
    categories: [1, 5],
    tags: ['conference', 'workshop']
}) }}">
    Subscribe to Conferences & Workshops
</a>
```

## Calendar Application Support

### Tested Applications

The iCal format is compatible with:
- **Google Calendar**: Import or subscribe via URL
- **Apple Calendar**: Subscribe via webcal://
- **Microsoft Outlook**: Import .ics files
- **Thunderbird**: Subscribe via URL
- **Mobile Devices**: iOS Calendar, Android Calendar apps



- [Calendar Integration](calendar.en.md) - FullCalendar.js integration
- [Social Media](social-media.en.md) - Social sharing configuration
- [Recurring Events](recurring.en.md) - Repeating event patterns