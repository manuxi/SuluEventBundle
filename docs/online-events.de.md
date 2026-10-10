# Teilnahme, Zeitzone und Tickets

[← Zurück zur README](../README.de.md)

Neben Datum und Ort kennt ein Event, wie man teilnimmt (vor Ort, online oder beides), in welcher Zeitzone seine Zeiten gemeint sind, ob es überhaupt stattfindet, wo es Tickets gibt und wer es veranstaltet. Alles ist optional; Events ohne diese Angaben verhalten sich wie bisher.

## Im Admin

- **Datum & Ort** (sichtbar, solange „Datum“ eingeschaltet ist): Beginn, Ende und **Ganztägig**, darunter **Teilnahme**, Referent und Ort.
- **Teilnahme & Tickets**: ein Abschnitt, der zugeklappt bleibt, bis in „Weiteres“ der Schalter **Teilnahme & Tickets** eingeschaltet wird.

| Feld | Bedeutung | Standard |
|---|---|---|
| Ganztägig | Nur das Datum zählt. Beginn wird 00:00:00, Ende 23:59:59 | aus |
| Teilnahme | Vor Ort, online oder vor Ort und online | vor Ort |
| Ort | Pflicht, außer die Teilnahme ist „Online“ (dann ist das Feld ausgeblendet) | – |
| Status | Findet statt, verschoben, neu terminiert, abgesagt, jetzt online | findet statt |
| Zeitzone | IANA-Name der Zone von Beginn und Ende, siehe unten | automatisch |
| Sprachen | Sprache(n) der Veranstaltung | – |
| Tickets / Anmeldung | Link zu Tickets oder Anmeldung (http/https) | – |
| Link zur Online-Teilnahme | Stream- oder Meeting-Link, nur bei „Online“ und „Vor Ort und online“ | – |
| Kostenlos | Schalter für kostenlose Events | aus |
| Preis | Kurztext je Sprache, z. B. „ab 99 EUR“. Bei kostenlosen Events ausgeblendet | – |
| Veranstalter, Website des Veranstalters | | – |

Der Ort wird für Online-Events mit dem [SuluBlockValidationBundle](https://github.com/manuxi/SuluBlockValidationBundle) optional (eine Abhängigkeit dieses Bundles; es muss in `config/bundles.php` registriert sein: `Manuxi\SuluBlockValidationBundle\SuluBlockValidationBundle::class => ['all' => true]`). Die Prüfung gibt es nur im Admin-Formular: MCP-Tools, Importe und die Konsole verlangen keinen Ort.

## Zeitzone

Beginn und Ende eines Events werden so gespeichert, wie sie eingegeben wurden, als Uhrzeit ohne Zone („10:00“). Die Zone, in der sie gemeint sind, ist in dieser Reihenfolge:

1. die **Zeitzone des Events** (Abschnitt „Teilnahme & Tickets“),
2. die **Zeitzone seines Ortes** (Orts-Formular),
3. die konfigurierte Zone `sulu_event.timezone`,
4. die PHP-Standardzeitzone.

„Automatisch“ bleibt ausgewählt, solange das Event nicht in einer anderen Zone stattfindet als sein Ort, etwa eine Online-Keynote einer Firma aus Kalifornien. Für Events im Ausland bekommt der **Ort** einmal seine Zone (z. B. „America/Los_Angeles“ für das Messezentrum in Las Vegas); jedes Event dort funktioniert dann ohne weitere Eingabe.

Eine spätere Änderung der Zone verschiebt die gespeicherte Uhrzeit nicht: „10:00“ bleibt 10:00, wird nur in einer anderen Zone gelesen. Die iCal-Dateien und die Kalender-API nutzen diese Zone, um den richtigen Zeitpunkt anzugeben ([iCal](feeds-ical.de.md), [Kalender](calendar.de.md)).

## Ganztägige Events

Mit **Ganztägig** werden die Uhrzeiten ignoriert: Der Beginn wird auf 00:00:00 gesetzt und das Ende, falls vorhanden, auf 23:59:59. Im iCal-Export sind es Datumsangaben (`DTSTART;VALUE=DATE`), im Kalender `allDay`-Events.

Bis es dieses Feld gab, hat das Bundle „ganztägig“ aus den Uhrzeiten geraten. Dieses Raten entfällt: Ein Event um Mitternacht ist nur ganztägig, wenn das Häkchen gesetzt ist. Das SQL, um bestehende Events zu übernehmen, steht in [UPGRADE.md](../UPGRADE.md).

## Status

Abgesagte und verschobene Events bleiben in Listen und Kalendern und werden entsprechend gekennzeichnet:

| Status | iCal | Kalender-API |
|---|---|---|
| Findet statt, neu terminiert, jetzt online | – | `extendedProps.status` |
| Abgesagt | `STATUS:CANCELLED` | `extendedProps.status` |
| Verschoben | `STATUS:TENTATIVE` | `extendedProps.status` |

## Online-Events

Bei „Online“ ist kein Ort nötig. In der iCal-Datei ist der Link zur Teilnahme der Ort des Events, wenn es keinen Ort hat. Die Kalender-API sendet `extendedProps.attendanceMode`, damit ein Frontend Online-Events kennzeichnen kann.

## Für Templates

Alle Felder stehen am Inhalt des Events bereit: `event.allDay`, `event.timezone`, `event.status`, `event.attendanceMode`, `event.onlineUrl`, `event.registrationUrl`, `event.isFree`, `event.priceInfo`, `event.organizerName`, `event.organizerUrl`, `event.languages`. Die Zeitzone eines Events liefert der Service `sulu_event.timezone_resolver` (`resolve()` gibt die `DateTimeZone`, `toInstant()` macht aus einer gespeicherten Uhrzeit ein `DateTimeImmutable` in dieser Zone). In Twig nimmt man `sulu_event_datetime(event, 'start')`; die strukturierten Daten und das Beispiel-Template stehen in [Strukturierte Daten (schema.org) und Zeiten im Template](schema-org.de.md).

## MCP

`sulu_event_create` und `sulu_event_update` nehmen dieselben Schlüssel in `content` (`allDay`, `timezone`, `status`, `attendanceMode`, `onlineUrl`, `registrationUrl`, `isFree`, `priceInfo`, `organizerName`, `organizerUrl`, `languages`), siehe [MCP-Tools](mcp.de.md). Ungültige Werte (unbekannter Status, unbekannte Zeitzone, eine URL ohne http/https) werden mit einer Meldung abgelehnt.
