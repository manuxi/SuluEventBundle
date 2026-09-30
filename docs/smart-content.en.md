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

Which categories and tags to offer is up to the template: only events with an excerpt carry them, and only the live version counts.
