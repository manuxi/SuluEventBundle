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

`url` und `locationId` sind im Standard-Template Pflicht. Den Ort vorher mit `sulu_location_create` anlegen oder eine ID aus `sulu_location_list` nehmen.

Veröffentlichen gehört nicht zu den Tools. Das geht im Admin oder mit den [Sammelaktionen](https://github.com/manuxi/SuluBulkActionsBundle).
