# Kalender-Feature

## Übersicht

Das SuluEventBundle enthält ein integriertes Kalendersystem auf Basis von FullCalendar. Mit diesem Feature können Events in verschiedenen Kalenderansichten auf der Website dargestellt werden.

## Konfiguration

Die Kalendereinstellungen können im Sulu-Admin-Panel unter **Einstellungen > Events** konfiguriert werden.

### Verfügbare Einstellungen

![settings-calendar.de.png](img/settings-calendar.de.png)

#### Kalenderdarstellung
- **Wochenstart**: Erster Tag der Woche (0 = Sonntag, 1 = Montag, etc.)
- **Event-Zeit anzeigen**: Start-/Endzeiten in der Kalenderansicht anzeigen
- **Wochennummern anzeigen**: Wochennummern in Kalenderansicht anzeigen
- **Wochenenden anzeigen**: Samstag und Sonntag in Kalenderansicht einbeziehen

#### Events
- **Event-Limit pro Tag**: Maximale Anzahl Events pro Tag, bevor "mehr anzeigen"-Link erscheint
- **Auf Event-Zeitraum begrenzen**: Nur Daten innerhalb des Bereichs geplanter Events anzeigen (momentan außer Funktion)
- **Event-Ort anzeigen**: Ortsinformationen in der Kalenderansicht anzeigen
- **Event-Farbe**: Standardfarbe für Events in der Kalenderansicht

#### Kalenderansichten
- **Kalenderansicht aktivieren**: Kalenderansicht komplett aktivieren/deaktivieren
- **Erlaubte Kalenderansichten**: Auswahl verfügbarer Ansichten (Monat, Woche, Tag, Liste)

## Verwendung

### Content Type

Kalender zu einer Seite hinzufügen über den Content Type:

```xml
<property name="toggle_calendar" type="checkbox" colspan="2">
    <meta>
        <title lang="en">Show Calendar</title>
        <title lang="de">Kalender zeigen</title>
    </meta>
    <params>
        <param name="type" value="toggler"/>
    </params>
</property>
```

### Template-Integration

```twig
{% if toggle_calendar %}
    {% if eventsSettings is not defined %}
        {% set eventsSettings = load_event_settings() %}
    {% endif %}

    <div class="col-12 event-calendar justify-content-{{ alignment_content }}" id="event-calendar"
         data-events-url="{{ path('sulu_event.api.calendar', {
             '_locale': app.request.locale,
             'dataId': events.dataId|default(0),
             'includeSubFolders': events.includeSubFolders|default(0),
             'categories': events.categories|default([]),
             'tags': events.tags|default([])
         }) }}"

         data-initial-view="{{ calendarView|default('dayGridMonth') }}"
         data-locale="{{ app.request.locale }}"
         data-week-numbers="{{ eventsSettings.showWeekNumbers ? 'true' : 'false' }}"
         data-weekends="{{ eventsSettings.showWeekends ? 'true' : 'false' }}"
         data-event-limit="{{ eventsSettings.eventLimitPerDay|default(3) }}"
         data-limit-to-events="{{ eventsSettings.limitToEventRange ? 'true' : 'false' }}"
         data-first-day="{{ eventsSettings.calendarStartDay|default(1) }}"
         data-event-color="{{ eventsSettings.eventColor|default('#ccc') }}"
         data-toggle-view="{{ eventsSettings.toggleCalendarView ? 'true' : 'false' }}"
         data-toggle-location="{{ eventsSettings.showCalendarEventLocation ? 'true' : 'false' }}"
         data-allowed-views="{{ eventsSettings.allowedCalendarViews|join(',') }}">

    </div>
{% endif %}
```
### Beispiel für Auswahl einer View

```
<property name="calendarView" type="single_select" colspan="10" visibleCondition="__parent.toggle_calendar == true">
    <meta>
        <title lang="de">Kalenderansicht</title>
        <title lang="de">Calendar View</title>
    </meta>
    <params>
        <param name="defaultValue" value="listMonth"/>
        <param name="values" type="collection">
            <param name="dayGridMonth">
                <meta>
                    <title lang="en">Month Grid</title>
                    <title lang="de">Monatsraster</title>
                </meta>
            </param>
            <param name="timeGridWeek">
                <meta>
                    <title lang="en">Week with Times</title>
                    <title lang="de">Woche mit Uhrzeiten</title>
                </meta>
            </param>
            <param name="listWeek">
                <meta>
                    <title lang="en">Week List</title>
                    <title lang="de">Wochenliste</title>
                </meta>
            </param>
            <param name="listMonth">
                <meta>
                    <title lang="en">Month List</title>
                    <title lang="de">Monatsliste</title>
                </meta>
            </param>
            <param name="multiMonthYear">
                <meta>
                    <title lang="en">Year Overview (4 Monate)</title>
                    <title lang="de">Jahresübersicht (4 Monate)</title>
                </meta>
            </param>
        </param>
    </params>
</property>
```

### API-Endpunkt

Der Kalender ruft Events über folgende URL ab:
```
GET /api/events/calendar/{locale}
```

#### Query-Parameter

- `start` - Startdatum (ISO 8601 Format)
- `end` - Enddatum (ISO 8601 Format)
- `dataId` - Filterung nach Seiten-ID
- `includeSubFolders` - Events von Unterseiten einbeziehen
- `categories[]` - Filterung nach Kategorie-IDs
- `tags[]` - Filterung nach Tag-IDs
- `location` - Filterung nach Ortsname
- `sortBy` - Sortierfeld (startDate, title, created, changed)
- `sortMethod` - Sortierrichtung (asc, desc)

#### Response-Format

```json
[
  {
    "id": 123,
    "title": "Event-Titel",
    "start": "2025-11-15T14:00:00+00:00",
    "end": "2025-11-15T16:00:00+00:00",
    "allDay": false,
    "url": "/events/event-slug",
    "extendedProps": {
      "summary": "Event-Beschreibung",
      "location": "Ortsname"
    }
  }
]
```

## Rate Limiting

Die Kalender-API ist auf 100 Anfragen pro Stunde pro IP-Adresse limitiert. Der Rate Limiter kann bei Bedarf in der Symfony-Konfiguration angepasst werden.

## Frontend-Implementierung

Das Bundle bietet eine grundlegende Kalenderimplementierung. Für erweiterte Anpassungen siehe die FullCalendar-Dokumentation unter https://fullcalendar.io/

### Grundlegendes JavaScript-Setup

```javascript
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';

const calendar = new Calendar(calendarEl, {
    plugins: [dayGridPlugin, timeGridPlugin, listPlugin],
    initialView: 'dayGridMonth',
    locale: 'de',
    events: '/api/events/calendar/de'
});

calendar.render();
```

## Ganztägige Events

Events werden als ganztägig erkannt, wenn:
- Startzeit ist 00:00
- Endzeit ist 00:00 oder keine Endzeit gesetzt ist

Ganztägige Events werden im Kalender ohne Zeitangabe dargestellt.