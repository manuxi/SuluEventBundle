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
/events/{id}/download.{locale}.ics
```

**Beispiel:**
```
https://beispiel.de/events/42/download.de.ics
```

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
<a href="{{ path('sulu_event.ical_single', {id: event.id, _locale: app.request.locale}) }}" 
   download="event-{{ event.id }}.ics"
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

Jedes iCal-Event enthält:
- **UID**: Eindeutiger Event-Identifier
- **DTSTAMP**: Zeitstempel der iCal-Generierung
- **DTSTART**: Event-Startdatum und -zeit
- **DTEND**: Event-Enddatum und -zeit (falls vorhanden)
- **SUMMARY**: Event-Titel
- **DESCRIPTION**: Event-Zusammenfassung/Beschreibung
- **LOCATION**: Veranstaltungsort-Name (falls zugewiesen)
- **URL**: Link zur Event-Detailseite

### Kalender-Feed filtern

Kalender-Feeds können über Query-Parameter gefiltert werden:

```
/events/calendar.de.ics?categories[]=1&categories[]=2&tags[]=wichtig
```

**Verfügbare Filter:**
- `categories[]`: Nach Kategorie-IDs filtern
- `tags[]`: Nach Tag-Namen filtern

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
