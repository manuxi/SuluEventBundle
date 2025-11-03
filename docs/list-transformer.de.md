# List-Transformer

## Übersicht

Das SuluEventBundle enthält einen Transformer zur hübscheren Listenansicht der Types.
Er zeigt in der Liste einen Indikator mit der jeweiligen Typ-Farbe.

![img.png](img/list-transformer.de.png)

## Installation

Der Transformer muss manuell hinzugefügt werden.

Dazu unter assets/admin
1. in der package.json hinzufügen:
```javascript
"sulu-event-bundle": "../../vendor/manuxi/sulu-event-bundle/src/Resources"
```

2. in der assets/admin/app.js hinzufügen:
```javascript
import listFieldTransformerRegistry from 'sulu-admin-bundle/containers/List/registries/listFieldTransformerRegistry';
import {EventTypeColorFieldTransformer} from 'sulu-event-bundle/js';

listFieldTransformerRegistry.add('event_type_color', new EventTypeColorFieldTransformer());
```

3. unter assets/admin/ installieren und bauen:
```bash
npm install
npm run build
```

4. xml ins Projekt kopiert werden: 
```bash
cp src/Resources/config/lists/events.xml => [Projekt]/config/lists/events.xml
```

5. Dann folgendes zur kopierten events.xml hinzufügen:
```xml
<property name="type" visibility="always" translation="sulu_event.type" sortable="true">
    <field-name>type</field-name>
    <entity-name>%sulu.model.event.class%</entity-name>

    <transformer type="event_type_color" />
</property>
```
