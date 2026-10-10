# MCP-Tools

Ist [sulu/mcp-bundle](https://github.com/sulu/SuluMcpBundle) installiert, bringt das Bundle Tools mit, mit denen KI-Assistenten Veranstaltungen bearbeiten können. Ohne dieses Bundle wird nichts geladen.

| Tool | Zweck | Recht |
|---|---|---|
| `sulu_event_list` | Veranstaltungen einer Sprache (Entwürfe), seitenweise | Ansehen |
| `sulu_event_get` | Eine Veranstaltung mit allen Template-Feldern | Ansehen |
| `sulu_event_create` | Veranstaltung als Entwurf anlegen | Hinzufügen |
| `sulu_event_update` | Felder einer Veranstaltung ändern | Bearbeiten |
| `sulu_location_list` | Orte mit ihren IDs | Ansehen |
| `sulu_location_create` | Ort anlegen | Hinzufügen |
| `sulu_event_import_upsert` | Ein Event mit den Regeln des Imports als Entwurf anlegen oder aktualisieren | Hinzufügen und Bearbeiten |
| `sulu_event_import_pending` | Importierte Events, an denen noch etwas zu tun ist | Ansehen |
| `sulu_event_import_report` | Ergebnis des letzten Import-Laufs | Ansehen |

Die Rechte sind die des Sicherheitskontexts der Veranstaltungen (`sulu.events.events`).

## Veranstaltung anlegen

`sulu_event_create` erwartet `locale`, `title`, `template` (`event`, `event_basic` oder `event_detailed`) und die Template-Felder in `content`:

```json
{
  "url": "/events/meine-veranstaltung",
  "type": "conference",
  "startDate": "2026-11-05T09:00:00",
  "endDate": "2026-11-05T17:00:00",
  "locationId": 1,
  "summary": "Kurztext",
  "text": "<p>Text</p>",
  "image": {"id": 12}
}
```

`url` ist Pflicht. `locationId` ist Pflicht, außer `attendanceMode` ist `online`. Den Ort vorher mit `sulu_location_create` anlegen (es nimmt eine optionale IANA-`timezone` wie `America/Los_Angeles`) oder eine ID aus `sulu_location_list` nehmen.

Weitere Schlüssel in `content`:

| Schlüssel | Wert |
|---|---|
| `allDay` | `true`: ganze Tage, die Uhrzeiten werden auf 00:00:00 und 23:59:59 gesetzt |
| `timezone` | IANA-Name für die Uhrzeit in `startDate`/`endDate`; weglassen, um die Zone des Ortes oder der Website zu nehmen |
| `status` | `scheduled`, `postponed`, `rescheduled`, `cancelled`, `moved_online` |
| `attendanceMode` | `offline`, `online`, `mixed` |
| `onlineUrl`, `registrationUrl`, `organizerUrl` | vollständige http(s)-URLs |
| `isFree` | `true` / `false` |
| `priceInfo` | Kurztext der Sprache, z. B. `ab 99 EUR` |
| `organizerName` | Text |
| `languages` | z. B. `["de", "en"]` |
| `excerptCategories` | Kategorie-IDs aus dem Sulu-Tool `sulu_category_list` |
| `excerptTags` | Tag-**IDs** (keine Namen) aus `sulu_tag_list` / `sulu_tag_create`; unbekannte IDs werden ignoriert |
| `excerpt` | `{"image": {"id": 12}, "title": "...", "description": "..."}`, das Bild für Karten und Teaser |

Ungültige Werte (ein unbekannter Status, eine unbekannte Zeitzone, eine URL ohne `http://` oder `https://`) werden mit einer Meldung abgelehnt, gespeichert wird dann nichts. `sulu_event_get` zeigt die Zeitzone als `auto`, wenn das Event keine eigene hat. Mehr: [Teilnahme, Zeitzone und Tickets](online-events.de.md).

Veröffentlichen gehört nicht zu den Tools. Das geht im Admin oder mit den [Sammelaktionen](https://github.com/manuxi/SuluBulkActionsBundle).

## Event ändern

`sulu_event_update` nimmt `uuid`, `locale`, optional `title` und die zu ändernden Felder in `content`:

```json
{"summary": "Neuer Kurztext", "startDate": "2026-11-06T09:00:00"}
```

Geändert werden nur die übergebenen Felder. Alles andere (Bild, Ort, Termine, Kategorien usw.) bleibt unverändert. Ein veröffentlichtes Event wird wieder zum Entwurf und muss erneut veröffentlicht werden.

## Werkzeuge für den Import

Die drei Werkzeuge gehören zum [Event-Import](import.de.md). Sie sind für Recherche und Redaktion gedacht: Ein Assistent findet Events im Web, trägt sie ein und vervollständigt später die, die der Import angelegt hat.

**`sulu_event_import_upsert`** legt **ein** Event als Entwurf an oder aktualisiert es, mit denselben Abgleich- und Schutzregeln wie der Import-Befehl. Parameter: `source` (woher das Event stammt, z. B. `editorial`), `externalId` (eine stabile Kennung innerhalb der Quelle, z. B. die URL der Seite) und die Fakten: `titles` (Sprache => Titel), `startDate`, `endDate`, `timezone`, `allDay`, `status`, `attendanceMode`, `location` (Objekt) oder `locationId`, `onlineUrl`, `registrationUrl`, `organizerName`, `organizerUrl`, `isFree`, `priceInfos`, `languages`, `type`, `categories` (**Schlüssel**), `tags` (**Namen**), `subtitles`, `summaries`, `texts`, `sourceUrl`, `sourceDescription`, `series`, `priority` (Standard 30) und `dryRun`.

```json
{
  "source": "editorial",
  "externalId": "https://conf.example.com/2027",
  "titles": {"de": "Example Conf 2027"},
  "startDate": "2027-06-12T10:00:00",
  "endDate": "2027-06-13T17:00:00",
  "timezone": "Europe/Berlin",
  "location": {"name": "Messe Hall 3", "city": "Köln", "countryCode": "DE"},
  "categories": ["web-php"],
  "tags": ["Symfony"],
  "summaries": {"de": "Zwei Tage rund um PHP."},
  "sourceUrl": "https://conf.example.com/2027",
  "series": "example-conf"
}
```

Die Antwort enthält `action` (`created`, `updated`, `unchanged`, `rejected`), `uuid`, `detail`, `lockedFields` (Felder, die jemand im Admin geändert hat: sie werden nicht überschrieben), `warnings` (ein unbekannter Kategorie-Schlüssel, ein fehlendes Bild) und `newLocations`. Ein erneuter Aufruf mit derselben `source` und `externalId` aktualisiert dasselbe Event. Ein im Admin gelöschtes Event wird nicht neu angelegt (`rejected`). Texte, Kurztexte, Kategorien, Tags und das Bild werden nur ergänzt, wenn das Event noch keine hat. Die Texte selbst schreiben: der Text einer Quelle darf nicht kopiert werden; was gelesen wurde, gehört in `sourceDescription`, das nicht veröffentlicht wird.

**`sulu_event_import_pending`** listet importierte Events, denen etwas fehlt: `text:de`, `summary:en` (leer), `locale:en` (kein Entwurf in dieser Sprache), `categories` (kein Thema), `image` (kein Bild). Jeder Eintrag trägt `sourceUrl` (die Seite des Events) und `sourceDescription` als Recherchehilfe sowie `published`. `locales` begrenzt die geprüften Sprachen (Standard: `sulu_event.import.locales`, sonst die Sprachen des Events). Seitenweise mit `page` und `limit`.

**`sulu_event_import_report`** liefert den letzten Lauf des Import-Befehls: Zahlen, Fehler, verschwundene Events, Events mit geschützten Feldern, neue Orte (`certain: false` heißt: Zeitzone geraten). `includeDryRuns` berücksichtigt auch Testläufe.

Die Rechte sind die des Security-Kontexts der Events. Die Werkzeuge laufen als der Benutzer, der am MCP-Server angemeldet ist, nicht als `sulu_event.import.user`.
