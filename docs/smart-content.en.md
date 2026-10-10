# Smart content: filtering on the website

[← Back to README](../README.md)

The `events` smart content provider evaluates categories and tags that a website visitor picks, next to the ones saved in the smart content field itself. The provider reads them from the query string, so a filter is a plain link or a form with `GET`:

| Parameter | Meaning | Example |
|---|---|---|
| `categories` | Category ids, comma separated | `?categories=9,12` |
| `tags` | Tag names, comma separated | `?tags=Workshop,Online` |

- Several values match with **OR** by default (an event needs one of them). Set `website_categories_operator` / `website_tags_operator` to `AND` in the params of the smart content field to require all of them.
- The visitor's choice narrows down the result of the field; categories and tags set in the admin still apply.
- Paging (`max_per_page`) and the total number of the smart content view (`view.total`) refer to the filtered result.
- The names of the parameters can be changed with `categories_parameter` and `tags_parameter` in the params of the field.

```xml
<property name="events" type="smart_content">
    <params>
        <param name="provider" value="events"/>
        <param name="max_per_page" value="12"/>
        <param name="website_tags_operator" value="AND"/>
    </params>
</property>
```

Which categories and tags to offer is up to the template: only events with an excerpt carry them, and only the live version counts. The helper [`sulu_event_filter_options`](#filter-options-for-a-filter-bar) lists what there is.

## More filters: participation, country, free of charge

| Parameter | Meaning | Example |
|---|---|---|
| `attendanceMode` | `offline`, `online`, `mixed`, comma separated. Exact values: `online` does not include `mixed` | `?attendanceMode=online,mixed` |
| `country` | Country codes (ISO 3166-1 alpha-2) of the location, comma separated | `?country=DE,AT` |
| `free` | `1`, `true`, `yes` or `on`: only events marked as free of charge | `?free=1` |

- Several values of one kind match with **OR**, the kinds with **AND** (and with categories and tags). A list without a usable value (`?attendanceMode=hybrid`) matches nothing.
- The names of the parameters can be changed with `attendance_mode_parameter`, `country_parameter` and `free_parameter` in the params of the field.
- In the admin, the same filters are available in the list of "types" of the smart content field: next to *pending*, *expired* and the event types there are "Participation: On site / Online / On site and online", "Free of charge" and "Country: …" (the countries of the locations in use). They are fixed filters of the field and combine with the event types (OR within a kind, AND between the kinds).

## Filter options for a filter bar

`sulu_event_filter_options(locale)` returns the values a filter bar can offer, each with the number of **upcoming, published** events of the locale (values without such events are left out):

```twig
{% set options = sulu_event_filter_options(app.request.locale) %}

{% for topic in options.topics %}
    <a href="?categories={{ topic.id }}">{{ topic.name }} ({{ topic.count }})</a>
{% endfor %}
{% for type in options.types %}{{ type.name }} {{ type.color }} ({{ type.count }}){% endfor %}
{% for country in options.countries %}<a href="?country={{ country.code }}">{{ country.name }}</a>{% endfor %}
{% for mode in options.attendanceModes %}<a href="?attendanceMode={{ mode.key }}">{{ mode.name }}</a>{% endfor %}
{% if options.free %}<a href="?free=1">Free ({{ options.free.count }})</a>{% endif %}
```

| Key | Entries |
|---|---|
| `topics` | `id`, `key`, `name` (in the locale, otherwise the default locale of the category), `parent` (id or `null`), `depth`, `count`; in tree order |
| `types` | `key`, `name`, `color`, `count` |
| `countries` | `code`, `name` (in the locale), `count`, sorted by name |
| `attendanceModes` | `key`, `name`, `count` |
| `free` | `['count' => n]` or `null` if no upcoming event is free |

Topics are categories. Set the key of a root category and only its descendants are listed:

```yaml
# config/packages/sulu_event.yaml
sulu_event:
    category_root: 'event-topics'
```

Without the option every category in use is listed; with an unknown key no topics. The selection of categories in the **admin** (excerpt tab) is a generic field of Sulu and is not restricted by this option.

The result is cached per locale (one hour, in `cache.app.taggable` if it exists) and cleared whenever an event is created, changed, published, unpublished, copied, removed or restored.
