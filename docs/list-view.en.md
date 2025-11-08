# List view

There are several options in the list, which can be accessed via Events.

### 1. List transformer

![img.png](img/list-transformer.de.png)

Configuration here: [List transformer](docs/list-transformer.en.md) - Type transformer for lists

### 2. Overwriting the values for startDate and endDate

The usual values for startDate and endDate are as follows:

```xml
<property name="startDate" visibility="no" translation="sulu_event.start_date" type="datetime">
    <field-name>startDate</field-name>
    <entity-name>%sulu.model.event.class%</entity-name>
</property>

<property name="endDate" visibility="no" translation="sulu_event.end_date" type="datetime">
    <field-name>endDate</field-name>
    <entity-name>%sulu.model.event.class%</entity-name>
</property>
```

**IMPORTANT:** These are overwritten in the default configuration (events.xml) of this bundle to allow for greater flexibility.
If the above values are required, the Resouces/config/lists/events.xml file contained in the bundle can be copied to config/lists/events.xml and adjusted accordingly.

In the SuluEventBundle, the xml has been adjusted so that the date values can be adjusted:
The first two properties are necessary so that startDate/endDate can be processed correctly.

### Configuration

The configuration can be adjusted as follows:

```yaml
# config/packages/sulu_event.yaml
sulu_event:
    list_date_format: “time_labels”  # “time_labels”, “clock_format”, “default”
```

Options for list_date_format:

*default*: Date; if the time is 00:00, it is not displayed

![img.png](img/list_date_format.de.png)

*time_labels*: Labels such as morning, afternoon

![img.png](img/time_labels.de.png)

*clock_format*: the date is displayed in the first field, the time(s) in the second field

![img.png](img/clock_format.de.png)

Date formatting

The format of date information is handled by locale strings.
Default:

````yaml
sulu_event:
    # Formats
    date_format: 'm/d/Y'
    datetime_format: 'm/d/Y h:i A'
````
