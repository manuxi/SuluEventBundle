# Kalender-Feature

## Übersicht

Das SuluEventBundle enthält ein integriertes Kalendersystem auf Basis von FullCalendar. Mit diesem Feature können Events in verschiedenen Kalenderansichten auf der Website dargestellt werden.

![img.png](img/calendar-month.de.png)
![img.png](img/calendar-year.de.png)

## Installation

Die Installation ist nicht ganz ohne. Sicher gibt es andere Wege (von denen ich gerne mehr erfahre!), ich beschreibe meinen:

Im Hauptprojekt HP die Sources laden:

```bash
npm install --save @fullcalendar/core@^6.1.19 @fullcalendar/bootstrap5@^6.1.19 @fullcalendar/daygrid@^6.1.19 @fullcalendar/timegrid@^6.1.19 @fullcalendar/list@^6.1.19 @fullcalendar/multimonth@^6.1.19
```

Javascript und (optional) scss einbunden:
assets/website/js/app.js:
```javascript
import '../../../vendor/manuxi/sulu-event-bundle/src/Resources/public/js/calendar.js';
```
assets/website/scss/app.scss:
```scss
@import '../../../vendor/manuxi/sulu-event-bundle/src/Resources/public/scss/calendar.scss';
```

Damit die Projektvariablen auch in der scss verfügbar sind (webpack.config.js):
```javascript
Encore.enableSassLoader(options => {
    options.sassOptions = {
        includePaths: [
            'assets/website/scss',
        ],
    };
    options.additionalData = `
        @import "config/variables"; 
        @import "config/variables.components";
    `;
})
```
Für Javascripts habe ich folgende Änderungen in der webpack.config.js durchgeführt:
```javascript
// enables and configure @babel/preset-env polyfills
Encore.configureBabelPresetEnv((config) => {
    config.useBuiltIns = 'usage';
    config.corejs = {
        version: 3,
        proposals: true
    };
})
```
und
```javascript
const config = Encore.getWebpackConfig();

// Fix module resolution for core-js and node_modules in bundles
config.resolve = config.resolve || {};
config.resolve.modules = config.resolve.modules || [];
config.resolve.modules.unshift(path.resolve(__dirname, 'node_modules'));
config.resolve.symlinks = true;

module.exports = config;
```

## Konfiguration

Die Kalendereinstellungen können im Sulu-Admin-Panel unter **Einstellungen > Events** konfiguriert werden.

### Verfügbare Einstellungen

![settings-calendar.de.png](img/settings-calendar.de.png)

Siehe auch [Einstellungen](docs/settings.de.md)

#### Kalenderdarstellung
- **Wochenstart**: Erster Tag der Woche (0 = Sonntag, 1 = Montag, etc.)
- **Wochennummern anzeigen**: Nummern der Kalenderwochen anzeigen
- **Wochenenden anzeigen**: Samstag und Sonntag in Kalenderansicht einbeziehen

#### Events
- **Event-Limit pro Tag**: Maximale Anzahl Events pro Tag, bevor "mehr anzeigen"-Link erscheint
- **Event-Zeit anzeigen**: Start-/Endzeiten in der Kalenderansicht anzeigen
- **Auf Event-Zeitraum begrenzen**: Nur Daten innerhalb des Bereichs geplanter Events anzeigen
- **Event-Typ anzeigen**: Typ in der Einzelansicht (Popover) anzeigen
- **Event-Ort anzeigen**: Standort in der Einzelansicht (Popover) anzeigen
- **Event-Farbe**: Standardfarbe für Events in der Kalenderansicht

#### Kalenderansichten
- **Sicht-Wechsel erlaubt**: Sichten sind für Besucher änderbar
- **Erlaubte Kalendersichten**: Auswahl verfügbarer Ansichten (Monat, Woche, Tag, Liste)

#### Anzeigeoptionen Wochensicht
- **Start-Zeit**: Tag fängt um diese Zeit an
- **End-Zeit**: Tag hört um diese Zeit auf

#### Anzeigeoptionen Jahressicht
- **Anzuzeigende Monate**: Anzahl an Monaten, die angezeigt werden

## Verwendung

### Content Type

Einfacher Schalter:

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

Es wird ein Element mit id="event-calendar" erwartet. Parameter wie folgt angeben:

```html
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
         data-week-day-start="{{ eventsSettings.calendarWeekTimeStart|default('00:00') }}"
         data-week-day-end="{{ eventsSettings.calendarWeekTimeEnd|default('24:00') }}"
         data-year-months="{{ eventsSettings.calendarYearMonths|default(3) }}"
         data-weekends="{{ eventsSettings.showWeekends ? 'true' : 'false' }}"
         data-event-limit="{{ eventsSettings.eventLimitPerDay|default(3) }}"
         data-limit-to-events="{{ eventsSettings.limitToEventRange ? 'true' : 'false' }}"
         data-first-day="{{ eventsSettings.calendarStartDay|default(1) }}"
         data-event-color="{{ eventsSettings.eventColor|default('#ccc') }}"
         data-toggle-view="{{ eventsSettings.toggleCalendarView ? 'true' : 'false' }}"
         data-toggle-location="{{ eventsSettings.showCalendarEventLocation ? 'true' : 'false' }}"
         data-toggle-type="{{ eventsSettings.showCalendarEventType ? 'true' : 'false' }}"
         data-allowed-views="{{ eventsSettings.allowedCalendarViews|join(',') }}">

    </div>
{% endif %}
```

'calendarView' steuert die inititale View. Siehe nächsten Abschnitt.

### Beispiel für Auswahl einer View

```html
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

## Rate Limiting

Die Kalender-API ist auf 100 Anfragen pro Stunde pro IP-Adresse limitiert. Der Rate Limiter kann bei Bedarf in der Symfony-Konfiguration angepasst werden.

## Frontend-Implementierung

Das Bundle bietet eine grundlegende Kalenderimplementierung. Für erweiterte Anpassungen siehe die FullCalendar-Dokumentation unter https://fullcalendar.io/

## Ganztägige Events

Events werden als ganztägig erkannt, wenn:
- Startzeit ist 00:00
- Endzeit ist 00:00 oder keine Endzeit gesetzt ist

Ganztägige Events werden im Kalender ohne Zeitangabe dargestellt.