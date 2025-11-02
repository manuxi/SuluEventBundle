# Standorte

## Übersicht

Das Standortsystem ermöglicht die Verwaltung und Wiederverwendung von Event-Standorten. Standorte werden separat von Events verwaltet und können mehreren Events zugewiesen werden.

## Standorte verwalten

Standorte werden im Sulu-Admin-Panel unter **Events > Standorte** verwaltet.

### Standort-Eigenschaften

- **Name**: Anzeigename des Standorts
- **Straße**: Straßenadresse
- **Nummer**: Haus-/Gebäudenummer
- **Postleitzahl**: PLZ
- **Stadt**: Stadtname
- **Land**: Ländername
- **Breitengrad**: Geografischer Breitengrad (für Karten)
- **Längengrad**: Geografischer Längengrad (für Karten)
- **Notizen**: Zusätzliche Informationen oder Anweisungen

## Standorte Events zuweisen

Bei der Erstellung oder Bearbeitung eines Events können Sie im Feld **Standort** einen Standort aus bestehenden Einträgen auswählen. Dies ist ein Einzelauswahlfeld, das auf vorhandene Standort-Einträge verweist.

## Verwendung in Templates

### Standort-Informationen anzeigen

```twig
{% if event.location %}
    <div class="event-location">
        <h3>Standort</h3>
        
        <address>
            <strong>{{ event.location.name }}</strong><br>
            {{ event.location.street }} {{ event.location.number }}<br>
            {{ event.location.postalCode }} {{ event.location.city }}<br>
            {{ event.location.country }}
        </address>
        
        {% if event.location.notes %}
            <p class="location-notes">{{ event.location.notes }}</p>
        {% endif %}
    </div>
{% endif %}
```

### Karte anzeigen

Wenn Breiten- und Längengrad gesetzt sind:

```twig
{% if event.location and event.location.latitude and event.location.longitude %}
    <div class="location-map">
        <div id="map" 
             data-lat="{{ event.location.latitude }}" 
             data-lng="{{ event.location.longitude }}"
             data-name="{{ event.location.name }}">
        </div>
    </div>
{% endif %}
```

### Formatierte Adresse erhalten

```twig
{% set address = [
    event.location.street ~ ' ' ~ event.location.number,
    event.location.postalCode ~ ' ' ~ event.location.city,
    event.location.country
]|filter(v => v)|join(', ') %}

<p>{{ address }}</p>
```

### Karten-Links generieren

#### Google Maps
```twig
{% if event.location %}
    {% set query = [
        event.location.name,
        event.location.street,
        event.location.number,
        event.location.postalCode,
        event.location.city
    ]|filter(v => v)|join(' ')|url_encode %}
    
    <a href="https://www.google.com/maps/search/?api=1&query={{ query }}" 
       target="_blank">
        In Google Maps öffnen
    </a>
{% endif %}
```

#### OpenStreetMap
```twig
{% if event.location.latitude and event.location.longitude %}
    <a href="https://www.openstreetmap.org/?mlat={{ event.location.latitude }}&mlon={{ event.location.longitude }}&zoom=15" 
       target="_blank">
        In OpenStreetMap öffnen
    </a>
{% endif %}
```

## Standort-basierte Filterung

### Kalender-API

Events nach Standortnamen filtern:

```
GET /api/events/calendar/de?location=Konferenzzentrum
```

### Listenansicht

Events nach Standort in benutzerdefinierten Abfragen filtern:

```twig
{% set events = sulu_event_query()
    .locale(app.request.locale)
    .location('Konferenzzentrum')
    .execute() %}
```

## Geocoding

Das Bundle enthält keine automatische Geocodierung. Sie können:

1. **Manuelle Eingabe**: Breiten-/Längengrad manuell eingeben
2. **Externer Service**: Geocoding-Service verwenden (Google Maps, OpenStreetMap Nominatim)
3. **Eigene Implementierung**: Geocodierung über Event-Listener oder eigene Commands hinzufügen

### Beispiel: JavaScript-Geocoding

```javascript
async function geocodeLocation(address) {
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`;
    const response = await fetch(url);
    const data = await response.json();
    
    if (data.length > 0) {
        return {
            latitude: parseFloat(data[0].lat),
            longitude: parseFloat(data[0].lon)
        };
    }
    
    return null;
}
```

## Schema.org-Integration

Für korrektes Event-Markup Standortdaten einbeziehen:

```twig
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Event",
    "name": "{{ event.title }}",
    "startDate": "{{ event.startDate|date('c') }}",
    {% if event.location %}
    "location": {
        "@type": "Place",
        "name": "{{ event.location.name }}",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "{{ event.location.street }} {{ event.location.number }}",
            "addressLocality": "{{ event.location.city }}",
            "postalCode": "{{ event.location.postalCode }}",
            "addressCountry": "{{ event.location.country }}"
        }
        {% if event.location.latitude and event.location.longitude %},
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": {{ event.location.latitude }},
            "longitude": {{ event.location.longitude }}
        }
        {% endif %}
    }
    {% endif %}
}
</script>
```

## Best Practices

1. **Standorte wiederverwenden** - Standort-Einträge einmal erstellen und für mehrere Events nutzen
2. **Vollständige Daten** - So viele Felder wie möglich ausfüllen für bessere User Experience
3. **Konsistente Benennung** - Einheitliche Standortnamen für Filterung und Suche verwenden
4. **Koordinaten hinzufügen** - Breiten-/Längengrad für Karten-Integration einbeziehen
5. **Zentral aktualisieren** - Bei Standortänderungen den Standort-Eintrag aktualisieren (betrifft alle Events)
6. **Notizen-Feld nutzen** - Parkhinweise, Barrierefreiheit oder besondere Anweisungen hinzufügen

## Virtuelle Events

Für Online-Events können Sie entweder:

1. **Standort leer lassen** - Geeignet für rein virtuelle Events
2. **"Online"-Standort erstellen** - Standort mit Namen "Online" oder "Virtuell" für Filterung erstellen
3. **Notizen-Feld verwenden** - Meeting-Link oder Plattform-Informationen in Notizen eintragen

Beispiel virtueller Standort:
```
Name: Online
Notizen: Zoom-Meeting-Link wird per E-Mail versendet
```