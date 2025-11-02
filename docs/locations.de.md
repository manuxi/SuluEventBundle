# Standorte

## Übersicht

Das Standortsystem ermöglicht die Verwaltung und Wiederverwendung von Event-Standorten. Standorte werden separat von Events verwaltet und können mehreren Events zugewiesen werden.

## Standorte verwalten

Standorte werden im Sulu-Admin-Panel unter **Events > Standorte** verwaltet.

### Standort-Eigenschaften

![img.png](img/locations.de.png)

- **Name**: Anzeigename des Standorts
- **Straße**: Straßenadresse
- **Nummer**: Haus-/Gebäudenummer
- **Postleitzahl**: PLZ
- **Stadt**: Stadtname
- **Land**: Ländername
- **Notizen**: Zusätzliche Informationen oder Anweisungen
- **Weitere Optionen**

## Standorte Events zuweisen

Bei der Erstellung oder Bearbeitung eines Events können Sie im Feld **Standort** einen Standort aus bestehenden Einträgen auswählen. Dies ist ein Einzelauswahlfeld, das auf vorhandene Standort-Einträge verweist.

## Verwendung in Templates

### Standort-Informationen anzeigen

```html
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

```html
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

```html
{% set address = [
    event.location.street ~ ' ' ~ event.location.number,
    event.location.postalCode ~ ' ' ~ event.location.city,
    event.location.country
]|filter(v => v)|join(', ') %}

<p>{{ address }}</p>
```

### Karten-Links generieren

#### Google Maps
```html
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
```html
{% if event.location.latitude and event.location.longitude %}
    <a href="https://www.openstreetmap.org/?mlat={{ event.location.latitude }}&mlon={{ event.location.longitude }}&zoom=15" 
       target="_blank">
        In OpenStreetMap öffnen
    </a>
{% endif %}
```

### Verwendung im Template

```html
{% if location.email %}
    <a href="mailto:{{ location.email }}">{{ location.email }}</a>
{% endif %}

{% if location.phoneNumber %}
    <a href="tel:{{ location.phoneNumber }}">{{ location.phoneNumber }}</a>
{% endif %}

{% if location.link %}
    <a href="{{ location.link.href }}" 
       {% if location.link.target %}target="{{ location.link.target }}"{% endif %}>
        {{ location.link.title ?: 'Website' }}
    </a>
{% endif %}

{% if location.pdf %}
    <a href="{{ location.pdf.url }}" target="_blank">
        Download PDF
    </a>
{% endif %}

{% if location.latitude and location.longitude %}
    {# Map integration #}
    <div data-lat="{{ location.latitude }}" 
         data-lng="{{ location.longitude }}">
    </div>
{% endif %}

{% if location.images %}
    <div class="location-gallery">
        {% for image in location.images %}
            <img src="{{ image.thumbnails['300x300'] }}" alt="{{ image.title }}">
        {% endfor %}
    </div>
{% endif %}
```

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

```javascript
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
