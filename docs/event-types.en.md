# Event Types - Configuration Flow

The event type configuration includes the name and colour of the type.

### 1. Bundle

**File:** `Resources/config/packages/sulu_event_bundle.yaml`

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

### 2. Types can be overwritten

**File:** `config/packages/sulu_event.yaml` (Created in project)

```yaml
sulu_event:
    types:
        vip:
            name: 'app.event_type.vip'
            color: '#ffd700'
        default:
            name: 'app.event_type.default'
            color: '#0d6efd'
    default_type: 'default'
```

**IMPORTANT:** Your own types override bundle defaults!
- **No merging!** (by design)

## Usage

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

Bundle provides translations for default types:

```yaml
# Resources/translations/admin.de.yaml
sulu_event:
    type:
        default: Default
        conference: Conference
        workshop: Workshop
        # ...
```

Custom types must be translated:

```yaml
# translations/admin.de.yaml
app:
    event_type:
        vip: VIP Event
        intern: Internes Event
```
