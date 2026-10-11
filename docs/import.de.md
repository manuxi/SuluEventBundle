# Event-Import

[← Zurück zur README](../README.de.md)

Der Import liest Events aus Kalender-Feeds (iCal) und aus Webseiten mit strukturierten Daten (schema.org / JSON-LD) und legt sie als **Entwurf** an. Er veröffentlicht nie etwas: Jedes Event prüft eine Redakteurin oder ein Redakteur, schreibt den Text und veröffentlicht es. Der Import ist ausgeschaltet, bis er konfiguriert wird.

```bash
bin/adminconsole sulu:event:import --dry-run   # zeigen, was passieren würde
bin/adminconsole sulu:event:import             # ausführen
```

## Was übernommen wird (und was nicht)

Übernommen werden nur **Fakten**: Titel, Datum und Uhrzeit, Zeitzone, Ort, Status, Links für Tickets und Stream, Preis, Veranstalter (nur Organisationen), Sprache. Die **Beschreibung** einer Quelle wird *nicht* als Text des Events verwendet: Der Text einer Seite oder eines Feeds gehört seinem Autor. Sie wird als Hinweis beim Import-Eintrag gespeichert, für alle, die den Text schreiben. Namen und Adressen von Personen (Veranstalter, Teilnehmer in Kalender-Feeds) werden verworfen.

Jedes Event ist ein Entwurf in der Sprache der Quelle (`locale`). Es bekommt Typ, Kategorien und Tags aus der Konfiguration der Quelle, eine URL aus Titel und Jahr (`/events/example-conf-2027`, bei Doppelungen mit angehängter Zahl) und den Ort (gefunden oder neu angelegt, siehe unten).

## Einrichtung

1. Datenbank aktualisieren, siehe [UPGRADE.md](../UPGRADE.md) (Tabellen `app_event_import` und `app_event_import_run`).
2. Einen Sulu-Benutzer für den Import anlegen (zum Beispiel `event-import`). Importierte Events zeigen diesen Benutzer als Ersteller.
3. Quellen konfigurieren:

```yaml
# config/packages/sulu_event.yaml
sulu_event:
    import:
        enabled: true
        user: 'event-import'
        user_agent: 'MeinProjekt-EventImport (+https://example.com/kontakt)'
        auto_update_published_dates: false
        sources:
            ical:
                -   key: 'dev-kalender'
                    url: 'https://example.com/kalender/events.ics'
                    type: 'meetup'
                    categories: ['web-php']
                    tags: ['Symfony']
                    locale: 'de'
            jsonld:
                -   key: 'example-conf'
                    url: 'https://conf.example.com/'
                    type: 'conference'
                    series: 'example-conf'
                    timezone: 'Europe/Berlin'
```

| Option | Bedeutung | Standard |
|---|---|---|
| `enabled` | Schaltet den Import ein | `false` |
| `user` | Benutzername des Sulu-Benutzers, der Ersteller und Bearbeiter importierter Events ist. **Pflicht**, wenn eingeschaltet | – |
| `user_agent` | Wie sich der Import vorstellt. Einen Kontakt angeben | `SuluEventBundle-Import (+https://github.com/manuxi/SuluEventBundle)` |
| `timeout` | Sekunden, die auf eine Quelle gewartet wird | `15` |
| `auto_update_published_dates` | Eine Änderung von Datum, Uhrzeit oder Status eines **veröffentlichten** Events geht von selbst live, wenn im Entwurf sonst nichts wartet | `false` |
| `url_prefix` | Pfad, mit dem die URLs importierter Events beginnen | `/events` |
| `webspace` | Webspace importierter Events | Standard-Webspace |
| `category_images` | Eigene Bilder: Kategorie-Schlüssel => Dateiname oder ID eines Mediums, siehe „Bilder“ | – |
| `location_image` | Bild (Id oder Dateiname) für Orte, die der Import neu anlegt: keine Quelle kennt ein Foto vom Ort. Orte ohne Bild bekommen es mit `sulu:event:fill-images`; die Redaktion ersetzt es durch ein echtes Foto | – |
| `location_aliases` | Andere Schreibweisen eines Ortes oder einer Stadt und die, die führt, zum Beispiel `Brüssel: Brussels`, `Köln: Cologne`. Der Import findet und legt den Ort unter dem führenden Namen an, eine deutsche Schreibweise der Quelle macht keinen zweiten Ort (Stadt mit Bundesstaat oder Land, „San Francisco CA“, ist ohnehin dieselbe Stadt) | – |
| `locales` | Sprachen, in denen ein importiertes Event einen Text haben soll. Jede bekommt beim Import gleich einen Entwurf mit dem Titel der Quelle und allen Fakten (auch bei Events, die es schon gibt); fehlt der Text, steht das Event in der Liste der Events mit offener Arbeit | – |

Optionen einer Quelle (`ical` und `jsonld` haben dieselben):

| Option | Bedeutung |
|---|---|
| `key` | Kurzname (`a-z`, Ziffern, `-`, `_`), erscheint in Berichten. Je Art eindeutig |
| `url` | Adresse des Feeds oder der Seite (http/https) |
| `type` | Event-Typ (Schlüssel aus `sulu_event.types`) |
| `categories` | Schlüssel von Sulu-Kategorien; unbekannte Schlüssel werden gemeldet |
| `tags` | Namen von Tags; fehlende Tags werden angelegt |
| `locale` | Sprache von Titel und Texten der Quelle (Standard `en`). Eine Sprache verwenden, die der Webspace hat |
| `timezone` | Zeitzone für Events, zu denen die Quelle keine nennt. Optional |
| `series` | Name einer Veranstaltungsreihe, siehe „Mehrere Quellen“ |
| `priority` | Welche Quelle gewinnt, siehe „Mehrere Quellen“. Standard 100 für Seiten, 50 für Kalender |
| `languages` | Sprachkürzel, in denen die Events gehalten werden |
| `image` | ID eines Mediums (ein eigenes Bild), das jedes Event der Quelle bekommt, siehe „Bilder“ |

Fehler in der Konfiguration werden beim Aufbau des Caches gemeldet, mit dem Pfad der Option.

## Quellen

**iCal** (`ical`): jeder `.ics`-Feed, zum Beispiel Kalender einer Konferenz oder eines Anbieters. Wiederkehrende Events werden für die nächsten 12 Monate aufgelöst (höchstens 60 Termine je Event). Zeiten mit Zeitzone behalten sie; Zeiten in UTC bekommen die Zone des Ortes (aus den Koordinaten) oder die konfigurierte `timezone`; ganztägige Events bleiben ganztägig. Abgesagte Events werden als abgesagt markiert.

**Seiten mit strukturierten Daten** (`jsonld`): liest `<script type="application/ld+json">` mit `Event` oder einem Untertyp (auch innerhalb von `@graph` und Listen). Die Zeitzone ergibt sich aus dem Offset zusammen mit Land oder Koordinaten des Ortes, oder aus `timezone`.

Eigene Quellen: `Manuxi\SuluEventBundle\Import\EventSourceInterface` implementieren (`getKey()`, `getPriority()`, `fetch()` mit `EventCandidate`-Objekten) und den Service mit `sulu_event.import_source` taggen (wird automatisch konfiguriert).

## Bilder

Importierte Events bekommen **eigene Bilder**, nie die Bilder einer Quelle (die gehören der Quelle). Ohne Bild wirkt ein Event in Listen und Karten leer, deshalb:

```yaml
sulu_event:
    import:
        category_images:          # Kategorie-Schlüssel => Dateiname (oder ID) eines Mediums
            web-php: event-topic-web-php.jpg
            ai: event-topic-ai.jpg
        sources:
            jsonld:
                -   key: 'example-conf'
                    url: 'https://conf.example.com/'
                    image: 130    # geht dem Bild der Kategorie vor
```

**Dateinamen** sind besser als IDs: Die IDs der Medien unterscheiden sich zwischen Testplattform und Live-Seite, die Namen nicht (genommen wird das erste Medium, dessen aktuelle Datei so heißt). Ein neues Event bekommt das Bild seiner Quelle, sonst das seiner ersten Kategorie, die aufgeführt ist; es wird als Bild und als Excerpt-Bild (Karten und Teaser) gesetzt. Ein Event ohne Bild bekommt beim nächsten Lauf ebenfalls eins; ein Bild, das jemand gewählt hat, wird nie ersetzt. Eine Medien-ID, die es nicht gibt, wird als Warnung gemeldet. „Bild fehlt“ erscheint auch in der Liste der Events, an denen noch etwas zu tun ist (MCP-Werkzeug `sulu_event_import_pending`).

**Orte** haben kein Foto aus einer Quelle. Mit `location_image` (Dateiname oder ID) bekommt jeder Ort, den der Import neu anlegt, ein Bild; die Redaktion ersetzt es durch ein echtes Foto. **Nachträglich** füllt `php bin/adminconsole sulu:event:fill-images` (mit `--dry-run` zum Ansehen) alle Events und Orte ohne Bild: ein Event nimmt das Bild seines ersten Themas aus `category_images`, ein Ort das aus `location_image`. Ein vorhandenes Bild wird nie ersetzt; ein veröffentlichtes Event wird dabei zum Entwurf und ist mit dem Bild live, sobald es erneut veröffentlicht wird.

## Im Admin

- **Tab „Quelle“** eines Events (nur für Events aus dem Import): die Quellen, der Zustand, der letzte Abgleich, die Seite des Events bei der Quelle, die Beschreibung der Quelle (Hinweis für alle, die den Text schreiben, wird nicht veröffentlicht), der letzte Fehler und die geschützten Felder. Mit dem Schalter **Alle geschützten Felder freigeben** werden sie freigegeben: nach dem Speichern schreibt der nächste Import die Werte der Quelle wieder hinein.
- **Liste der Events**: die Spalte **Quelle** (leer bei von Hand erfassten Events) und die Filter **Zustand des Imports** (aktiv / nicht mehr in der Quelle) und **Veröffentlichungsstand** im Filter der Liste. Mit „importiert“ und „nicht veröffentlicht“ ergibt sich die Liste der Events, die auf Freigabe warten.
- **Einstellungen > Import**: der letzte Lauf (Zeitpunkt, Zahlen, Fehler, neue Orte), nur zur Ansicht.

### Der Knopf „Jetzt importieren“ (optional)

Mit `sulu_event.import.admin_button: true` hat der Reiter **Einstellungen > Import** einen Knopf **Jetzt importieren**: er startet den Import aller Quellen im Hintergrund (nach der Antwort an den Browser, das dauert ein bis zwei Minuten), füllt danach fehlende Bilder (`sulu:event:fill-images`) und zeigt das Ergebnis im selben Reiter. Solange ein Lauf geht, ist der Knopf gesperrt und der Reiter zeigt „Import läuft“; ein Lauf, der nach 30 Minuten nicht fertig ist, gilt als abgebrochen. Ein fehlgeschlagener Lauf steht mit seiner Fehlermeldung im Reiter. Was der Knopf **nicht** tut: Report-Mail, Koordinaten für die Karte und andere Aufgaben Ihres Projekts (das macht Ihr täglicher Lauf).

Der Knopf ist ein Stück JavaScript des Bundles und deshalb **aus**, solange es nicht in den Admin-Build eingebunden ist, sonst bricht der Reiter mit „toolbar action not registered“ ab. Einbinden: in `assets/admin/package.json` die Abhängigkeit `"sulu-event-bundle": "file:../../vendor/manuxi/sulu-event-bundle/src/Resources/js"`, in `assets/admin/app.js` die Zeile `import 'sulu-event-bundle';`, dann `npm install` und `npm run build` in `assets/admin`. Das Recht dafür ist „Bearbeiten“ der Einstellungen. Der Import läuft in dem PHP-Prozess, der die Antwort gesendet hat (`kernel.terminate`); mit PHP-FPM (`fastcgi_finish_request`) funktioniert das ohne Weiteres, bei anderen Servern kann der Browser auf das Ende des Laufs warten.

## Der Benutzer des Imports

`sulu_event.import.user` ist der Benutzer, dessen Name als Ersteller importierter Events erscheint. Der Befehl braucht **keine Rolle**: Er schreibt als dieser Benutzer, ohne anzumelden, und prüft keine Rechte. Eine Rolle ist nur nötig, wenn sich jemand mit diesem Benutzer anmeldet, etwa um die MCP-Werkzeuge zu nutzen (diese prüfen die Rechte). Einem solchen Benutzer eine Rolle geben, die Events (`sulu.events.events`), Orte, Tags und Kategorien (nur Ansehen) und Medien (nur Ansehen) **ansehen, hinzufügen und bearbeiten** darf, aber **nicht** das Recht „Live“ (Veröffentlichen) bei den Events, damit alles ein Entwurf bleibt, bis jemand es veröffentlicht.

## Was bei einem Lauf passiert

| Ergebnis | Bedeutung |
|---|---|
| Neu | Ein Entwurf wurde angelegt (abgesagte Events, die noch nicht bekannt sind, werden nicht angelegt) |
| Geändert | Der Entwurf eines bekannten Events wurde aktualisiert. Texte, Kategorien und Tags werden nur **ergänzt**, wenn sie leer sind, nie ersetzt |
| Unverändert | Nichts zu tun |
| Gesperrt | Ein Feld wurde nach dem letzten Import im Admin geändert. Es wird **nicht überschrieben**, der Lauf meldet den Unterschied |
| Verschwunden | Ein zukünftiges Event steht nicht mehr in der Quelle. Es bleibt, wird aber markiert; gelöscht wird nie |
| Abgelehnt | Das Event wurde im Admin gelöscht. Es wird nicht wieder angelegt. Wiederherstellen aus dem Papierkorb hebt das auf |
| Fehler | Eine Quelle oder ein Event ist fehlgeschlagen. Der Exit-Code des Befehls ist 1 |
| Neue Orte | Ein Ort wurde angelegt (Name, Zeitzone; „geraten“, wenn das Land mehrere Zonen hat). Bitte ansehen |

Änderungen gehen immer in den **Entwurf**. Ist das Event veröffentlicht, bleibt die Live-Version bestehen, bis jemand den Entwurf veröffentlicht. Mit `auto_update_published_dates: true` geht eine Änderung, die nur Datum, Uhrzeit, Zeitzone oder Status betrifft, von selbst live, aber nur, wenn im Entwurf sonst nichts wartet.

**Sperr-Erkennung**: Der Import merkt sich, was er geschrieben hat. Weicht der Wert im Entwurf davon ab, hat jemand im Admin geändert; das Feld wird gesperrt und die Quelle überschreibt es nicht mehr.

**Orte** werden über Name und Stadt gefunden, oder über Koordinaten (näher als 300 m); sonst wird ein neuer Ort mit der Zeitzone seines Landes oder seiner Koordinaten angelegt.

**Mehrere Quellen für ein Event**: Liefern zwei Quellen dasselbe Event (eine Konferenzseite und ein Kalender), bekommen beide dieselbe `series` (zum Beispiel `example-conf`). Zusammen mit dem Jahr des Beginns ergibt sich der Schlüssel `example-conf-2027`; die zweite Quelle verknüpft sich dann mit dem vorhandenen Event, statt ein weiteres anzulegen. Die Quelle mit der höheren `priority` pflegt das Event. `series` nur für Quellen verwenden, die *ein Event pro Jahr* liefern; Events derselben Quelle werden nie zusammengeführt.

Das Ergebnis jedes Laufs wird gespeichert (Tabelle `app_event_import_run`), der Befehl gibt es als Tabelle aus:

```
 Neu   Geändert   Unverändert   Gesperrt   Verschwunden   Abgelehnt   Fehler   Neue Orte
```

(Die Ausgabe des Befehls ist englisch: New, Changed, Unchanged, Locked, Vanished, Rejected, Errors, New locations.)

## Regelmäßig ausführen

Einmal am Tag genügt. Beispiel für Cron:

```
0 5 * * * cd /var/www/project && bin/adminconsole sulu:event:import
```

`--source=ical.dev-kalender` führt nur eine Quelle aus, `--limit=20` importiert höchstens 20 Events je Quelle.

## Höflichkeit, Nutzungsbedingungen und Datenschutz

- Der Import stellt sich mit `user_agent` vor, ruft höchstens eine Seite pro Sekunde und Host ab, merkt sich `ETag` / `Last-Modified` (eine unveränderte Seite kostet fast nichts) und befolgt die `robots.txt` (die Antwort wird 24 Stunden gemerkt; lässt sie sich nicht lesen, bleibt die Seite unberührt).
- **Die Nutzungsbedingungen jeder Quelle prüfen**, bevor sie eingetragen wird. Manche Anbieter erlauben keinen automatischen Abruf oder keine kommerzielle Nutzung ihrer Daten; für diese Events von Hand eintragen oder um Erlaubnis bitten. Der Import liest nur, was eine Seite dafür veröffentlicht (Feeds, strukturierte Daten).
- Keine Feeds eintragen, die personenbezogene Daten enthalten. Namen und Adressen von Personen werden nicht übernommen, aber die Verantwortung für den Inhalt der Quellen bleibt bei Ihnen. Den Text der Events schreiben Sie selbst, nicht die Quelle.
