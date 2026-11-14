# Dashboard-Kacheln

Ist [manuxi/sulu-dashboard-bundle](https://github.com/manuxi/SuluDashboardBundle) installiert, ergänzt das Bundle zwei Kacheln auf dem Sulu-Dashboard. Ohne Dashboard ändert sich nichts: Es wird nur vorgeschlagen (`composer suggest`), die Kacheln werden nur geladen, wenn es installiert ist.

```bash
composer require manuxi/sulu-dashboard-bundle
```

Beide Kacheln stehen in der schmalen Spalte. Sie sind mit dem Recht „Ansehen“ für Veranstaltungen (`sulu.events.events`, dasselbe gilt für Orte) sichtbar. Die Texte gibt es auf Deutsch und Englisch.

## Nächste Veranstaltungen

Die nächsten fünf veröffentlichten Veranstaltungen mit Datum und Ort, jeweils mit Link ins Formular. Der Ort steht als graue zweite Zeile. Im Fuß der Kachel steht die Zahl der Veranstaltungen, die in der Sprache der Toolbar nicht veröffentlicht sind (Entwurf ohne Live-Version), mit Link zur Liste.

- Berücksichtigt wird nur die veröffentlichte Version in der gewählten Sprache, wie auf der Website.
- Eine Veranstaltung gilt als bevorstehend, solange sie läuft oder heute oder später beginnt. Ohne Enddatum zählt das Startdatum ab heute.
- Wiederkehrende Veranstaltungen: Das Bundle speichert je Termin einen eigenen Eintrag (Befehl `sulu:events:generate-recurring`). Der nächste Termin einer Reihe ist deshalb der nächste bereits erzeugte Termin.

## Orte prüfen

Zeigt die Orte ohne Koordinaten oder mit unvollständiger Adresse (Straße, PLZ und Ort). In der zweiten Spalte steht, was fehlt (bei mehreren Angaben deren Anzahl). Ein Klick öffnet das Formular des Ortes. Ist alles vollständig, meldet die Kachel das.

Die Koordinaten stammen aus dem Kartenfeld des Ortes. Eine Kartenposition 0/0 gilt als fehlend.

## Screenshot-Bedarf

- Beide Kacheln auf dem Dashboard, mit und ohne Treffer (`docs/img/dashboard.de.png`, `docs/img/dashboard.en.png`)
