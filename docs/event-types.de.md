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

**Datei:** `config/packages/sulu_event.yaml` (Eigenes Projekt)

```yaml
sulu_event:
    types:
        vip:
            name: 'app.event_type.vip'
            color: '#ffd700'
        internal:
            name: 'app.event_type.intern'
            color: '#0d6efd'
    default_type: 'standard'
```

**WICHTIG:** Eigene Types überschreiben alle Bundle-Types!
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

Bundle liefert Übersetzungen für die Standard-Types:

```yaml
# Resources/translations/admin.de.yaml
sulu_event:
    type:
        default: Standard
        conference: Konferenz
        workshop: Workshop
        # ...
```

Eigene Types müssen übersetzt werden:

```yaml
# translations/admin.de.yaml
app:
    event_type:
        vip: VIP Event
        intern: Internes Event
```
