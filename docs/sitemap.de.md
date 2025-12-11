# Sitemap Integration

Das SuluEventBundle integriert sich automatisch in die Sulu Sitemap. Events werden in der Sitemap aufgelistet, sofern sie veröffentlicht sind und nicht explizit ausgeblendet wurden.

## Funktionsweise

Der `EventSitemapProvider` sammelt alle Events, die:
- Veröffentlicht sind
- Der aktuellen Webspace-Sprache entsprechen
- Nicht im SEO-Tab ausgeblendet wurden ("In Sitemap verstecken" nicht aktiv)

Die URLs werden basierend auf dem Routing generiert.

## Konfiguration

Es ist keine spezielle Konfiguration notwendig, solange das Bundle korrekt registriert ist. Der `EventSitemapProvider` ist als Service mit dem Tag `sulu.sitemap.provider` registriert.

### Events ausblenden

Um ein einzelnes Event aus der Sitemap auszuschließen:
1. Öffnen Sie das Event im Admin-Bereich.
2. Gehen Sie zum Tab **SEO**.
3. Aktivieren Sie die Checkbox **In Sitemap verstecken**.
4. Speichern und veröffentlichen Sie das Event.

## Überprüfung

Die Sitemap kann unter `/sitemap.xml` (je nach Konfiguration) aufgerufen werden. Dort sollten die Event-URLs erscheinen, z.B.:
```xml
<url>
    <loc>https://example.org/events/mein-tolles-event</loc>
    <lastmod>2024-03-20T10:00:00+00:00</lastmod>
</url>
```
