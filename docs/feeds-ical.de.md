# RSS/Atom-Feeds & iCal-Export

## Übersicht

Das SuluEventBundle bietet mehrere Möglichkeiten für Nutzer, Events zu abonnieren:
- **RSS/Atom-Feeds**: Halte Nutzer über neue Events auf dem Laufenden
- **iCal Einzel-Event**: Einzelne Events in Kalender-Anwendungen herunterladen
- **iCal Kalender-Feed**: Alle Events über webcal:// abonnieren

## RSS/Atom-Feed

### Verfügbare URLs

Der Feed ist in zwei Formaten verfügbar:

```
RSS 2.0:  /events/feed.{locale}.rss
Atom 1.0: /events/feed.{locale}.atom
```

**Beispiele:**
```
https://beispiel.de/events/feed.de.rss
https://beispiel.de/events/feed.en.atom
```

### Feed-Inhalt

Jeder Feed enthält:
- Event-Titel
- Event-Link (zur Detailseite)
- Event-Beschreibung (summary-Feld)
- Veröffentlichungsdatum (falls veröffentlicht)
- Eindeutige Event-ID (GUID)

### Verwendung in Templates

Feed-Discovery-Links in den HTML-`<head>` einfügen:

```html
<link rel="alternate" type="application/rss+xml" 
      title="Veranstaltungen RSS-Feed" 
      href="{{ path('sulu_event.feed', {_format: 'rss', _locale: app.request.locale}) }}" />

<link rel="alternate" type="application/atom+xml" 
      title="Veranstaltungen Atom-Feed" 
      href="{{ path('sulu_event.feed', {_format: 'atom', _locale: app.request.locale}) }}" />
```

Oder direkte Links anbieten:

```html
<a href="{{ path('sulu_event.feed', {_format: 'rss', _locale: app.request.locale}) }}">
    <i class="fa fa-rss"></i> RSS-Feed abonnieren
</a>
```

## iCal-Export

### Einzel-Event-Download

Einzelne Events als `.ics`-Datei herunterladen:

```
/events/{uuid}/download.{locale}.ics
```

**Beispiel:**
```
https://beispiel.de/events/019bf796-423c-7e1f-969c-5c4ece5e9b73/download.de.ics
```

`{uuid}` ist die UUID des Events (nicht mehr eine Zahl wie in älteren Versionen, die Route hat so nie funktioniert). Im Detail-Template ist `event` der Inhalt des Events, die UUID steht daher in `event.resource.id`. Events, die in der Sprache nicht veröffentlicht sind, liefern 404.

### Kalender-Feed (Abonnement)

Alle Events über eine Kalender-Anwendung abbonieren:

```
/events/calendar.{locale}.ics
```

**Beispiele:**
```
https://beispiel.de/events/calendar.de.ics
webcal://beispiel.de/events/calendar.de.ics
```

**Hinweis:** `webcal://`-Protokoll für direkte Kalender-Abonnements verwenden.

### Verwendung in Templates

#### Einzel-Event-Download-Button

```html
<a href="{{ path('sulu_event.ical_single', {id: event.resource.id, _locale: app.request.locale}) }}" 
   download="event-{{ event.resource.id }}.ics"
   class="btn btn-primary">
    <i class="fa fa-calendar-plus"></i> Zum Kalender hinzufügen
</a>
```

#### Kalender-Abonnement-Link

```html
<a href="webcal://{{ app.request.host }}{{ path('sulu_event.ical_feed', {_locale: app.request.locale}) }}" 
   class="btn btn-outline">
    <i class="fa fa-calendar"></i> Kalender abonnieren
</a>
```

Oder beide Protokolle anbieten:

```html
<div class="calendar-subscription">
    <h3>Kalender abonnieren</h3>
    <p>Klicke auf einen der folgenden Links zum Abonnieren:</p>
    <ul>
        <li>
            <a href="webcal://{{ app.request.host }}{{ path('sulu_event.ical_feed', {_locale: app.request.locale}) }}">
                Per webcal:// abonnieren (empfohlen)
            </a>
        </li>
        <li>
            <a href="{{ path('sulu_event.ical_feed', {_locale: app.request.locale}) }}" download="events.ics">
                .ics-Datei herunterladen
            </a>
        </li>
    </ul>
</div>
```

### iCal-Inhalt

Die Datei wird mit [sabre/vobject](https://sabre.io/vobject/) erzeugt; Sonderzeichen werden maskiert und lange Zeilen nach 75 Oktetten umgebrochen. Jedes iCal-Event enthält:
- **UID**: Stabiler Bezeichner `<uuid>@<host>`. Der Host ist der der Webseite des Events (Webspace und Sprache), nicht der des Abrufs, damit ein Abonnement keine Dubletten erzeugt, wenn die Seite unter einem anderen Namen abgerufen wird.
- **DTSTAMP** und **LAST-MODIFIED**: Zeitpunkt der letzten Änderung des Events
- **DTSTART**: Event-Start, als UTC (`…Z`) ausgegeben, siehe Zeitzone unten
- **DTEND**: Event-Ende (falls vorhanden und nach dem Start)
- **SUMMARY**: Event-Titel
- **DESCRIPTION**: Event-Zusammenfassung/Beschreibung (ohne HTML)
- **LOCATION**: Veranstaltungsort-Name (falls zugewiesen)
- **URL**: Absoluter Link zur Event-Detailseite, über die Webseiten-URL von Sulu gebildet (Webspace und Sprache des Events). Fehlt die Seite oder gibt es keine URL für die Sprache, entfällt die Zeile.
- **CATEGORIES**: Die Kategorien des Events (Reiter „Auszug“) mit ihren Namen in der Sprache des Feeds; hat eine Kategorie dort keinen Namen, wird der in der Standardsprache der Kategorie verwendet.

#### Zeitzone

Im Admin eingegebene Zeiten werden als Uhrzeit ohne Zeitzone gespeichert („10:00“). Der Export liest sie in der Zeitzone der Anwendung und rechnet sie nach UTC um, ein Termin um 10:00 Uhr in Berlin steht also im Sommer als `DTSTART:…T080000Z` und im Winter als `…T090000Z` in der Datei. Kalender-Anwendungen zeigen ihn dadurch in der eigenen Zeitzone des Nutzers richtig an. Eine `VTIMEZONE`-Angabe ist deshalb nicht nötig.

Die Zeitzone ist in dieser Reihenfolge: die des Events, die seines Ortes, die konfigurierte und die PHP-Standardzeitzone (`date.timezone`), siehe [Teilnahme, Zeitzone und Tickets](online-events.de.md). Die konfigurierte wird so gesetzt:

```yaml
# config/packages/sulu_event.yaml
sulu_event:
    timezone: 'Europe/Berlin'   # IANA-Name; ohne Angabe gilt die PHP-Standardzeitzone
```

#### Ganztägige Events

Events mit gesetztem Häkchen **Ganztägig** werden als Datum ohne Uhrzeit ausgegeben (`DTSTART;VALUE=DATE:…`); `DTEND` ist, wie in iCal vorgesehen, der Tag nach dem letzten Tag. Die Zeiten werden nicht mehr geraten: Ein Event, das um Mitternacht beginnt, ist ein Termin mit Uhrzeit, solange das Häkchen nicht gesetzt ist.

#### Status und Online-Events

Ein abgesagtes Event bekommt `STATUS:CANCELLED`, ein verschobenes `STATUS:TENTATIVE`. Ein Event ohne Ort, dessen Teilnahme „Online“ oder „Vor Ort und online“ ist, bekommt den Link zur Teilnahme als `LOCATION`.

### Kalender-Feed filtern

Kalender-Feeds können über Query-Parameter gefiltert werden:

```
/events/calendar.de.ics?categories[]=1&categories[]=2&tags[]=wichtig
```

**Verfügbare Filter:**
- `categories[]`: Nach Kategorie-IDs filtern
- `tags[]`: Nach Tag-Namen filtern

Mehrere Werte eines Filters gelten als ODER, Kategorien und Tags zusammen als UND. Ein Abo für ein einzelnes Thema ist damit ein Link wie:

```
webcal://example.com/events/calendar.de.ics?categories[]=12
```

Wie beim Smart Content und der Kalender-API zählen nur Events, die in der Sprache veröffentlicht sind. Ein Filter, der nur ungültige Werte enthält (etwa `categories[]=abc`), liefert einen leeren Kalender statt aller Events.

**Beispiel:**
```html
<a href="{{ path('sulu_event.ical_feed', {
    _locale: app.request.locale,
    categories: [1, 5],
    tags: ['konferenz', 'workshop']
}) }}">
    Konferenzen & Workshops abonnieren
</a>
```

## Kalender-Anwendungs-Support

### Getestete Anwendungen

Das iCal-Format ist kompatibel mit:
- **Google Calendar**: Import oder Abonnement via URL
- **Apple Calendar**: Abonnement via webcal://
- **Microsoft Outlook**: Import von .ics-Dateien
- **Thunderbird**: Abonnement via URL
- **Mobile Geräte**: iOS Kalender, Android Kalender-Apps
