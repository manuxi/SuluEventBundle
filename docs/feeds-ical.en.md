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
/events/{id}/download.{locale}.ics
```

**Example:**
```
https://example.com/events/42/download.en.ics
```

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
<a href="{{ path('sulu_event.ical_single', {id: event.id, _locale: app.request.locale}) }}" 
   download="event-{{ event.id }}.ics"
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

Each iCal event includes:
- **UID**: Unique event identifier
- **DTSTAMP**: Timestamp when iCal was generated
- **DTSTART**: Event start date and time
- **DTEND**: Event end date and time (if available)
- **SUMMARY**: Event title
- **DESCRIPTION**: Event summary/description
- **LOCATION**: Venue name (if assigned)
- **URL**: Link to event detail page

### Filtering Calendar Feed

You can filter the calendar feed using query parameters:

```
/events/calendar.en.ics?categories[]=1&categories[]=2&tags[]=important
```

**Available filters:**
- `categories[]`: Filter by category IDs
- `tags[]`: Filter by tag names

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