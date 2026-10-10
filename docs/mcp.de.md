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
