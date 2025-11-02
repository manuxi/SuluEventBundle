# Social Media Sharing

## Overview

The social media feature allows visitors to share events across various social platforms and provides event organizers with tools to configure social media settings per event.

## Configuration

Social settings can be configured for each event individually in the **Social Media** tab of the event editor.

### Available Options

#### Sharing Settings
- **Enable Social Media Sharing**: Activate/deactivate sharing for this event
- **Platforms**: Select which platforms should be available for sharing
    - Facebook
    - Twitter/X
    - LinkedIn
    - Instagram
    - WhatsApp
    - Email

#### Social Media Profiles
Link your social media profiles to be included in event promotions:
- **Facebook URL**: Your Facebook page or profile URL
- **Twitter Handle**: Your Twitter/X username (without @)
- **Instagram URL**: Your Instagram profile URL
- **LinkedIn URL**: Your LinkedIn company or profile URL

#### Custom Content
- **Custom Share Text**: Override the default share text with custom content
- **Target Groups**: Define specific target audiences for this event

## Usage in Templates

### Display Share Buttons

```twig
{% if event.socialSettings and event.socialSettings.enableSharing %}
    <div class="social-share">
        {% for platform in event.socialSettings.platforms %}
            <a href="{{ social_share_url(event, platform) }}" 
               class="share-{{ platform }}"
               target="_blank"
               rel="noopener noreferrer">
                Share on {{ platform|capitalize }}
            </a>
        {% endfor %}
    </div>
{% endif %}
```

### Twig Functions

The bundle provides the following Twig functions:

#### `social_share_url(event, platform)`

Generates a sharing URL for the specified platform.

```twig
{{ social_share_url(event, 'facebook') }}
```

**Supported platforms:**
- `facebook` - Share on Facebook
- `twitter` - Share on Twitter/X
- `linkedin` - Share on LinkedIn
- `whatsapp` - Share via WhatsApp
- `email` - Share via Email

#### `social_profile_url(event, platform)`

Returns the social media profile URL for the event.

```twig
{% if social_profile_url(event, 'instagram') %}
    <a href="{{ social_profile_url(event, 'instagram') }}">
        Follow us on Instagram
    </a>
{% endif %}
```

### Complete Example

```twig
<article class="event-detail">
    <h1>{{ event.title }}</h1>
    
    {% if event.socialSettings and event.socialSettings.enableSharing %}
        <div class="social-sharing">
            <h3>Share this Event</h3>
            
            <div class="share-buttons">
                {% if event.socialSettings.isPlatformEnabled('facebook') %}
                    <a href="{{ social_share_url(event, 'facebook') }}" 
                       class="btn btn-facebook"
                       target="_blank">
                        <i class="fab fa-facebook"></i> Share
                    </a>
                {% endif %}
                
                {% if event.socialSettings.isPlatformEnabled('twitter') %}
                    <a href="{{ social_share_url(event, 'twitter') }}" 
                       class="btn btn-twitter"
                       target="_blank">
                        <i class="fab fa-twitter"></i> Tweet
                    </a>
                {% endif %}
                
                {% if event.socialSettings.isPlatformEnabled('linkedin') %}
                    <a href="{{ social_share_url(event, 'linkedin') }}" 
                       class="btn btn-linkedin"
                       target="_blank">
                        <i class="fab fa-linkedin"></i> Share
                    </a>
                {% endif %}
                
                {% if event.socialSettings.isPlatformEnabled('whatsapp') %}
                    <a href="{{ social_share_url(event, 'whatsapp') }}" 
                       class="btn btn-whatsapp"
                       target="_blank">
                        <i class="fab fa-whatsapp"></i> Send
                    </a>
                {% endif %}
                
                {% if event.socialSettings.isPlatformEnabled('email') %}
                    <a href="{{ social_share_url(event, 'email') }}" 
                       class="btn btn-email">
                        <i class="fas fa-envelope"></i> Email
                    </a>
                {% endif %}
            </div>
        </div>
        
        <div class="social-profiles">
            <h3>Follow Us</h3>
            {% if social_profile_url(event, 'facebook') %}
                <a href="{{ social_profile_url(event, 'facebook') }}">Facebook</a>
            {% endif %}
            {% if social_profile_url(event, 'instagram') %}
                <a href="{{ social_profile_url(event, 'instagram') }}">Instagram</a>
            {% endif %}
        </div>
    {% endif %}
</article>
```

## Share URL Formats

The generated URLs follow standard social media sharing formats:

- **Facebook**: `https://www.facebook.com/sharer/sharer.php?u={url}`
- **Twitter**: `https://twitter.com/intent/tweet?text={text}&url={url}`
- **LinkedIn**: `https://www.linkedin.com/sharing/share-offsite/?url={url}`
- **WhatsApp**: `https://wa.me/?text={text}%20{url}`
- **Email**: `mailto:?subject={subject}&body={body}`

## Custom Share Text

If a custom share text is configured, it will be used instead of the event's default title and description. This allows for:
- Optimized social media messaging
- Hashtag inclusion
- Call-to-action phrases
- Platform-specific formatting

## Meta Tags

For optimal social media sharing, ensure your event detail template includes Open Graph and Twitter Card meta tags:

```twig
<meta property="og:title" content="{{ event.title }}" />
<meta property="og:description" content="{{ event.summary }}" />
<meta property="og:url" content="{{ app.request.schemeAndHttpHost ~ event.routePath }}" />
<meta property="og:image" content="{{ event.image.url }}" />
<meta property="og:type" content="event" />

<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ event.title }}" />
<meta name="twitter:description" content="{{ event.summary }}" />
<meta name="twitter:image" content="{{ event.image.url }}" />
```