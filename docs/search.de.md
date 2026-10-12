# Suche

[← Zurück zur README](../README.de.md)

Veröffentlichte Events stehen im Suchindex `website` (Sulu Search, SEAL). Das Schema dieses Index gehört Sulu: `title`, `url`, `content`, `mediaId`, `authoredAt` und `metadata`. Alles andere, was ein Treffer anzeigen soll, liegt deshalb in `metadata.preview`. Felder außerhalb des Schemas verwirft der Suchadapter.

| Feld | Inhalt |
| --- | --- |
| `title`, `url`, `mediaId` | Titel, Adresse und Bild des Events |
| `content` | Untertitel, Zusammenfassung, Text und Fußtext als reiner Text (ohne HTML) |
| `metadata.preview.subtitle` | Untertitel |
| `metadata.preview.description` | Zusammenfassung, sonst Anfang des Textes (höchstens 500 Zeichen) |
| `metadata.preview.startsAt`, `endsAt` | Beginn und Ende (ISO 8601) |
| `metadata.preview.place` | Ort als „Name, Stadt“ |
| `metadata.preview.type` | Schlüssel des Event-Typs |

Leere Felder fehlen im Index. Ein Template sollte jedes Feld deshalb mit einem Standardwert lesen (`hit.metadata.preview.place ?? null`).

Beim Veröffentlichen und beim vollständigen Neuaufbau (`php bin/console sulu:search:reindex`) entsteht dasselbe Dokument (`Search/EventWebsiteDocument`). Events, die vor dieser Version indiziert wurden, brauchen einmal den Neuaufbau, damit die Vorschaudaten im Index stehen.
