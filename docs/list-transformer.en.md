# List-Transformer

## Overview

The SuluEventBundle contains a transformer for a more attractive list view of the types. It displays the respective colours of the type in the list.

![img.png](img/list-transformer.de.png)

## Installation

The transformer must be added manually.

To do this, go to assets/admin
1. Add the following to package.json:

```javascript
"sulu-event-bundle": "../../vendor/manuxi/sulu-event-bundle/src/Resources"
```

2. Add to assets/admin/app.js:
```javascript
import listFieldTransformerRegistry from 'sulu-admin-bundle/containers/List/registries/listFieldTransformerRegistry';
import {EventTypeColorFieldTransformer} from 'sulu-event-bundle/js';

listFieldTransformerRegistry.add('event_type_color', new EventTypeColorFieldTransformer());
```

3. Install and build under assets/admin/:
```bash
npm install
npm run build
```

4. Copy xml to project: 
```bash
cp src/Resources/config/lists/events.xml => [Projekt]/config/lists/events.xml
```

5. Then add the following to the copied events.xml:
```xml
<property name="type" visibility="always" translation="sulu_event.type" sortable="true">
    <field-name>type</field-name>
    <entity-name>%sulu.model.event.class%</entity-name>

    <transformer type="event_type_color" />
</property>
```
