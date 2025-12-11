# Fixtures (Testdaten)

Das Bundle enthält eine Fixture-Klasse (`EventFixture`), um Testdaten für die Entwicklung zu generieren. Dies ist besonders nützlich, um das Layout und die Funktionalität (z.B. Pagination, Filter) zu testen.

## Verwendung

Die Fixtures können über die Symfony-Konsole geladen werden. Da sie der Gruppe `events` zugeordnet sind, können sie gezielt ausgeführt werden, ohne andere Daten zu löschen/ändern (abhängig von der `--append` Option).

```bash
# Lädt die Event-Fixtures (löscht ggf. bestehende Daten je nach Konfiguration)
php bin/console doctrine:fixtures:load --group=events --append
```

**Hinweis:** Die Option `--append` sorgt dafür, dass die Daten hinzugefügt werden, anstatt die gesamte Datenbank zu bereinigen.

## Generierte Daten

Die `EventFixture` erstellt:
- **25 Events** mit unterschiedlichen Titeln und Typen (Konferenz, Workshop, Meeting, etc.).
- **Zeiträume**: Mix aus vergangenen und zukünftigen Events (von -30 bis +45 Tagen).
- **Sprachen**: Inhalte werden sowohl für Englisch (`en`) als auch Deutsch (`de`) erstellt.
- **Status**: Die meisten Events werden veröffentlicht, jedes 5. Event bleibt im Entwurf-Status (`draft`), um auch das Verhalten unveröffentlichter Events testen zu können.
- **Details**: Füllt Titel, Untertitel, Zusammenfassung, Beschreibung, Footer, SEO-Daten, Auszüge, Kontaktdaten und Veranstaltungsorte.

## Voraussetzungen

Stellen Sie sicher, dass die Datenbank aktuell ist und die notwendigen Tabellen existieren:

```bash
php bin/console doctrine:schema:update --force
```
