# SuluEventBundle!
![php workflow](https://github.com/manuxi/SuluEventBundle/actions/workflows/php.yml/badge.svg)
![symfony workflow](https://github.com/manuxi/SuluEventBundle/actions/workflows/symfony.yml/badge.svg)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://github.com/manuxi/SuluEventBundle/LICENSE)
![GitHub Tag](https://img.shields.io/github/v/tag/manuxi/SuluEventBundle)
![Supports Sulu 2.6 or later](https://img.shields.io/badge/%20Sulu->=2.6-0088cc?color=00b2df)

[🇬🇧 English Version](README.md)

Dieses Bundle basiert auf dem [Sulu Workshop](https://github.com/sulu/sulu-workshop) und wurde im Laufe der Zeit mit immer mehr Features ausgestattet.

![img.png](docs/img/event-details.png)

## ✨ Features

### 📅 Event-Verwaltung
- **Vollständiger Event-Lebenszyklus** - Erstelle, veröffentliche und archiviere Events
- **Umfangreiche Event-Details** - Titel, Untertitel, Zusammenfassung, Text, Fußzeile
- **Datum & Uhrzeit** - Flexible Start-/Enddaten mit Zeitzonenunterstützung
- **Veranstaltungsorte** - Separate Ortsverwaltung mit Adressdetails
- **Medien-Integration** - Hero-Bilder, Bildergalerien, PDF-Anhänge
- **SEO & Excerpt** - Vollständiger SEO-Tab und Excerpt-Verwaltung
- **Mehrsprachig** - Vollständige Übersetzungsunterstützung
- **Autoren-Verwaltung** - Weise Kontakte als Event-Autoren zu

### 🔄 Erweiterte Features
- **Wiederkehrende Events** - Tägliche, wöchentliche, monatliche, jährliche Muster mit Ausnahmen
- **Social-Media-Integration** - Pro-Event-Sharing-Konfiguration (Facebook, Twitter, LinkedIn, Instagram, WhatsApp)
- **Kalender** - FullCalendar.js Integration mit Monats-/Wochen-/Listenansicht
- **iCal-Export** - Einzelne Events oder vollständige Kalender-Abonnements (webcal://)
- **RSS/Atom-Feeds** - Halte Abonnenten über neue Events auf dem Laufenden
- **Smart Content** - Als Content-Block in jeder Sulu-Seite verwendbar

Siehe [README.md](README.md) für vollständige Feature-Liste und Entwickler-Features.

## 📋 Voraussetzungen

- PHP 8.1 oder höher
- Sulu CMS 2.6 oder höher
- Symfony 6.x oder höher
- MySQL 5.7+ / MariaDB 10.2+ / PostgreSQL 11+

## 👩🏻‍🏭 Installation

### Schritt 1: Paket installieren

```bash
composer require manuxi/sulu-event-bundle
```

Falls du *nicht* Symfony Flex verwendest, füge das Bundle in `config/bundles.php` hinzu:

```php
return [
    //...
    Manuxi\SuluEventBundle\SuluEventBundle::class => ['all' => true],
];
```

### Schritt 2: Routen konfigurieren

Zu `routes_admin.yaml` hinzufügen:

```yaml
SuluEventBundle:
    resource: '@SuluEventBundle/Resources/config/routes_admin.yaml'
```
Für FullCalendar-Integration/iCal/Feeds muss Folgendes zu `routes_website.yaml` hinzugefügt werden:

```yaml
SuluEventBundle:
    resource: '@SuluEventBundle/Resources/config/routes_website.yaml'
```

### Schritt 3: Suche konfigurieren

Füge zu `sulu_search.yaml` hinzu:

```yaml
sulu_search:
    website:
        indexes:
            - events_published  # Veröffentlichte Events (Website)
            - events            # Entwürfe Events (Admin)
```

### Schritt 4: Datenbank aktualisieren

```bash
# Prüfe was erstellt wird
php bin/console doctrine:schema:update --dump-sql

# Führe Migration aus
php bin/console doctrine:schema:update --force
```

### Schritt 5: Berechtigungen erteilen

1. Gehe zu Sulu Admin → Einstellungen → Benutzerrollen
2. Finde die passende Rolle
3. Aktiviere Berechtigungen für "Events" und "Locations"
4. Lade die Seite neu

## 🎣 Verwendung

### Erstes Event erstellen

1. Navigiere zu **Events** in der Sulu-Admin-Navigation
2. Klicke auf **Event hinzufügen**
3. Erstelle zuerst mindestens einen **Veranstaltungsort**
4. Erstelle dann dein Event mit allen Details
5. Konfiguriere Social-Media-Einstellungen (optional)
6. Richte Wiederholungsmuster ein (optional)
7. Veröffentliche dein Event

Weitere Details siehe [README.md](README.md).

## 🧶 Konfiguration

### Event-Einstellungen

Zugriff über Sulu Admin → Einstellungen → Events

**Anzeigeoptionen:**
- Header, Hero-Bild, Breadcrumbs umschalten

**Kalender-Optionen:**
- Kalenderansicht aktivieren
- Standard-Ansicht (Monat/Woche/Liste)
- Erster Wochentag
- Event-Zeit anzeigen
- Event-Ort anzeigen

- Weiteres

### Wiederkehrende Events

Richte einen Cron-Job ein:

```bash
# Täglich um 2 Uhr ausführen
0 2 * * * cd /pfad/zum/projekt && php bin/console sulu:events:generate-recurring --lookahead=90
```

## 📖 Dokumentation

Detaillierte Dokumentation im [docs/](docs/) Verzeichnis.

- [Kalender-Integration](docs/calendar.de.md) - FullCalendar.js-Integration
- [Social Media](docs/social-media.de.md) - Social-Sharing-Konfiguration
- [Wiederkehrende Events](docs/recurring.de.md) - Wiederholende Event-Muster
- [Dynamic Event Types](docs/event_types.de.md) - Konfiguration der Event-Types
- [Feeds/iCal](docs/feeds-ical.de.md) - Feeds und iCal Handling
- [Locations](docs/locations.de.md) - Locations Handling

## 👩‍🍳 Mitwirken

Beiträge sind willkommen! Bitte erstelle Issues oder Pull Requests.

## 📝 Lizenz

Dieses Bundle ist unter der MIT-Lizenz lizenziert. Siehe [LICENSE](LICENSE).

## 🎉 Credits

Erstellt und gewartet von [manuxi](https://github.com/manuxi).

Basierend auf dem [Sulu Workshop](https://github.com/sulu/sulu-workshop).
