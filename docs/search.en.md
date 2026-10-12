# Search

[← Back to README](../README.md)

Published events are in the `website` search index (Sulu Search, SEAL). Sulu owns the schema of this index: `title`, `url`, `content`, `mediaId`, `authoredAt` and `metadata`. Anything else a hit should show therefore lives in `metadata.preview`. Fields outside the schema are dropped by the search adapter.

| Field | Content |
| --- | --- |
| `title`, `url`, `mediaId` | Title, address and image of the event |
| `content` | Subtitle, summary, text and footer as plain text (no HTML) |
| `metadata.preview.subtitle` | Subtitle |
| `metadata.preview.description` | Summary, otherwise the beginning of the text (500 characters at most) |
| `metadata.preview.startsAt`, `endsAt` | Start and end (ISO 8601) |
| `metadata.preview.place` | Place as "Name, City" |
| `metadata.preview.type` | Key of the event type |

Empty fields are missing from the index, so a template should read each field with a default (`hit.metadata.preview.place ?? null`).

Publishing and the full rebuild (`php bin/console sulu:search:reindex`) write the same document (`Search/EventWebsiteDocument`). Events indexed before this version need one rebuild so that the preview data is in the index.
