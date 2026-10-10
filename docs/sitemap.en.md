# Sitemap Integration

The SuluEventBundle automatically integrates with the Sulu Sitemap. Events are listed in the sitemap provided they are published and not explicitly hidden.

## How it works

The `EventSitemapProvider` collects all events that:
- Are published
- Match the current webspace language
- Are not hidden in the SEO tab ("Hide in sitemap" not active)

URLs are generated based on the routing.

## Configuration

No special configuration is required as long as the bundle is correctly registered. The `EventSitemapProvider` is registered as a service with the `sulu.sitemap.provider` tag.

### Leaving out past events

By default every published event stays in the sitemap. To drop events that ended some time ago (cancelled or postponed events of the future stay in):

```yaml
# config/packages/sulu_event.yaml
sulu_event:
    sitemap:
        hide_past_after_days: 90   # null (default): keep all
```

An event that ended more than 90 days ago is then no longer listed. Events without a date are always listed. The event page itself stays online.

### Hiding Events

To exclude a single event from the sitemap:
1. Open the event in the admin area.
2. Go to the **SEO** tab.
3. Check the **Hide in sitemap** checkbox.
4. Save and publish the event.

## Verification

The sitemap can be accessed at `/sitemap.xml` (depending on configuration). The event URLs should appear there, e.g.:
```xml
<url>
    <loc>https://example.org/events/my-great-event</loc>
    <lastmod>2024-03-20T10:00:00+00:00</lastmod>
</url>
```
