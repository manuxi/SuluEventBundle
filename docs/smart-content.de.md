# Smart Content: Filtern auf der Website

[← Zurück zur README](../README.de.md)

Der Smart-Content-Provider `events` wertet Kategorien und Tags aus, die ein Website-Besucher wählt, zusätzlich zu denen, die im Smart-Content-Feld selbst gespeichert sind. Der Provider liest sie aus der Query-String, ein Filter ist also ein einfacher Link oder ein `GET`-Formular:

| Parameter | Bedeutung | Beispiel |
|---|---|---|
| `categories` | Kategorie-IDs, kommagetrennt | `?categories=9,12` |
| `tags` | Tag-Namen, kommagetrennt | `?tags=Workshop,Online` |

- Mehrere Werte werden standardmäßig mit **ODER** verknüpft (eine Veranstaltung braucht einen davon). Mit `website_categories_operator` / `website_tags_operator` auf `AND` in den Params des Smart-Content-Felds müssen alle zutreffen.
- Die Auswahl des Besuchers schränkt das Ergebnis des Felds weiter ein; im Admin gesetzte Kategorien und Tags gelten weiterhin.
- Paginierung (`max_per_page`) und die Gesamtzahl der Smart-Content-Ansicht (`view.total`) beziehen sich auf das gefilterte Ergebnis.
- Die Namen der Parameter lassen sich mit `categories_parameter` und `tags_parameter` in den Params des Felds ändern.

```xml
<property name="events" type="smart_content">
    <params>
        <param name="provider" value="events"/>
        <param name="max_per_page" value="12"/>
        <param name="website_tags_operator" value="AND"/>
    </params>
</property>
```

Welche Kategorien und Tags angeboten werden, entscheidet das Template: Nur Veranstaltungen mit Excerpt tragen sie, und nur die Live-Version zählt. Der Helfer [`sulu_event_filter_options`](#filter-optionen-für-eine-filterleiste) listet auf, was es gibt.

## Weitere Filter: Typ, Teilnahme, Land, kostenlos

| Parameter | Bedeutung | Beispiel |
|---|---|---|
| `type` | Schlüssel der Event-Typen (`sulu_event.types`), kommagetrennt. Unbekannte Schlüssel zählen nicht; bleibt keiner übrig, trifft der Filter auf nichts zu | `?type=conference,meetup` |
| `attendanceMode` | `offline`, `online`, `mixed`, kommagetrennt. Exakte Werte: `online` schließt `mixed` nicht ein | `?attendanceMode=online,mixed` |
| `country` | Ländercodes (ISO 3166-1 alpha-2) des Ortes, kommagetrennt | `?country=DE,AT` |
| `free` | `1`, `true`, `yes` oder `on`: nur Events, die als kostenlos gekennzeichnet sind | `?free=1` |
| `featured` | `1`, `true`, `yes` oder `on`: nur Highlights (siehe [Highlights](#highlights)); ohne `featured_tag` trifft der Filter auf nichts zu | `?featured=1` |

- Mehrere Werte einer Art gelten als **ODER**, die Arten untereinander als **UND** (auch mit Kategorien und Tags). Eine Liste ohne verwertbaren Wert (`?attendanceMode=hybrid`) trifft auf nichts zu.
- Die Namen der Parameter lassen sich mit `type_parameter`, `attendance_mode_parameter`, `country_parameter`, `free_parameter` und `featured_parameter` in den Params des Felds ändern.
- Im Admin stehen dieselben Filter in der Liste der „Typen“ des Smart-Content-Felds bereit: neben *Anstehend*, *Vergangen* und den Event-Typen gibt es „Teilnahme: Vor Ort / Online / Vor Ort und online“, „Kostenlos“, „Highlights“ (nur mit `featured_tag`) und „Land: …“ (die Länder der verwendeten Orte). Das sind feste Filter des Felds; sie verbinden sich mit den Event-Typen (ODER innerhalb einer Art, UND zwischen den Arten).

## Filter-Optionen für eine Filterleiste

`sulu_event_filter_options(locale)` liefert die Werte, die eine Filterleiste anbieten kann, jeweils mit der Zahl der **kommenden, veröffentlichten** Events der Sprache (Werte ohne solche Events entfallen):

```twig
{% set options = sulu_event_filter_options(app.request.locale) %}

{% for topic in options.topics %}
    <a href="?categories={{ topic.id }}">{{ topic.name }} ({{ topic.count }})</a>
{% endfor %}
{% for type in options.types %}{{ type.name }} {{ type.color }} ({{ type.count }}){% endfor %}
{% for country in options.countries %}<a href="?country={{ country.code }}">{{ country.name }}</a>{% endfor %}
{% for mode in options.attendanceModes %}<a href="?attendanceMode={{ mode.key }}">{{ mode.name }}</a>{% endfor %}
{% if options.free %}<a href="?free=1">Kostenlos ({{ options.free.count }})</a>{% endif %}
{% if options.featured %}<a href="?featured=1">Highlights ({{ options.featured.count }})</a>{% endif %}
```

| Schlüssel | Einträge |
|---|---|
| `topics` | `id`, `key`, `name` (in der Sprache, sonst in der Standardsprache der Kategorie), `parent` (ID oder `null`), `depth`, `count`; in Baumreihenfolge |
| `types` | `key`, `name`, `color`, `count` |
| `countries` | `code`, `name` (in der Sprache), `count`, nach Name sortiert |
| `attendanceModes` | `key`, `name`, `count` |
| `free` | `['count' => n]` oder `null`, wenn kein kommendes Event kostenlos ist |
| `featured` | `['count' => n]` oder `null`, wenn `featured_tag` nicht gesetzt ist oder kein kommendes Event ein Highlight ist |

Themen sind Kategorien. Wird der Schlüssel einer Wurzelkategorie gesetzt, werden nur deren Nachfahren aufgelistet:

```yaml
# config/packages/sulu_event.yaml
sulu_event:
    category_root: 'event-topics'
```

Ohne die Option erscheint jede verwendete Kategorie, bei einem unbekannten Schlüssel keine. Die Auswahl der Kategorien im **Admin** (Reiter „Auszug“) ist ein allgemeines Feld von Sulu und wird von dieser Option nicht eingeschränkt.

Das Ergebnis wird je Sprache zwischengespeichert (eine Stunde, in `cache.app.taggable`, falls vorhanden) und geleert, sobald ein Event angelegt, geändert, veröffentlicht, zurückgezogen, kopiert, gelöscht oder wiederhergestellt wird.

## Highlights

Flaggschiffe lassen sich betonen und filtern, ohne neues Datenbankfeld: Ein Event ist ein **Highlight**, wenn es einen bestimmten Tag trägt. Den Namen legt das Projekt fest; ohne Namen ist die Funktion aus (Standard).

```yaml
# config/packages/sulu_event.yaml
sulu_event:
    featured_tag: 'Highlight'
```

- Der Tag wird im Event wie jeder andere gesetzt (Reiter „Excerpt“) oder vom Import (`featured: true` an der Quelle, siehe [Import](import.de.md)). Per MCP: `featured: true` bei `sulu_event_import_upsert`, `"featured": true/false` in `content` von `sulu_event_update`.
- Die Namen werden ohne Rücksicht auf Groß-/Kleinschreibung verglichen.
- **Filter**: `?featured=1` bei Smart Content, [Kalender-API](calendar.de.md) und [iCal-Feed](feeds-ical.de.md); im Admin „Highlights“ in der Typenliste des Smart-Content-Felds.
- **Anzeige**: `event.featured` (true/false) steht in den Daten jedes Events (Twig), `extendedProps.featured` in der Kalender-API.
- Ist `featured_tag` nicht gesetzt, ist kein Event ein Highlight, und der Filter `featured` liefert bewusst nichts statt aller Events.
- Die Filter-Optionen enthalten `featured` mit der Zahl kommender Highlights (siehe oben).
