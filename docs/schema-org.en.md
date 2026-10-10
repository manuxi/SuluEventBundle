# Structured data (schema.org) and times in templates

[← Back to README](../README.md)

The bundle provides the schema.org `Event` of an event as a plain **array**. It does not print a `<script>` tag: a project puts the array into its own structured data graph or encodes it as JSON-LD.

## `sulu_event_schema(event)`

`event` is the content of the event as it is in the event detail template (the `EventDimensionContent`). Anything else gives an empty array.

```twig
{% set schema = sulu_event_schema(event) %}
```

| Key | Content |
|---|---|
| `@type`, `name`, `description`, `url` | `Event`, title, introduction without HTML, absolute url of the page (webspace and locale of the event) |
| `startDate`, `endDate` | Times with the offset of the time zone of the event (`2027-07-01T10:00:00-07:00`); for **all-day** events dates only (`2027-03-02`, the end is the last day). No `endDate` if the end is not after the start |
| `eventStatus` | `EventScheduled`, `EventPostponed`, `EventRescheduled`, `EventCancelled` or `EventMovedOnline` (full `https://schema.org/…` urls) |
| `eventAttendanceMode` | `OfflineEventAttendanceMode`, `OnlineEventAttendanceMode` or `MixedEventAttendanceMode` |
| `location` | On site: `Place` with `PostalAddress` and `GeoCoordinates` (as far as known). Online: `VirtualLocation` with the online link. Mixed: both as a list. No location if there is none |
| `organizer` | `Organization` with name and url |
| `offers` | An `Offer` with the registration link as `url` (and the price text as `description`). **No price is invented**: without a registration link there is no offer, and a free event has no price text |
| `isAccessibleForFree` | `true`/`false` if the event says so, otherwise missing |
| `inLanguage` | Language code, or a list for several languages |
| `image` | Absolute url of the picture of the event (or of the excerpt image) |

Only what is known is included; empty values are left out.

### As JSON-LD

```twig
{% set schema = sulu_event_schema(event) %}
{% if schema %}
    <script type="application/ld+json">{{ {'@context': 'https://schema.org'}|merge(schema)|json_encode(constant('JSON_UNESCAPED_SLASHES') b-or constant('JSON_UNESCAPED_UNICODE') b-or constant('JSON_HEX_TAG') b-or constant('JSON_HEX_AMP'))|raw }}</script>
{% endif %}
```

`JSON_HEX_TAG` keeps a `</script>` in a title from ending the block. The example template `event_detail_example.html.twig` does exactly this.

### In your own graph

If the page already builds a structured data graph (organization, website, breadcrumbs, …), add the array to it instead of printing a second block. With a `schema_add`-like helper that is `schema_add(sulu_event_schema(event))`.

## `sulu_event_datetime(event, 'start'|'end')`

Start or end as a `DateTimeImmutable` **in the time zone of the event** (see [Participation, time zone and tickets](online-events.en.md)), `null` if there is none. `sulu_event_timezone(event)` gives the `DateTimeZone`.

```twig
{% set start = sulu_event_datetime(event, 'start') %}
{% set zone = sulu_event_timezone(event) %}

<time datetime="{{ start|date('c', false) }}">
    {{ start|format_datetime('full', 'short', locale=app.request.locale, timezone=zone.name) }}
</time>
```

Twig's `date` filter converts to the time zone of the server unless you pass `false` as the second argument (`|date('c', false)`); `format_datetime` needs the zone as `timezone=`. Otherwise "10:00 in Las Vegas" would be shown as the time of the server.
