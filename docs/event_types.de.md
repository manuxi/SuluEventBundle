# Event Types - Configuration Flow

Die Event-Types-Konfiguration beinhaltet den Namen und die Farbe des Types.

### 1. Bundle

**Datei:** `Resources/config/packages/sulu_event_bundle.yaml`

```yaml
sulu_event:
    types:
        default:
            name: 'sulu_event.type.default'
            color: '#0d6efd'
        conference:
            name: 'sulu_event.type.conference'
            color: '#6610f2'
        # ... 8 weitere
    default_type: 'default'
```

### 2. Types können überschrieben werden

**Datei:** `config/packages/sulu_event.yaml` (User erstellt)

```yaml
sulu_event:
    types:
        vip:
            name: 'app.event_type.vip'
            color: '#ffd700'
        standard:
            name: 'app.event_type.standard'
            color: '#0d6efd'
    default_type: 'standard'
```

**WICHTIG:** Eigene Types überschreiben Bundle-Defaults!
- **Kein Merge!** (by design)

## Verwendung im Code

### Admin Form (event_details.xml)

```xml
<property name="type" type="single_select">
    <params>
        <param name="default_value"
               type="expression"
               value="service('sulu_event.type_selection').getDefaultValue()"
        />
        <param name="values"
               type="expression"
               value="service('sulu_event.type_selection').getValues()"
        />
    </params>
</property>
```

### Twig Template

```twig
{# Get color for event type #}
{% set color = sulu_event_type_color(event.type) %}

{# Get translated name #}
{% set typeName = sulu_event_type_name(event.type) %}
```

## Translations

Bundle liefert Übersetzungen für Standard-Types:

```yaml
# Resources/translations/admin.de.yaml
sulu_event:
    type:
        default: Standard
        conference: Konferenz
        workshop: Workshop
        # ...
```

User muss eigene Types übersetzen:

```yaml
# translations/admin.de.yaml
app:
    event_type:
        vip: VIP Event
        intern: Internes Event
```

## Debug-Commands

```bash
# Parameter anzeigen
php bin/console debug:container --parameters | grep sulu_event

# Service-Definition anzeigen
php bin/console debug:container sulu_event.type_selection

# Config-Tree anzeigen
php bin/console config:dump-reference sulu_event
```
