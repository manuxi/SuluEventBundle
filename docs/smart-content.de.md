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

Welche Kategorien und Tags angeboten werden, entscheidet das Template: Nur Veranstaltungen mit Excerpt tragen sie, und nur die Live-Version zählt.
