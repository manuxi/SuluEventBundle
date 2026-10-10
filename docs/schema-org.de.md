# Strukturierte Daten (schema.org) und Zeiten im Template

[← Zurück zur README](../README.de.md)

Das Bundle liefert das schema.org-`Event` eines Events als einfaches **Array**. Es gibt kein `<script>`-Tag aus: Ein Projekt hängt das Array in seinen eigenen Graphen für strukturierte Daten oder kodiert es als JSON-LD.

## `sulu_event_schema(event)`

`event` ist der Inhalt des Events, wie er im Detail-Template des Events vorliegt (der `EventDimensionContent`). Alles andere ergibt ein leeres Array.

```twig
{% set schema = sulu_event_schema(event) %}
```

| Schlüssel | Inhalt |
|---|---|
| `@type`, `name`, `description`, `url` | `Event`, Titel, Einleitung ohne HTML, absolute URL der Seite (Webspace und Sprache des Events) |
| `startDate`, `endDate` | Zeiten mit dem Offset der Zeitzone des Events (`2027-07-01T10:00:00-07:00`); bei **ganztägigen** Events nur Datum (`2027-03-02`, das Ende ist der letzte Tag). Kein `endDate`, wenn das Ende nicht nach dem Beginn liegt |
| `eventStatus` | `EventScheduled`, `EventPostponed`, `EventRescheduled`, `EventCancelled` oder `EventMovedOnline` (volle `https://schema.org/…`-URLs) |
| `eventAttendanceMode` | `OfflineEventAttendanceMode`, `OnlineEventAttendanceMode` oder `MixedEventAttendanceMode` |
| `location` | Vor Ort: `Place` mit `PostalAddress` und `GeoCoordinates` (soweit bekannt). Online: `VirtualLocation` mit dem Online-Link. Gemischt: beides als Liste. Kein `location`, wenn es keinen gibt |
| `organizer` | `Organization` mit Name und URL |
| `offers` | Ein `Offer` mit dem Anmelde-Link als `url` (und dem Preistext als `description`). **Es wird kein Preis erfunden**: Ohne Anmelde-Link gibt es kein Angebot, und ein kostenloses Event hat keinen Preistext |
| `isAccessibleForFree` | `true`/`false`, wenn das Event es angibt, sonst nicht vorhanden |
| `inLanguage` | Sprachcode, bei mehreren Sprachen eine Liste |
| `image` | Absolute URL des Bildes des Events (oder des Excerpt-Bildes) |

Es steht nur drin, was bekannt ist; leere Werte entfallen.

### Als JSON-LD

```twig
{% set schema = sulu_event_schema(event) %}
{% if schema %}
    <script type="application/ld+json">{{ {'@context': 'https://schema.org'}|merge(schema)|json_encode(constant('JSON_UNESCAPED_SLASHES') b-or constant('JSON_UNESCAPED_UNICODE') b-or constant('JSON_HEX_TAG') b-or constant('JSON_HEX_AMP'))|raw }}</script>
{% endif %}
```

`JSON_HEX_TAG` verhindert, dass ein `</script>` in einem Titel den Block beendet. Das Beispiel-Template `event_detail_example.html.twig` macht genau das.

### Im eigenen Graphen

Baut die Seite schon einen Graphen für strukturierte Daten (Organisation, Website, Brotkrumen, …), hängen Sie das Array dort ein, statt einen zweiten Block auszugeben. Mit einem `schema_add`-artigen Helfer ist das `schema_add(sulu_event_schema(event))`.

## `sulu_event_datetime(event, 'start'|'end')`

Beginn oder Ende als `DateTimeImmutable` **in der Zeitzone des Events** (siehe [Teilnahme, Zeitzone und Tickets](online-events.de.md)), `null`, wenn es keines gibt. `sulu_event_timezone(event)` liefert die `DateTimeZone`.

```twig
{% set start = sulu_event_datetime(event, 'start') %}
{% set zone = sulu_event_timezone(event) %}

<time datetime="{{ start|date('c', false) }}">
    {{ start|format_datetime('full', 'short', locale=app.request.locale, timezone=zone.name) }}
</time>
```

Der Twig-Filter `date` rechnet in die Zeitzone des Servers um, wenn man nicht `false` als zweites Argument übergibt (`|date('c', false)`); `format_datetime` braucht die Zone als `timezone=`. Sonst würde „10:00 in Las Vegas“ als Uhrzeit des Servers angezeigt.
