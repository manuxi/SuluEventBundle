# Locations

## Overview

The location system allows you to manage and reuse event locations. Locations are managed separately from events and can be assigned to multiple events.

## Managing Locations

Locations are managed in the Sulu admin panel under **Events > Locations**.

### Location Properties

![img.png](img/locations.en.png)

- **Name**: Display name of the location
- **Street**: Street address
- **Number**: House/building number
- **Postal Code**: ZIP/postal code
- **City**: City name
- **Country**: Country name
- **Latitude**: Geographic latitude (for maps)
- **Longitude**: Geographic longitude (for maps)
- **Notes**: Additional information or instructions

## Assigning Locations to Events

When creating or editing an event, you can select a location from the **Location** field. This is a single-selection field that references existing location entries.

## Usage in Templates

### Display Location Information

```html
{% if event.location %}
    <div class="event-location">
        <h3>Location</h3>
        
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

### Display Map

If latitude and longitude are set:

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

### Get Formatted Address

```html
{% set address = [
    event.location.street ~ ' ' ~ event.location.number,
    event.location.postalCode ~ ' ' ~ event.location.city,
    event.location.country
]|filter(v => v)|join(', ') %}

<p>{{ address }}</p>
```

### Generate Map Links

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
        Open in Google Maps
    </a>
{% endif %}
```

#### OpenStreetMap
```html
{% if event.location.latitude and event.location.longitude %}
    <a href="https://www.openstreetmap.org/?mlat={{ event.location.latitude }}&mlon={{ event.location.longitude }}&zoom=15" 
       target="_blank">
        Open in OpenStreetMap
    </a>
{% endif %}
```

### Template usage

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

## Geocoding

The bundle does not include automatic geocoding. You can:

1. **Manual entry**: Enter latitude/longitude manually
2. **External service**: Use a geocoding service (Google Maps, OpenStreetMap Nominatim)
3. **Custom implementation**: Add geocoding via event listeners or custom commands

### Example: JavaScript Geocoding

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

## Schema.org Integration

For proper event markup, include location data:

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

