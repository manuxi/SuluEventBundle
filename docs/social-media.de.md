# Social Media Sharing

## Übersicht

Das Social-Media-Feature ermöglicht es Besuchern, Events auf verschiedenen Social-Media-Plattformen zu teilen, und bietet Veranstaltern Tools zur Konfiguration von Social-Media-Einstellungen pro Event.

## Konfiguration

Social-Media-Einstellungen können für jedes Event individuell im Tab **Social Media** des Event-Editors konfiguriert werden.

### Verfügbare Optionen

#### Sharing-Einstellungen
- **Social Media Sharing aktivieren**: Sharing für dieses Event aktivieren/deaktivieren
- **Plattformen**: Auswahl, welche Plattformen zum Teilen verfügbar sein sollen
    - Facebook
    - Twitter/X
    - LinkedIn
    - Instagram
    - WhatsApp
    - E-Mail

#### Social-Media-Profile
Verknüpfen Sie Ihre Social-Media-Profile für Event-Promotions:
- **Facebook-URL**: URL Ihrer Facebook-Seite oder -Profils
- **Twitter-Handle**: Ihr Twitter/X-Benutzername (ohne @)
- **Instagram-URL**: URL Ihres Instagram-Profils
- **LinkedIn-URL**: URL Ihres LinkedIn-Unternehmens oder -Profils

#### Benutzerdefinierter Content
- **Individueller Share-Text**: Standard-Share-Text mit eigenem Content überschreiben
- **Zielgruppen**: Definieren Sie spezifische Zielgruppen für dieses Event

## Verwendung in Templates

### Share-Buttons anzeigen

```twig
{% if event.socialSettings and event.socialSettings.enableSharing %}
    <div class="social-share">
        {% for platform in event.socialSettings.platforms %}
            <a href="{{ social_share_url(event, platform) }}" 
               class="share-{{ platform }}"
               target="_blank"
               rel="noopener noreferrer">
                Auf {{ platform|capitalize }} teilen
            </a>
        {% endfor %}
    </div>
{% endif %}
```

### Twig-Funktionen

Das Bundle stellt folgende Twig-Funktionen bereit:

#### `social_share_url(event, platform)`

Generiert eine Sharing-URL für die angegebene Plattform.

```twig
{{ social_share_url(event, 'facebook') }}
```

**Unterstützte Plattformen:**
- `facebook` - Auf Facebook teilen
- `twitter` - Auf Twitter/X teilen
- `linkedin` - Auf LinkedIn teilen
- `whatsapp` - Via WhatsApp teilen
- `email` - Via E-Mail teilen

#### `social_profile_url(event, platform)`

Gibt die Social-Media-Profil-URL für das Event zurück.

```twig
{% if social_profile_url(event, 'instagram') %}
    <a href="{{ social_profile_url(event, 'instagram') }}">
        Folgen Sie uns auf Instagram
    </a>
{% endif %}
```

### Vollständiges Beispiel

```twig
<article class="event-detail">
    <h1>{{ event.title }}</h1>
    
    {% if event.socialSettings and event.socialSettings.enableSharing %}
        <div class="social-sharing">
            <h3>Event teilen</h3>
            
            <div class="share-buttons">
                {% if event.socialSettings.isPlatformEnabled('facebook') %}
                    <a href="{{ social_share_url(event, 'facebook') }}" 
                       class="btn btn-facebook"
                       target="_blank">
                        <i class="fab fa-facebook"></i> Teilen
                    </a>
                {% endif %}
                
                {% if event.socialSettings.isPlatformEnabled('twitter') %}
                    <a href="{{ social_share_url(event, 'twitter') }}" 
                       class="btn btn-twitter"
                       target="_blank">
                        <i class="fab fa-twitter"></i> Tweeten
                    </a>
                {% endif %}
                
                {% if event.socialSettings.isPlatformEnabled('linkedin') %}
                    <a href="{{ social_share_url(event, 'linkedin') }}" 
                       class="btn btn-linkedin"
                       target="_blank">
                        <i class="fab fa-linkedin"></i> Teilen
                    </a>
                {% endif %}
                
                {% if event.socialSettings.isPlatformEnabled('whatsapp') %}
                    <a href="{{ social_share_url(event, 'whatsapp') }}" 
                       class="btn btn-whatsapp"
                       target="_blank">
                        <i class="fab fa-whatsapp"></i> Senden
                    </a>
                {% endif %}
                
                {% if event.socialSettings.isPlatformEnabled('email') %}
                    <a href="{{ social_share_url(event, 'email') }}" 
                       class="btn btn-email">
                        <i class="fas fa-envelope"></i> E-Mail
                    </a>
                {% endif %}
            </div>
        </div>
        
        <div class="social-profiles">
            <h3>Folgen Sie uns</h3>
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

## Share-URL-Formate

Die generierten URLs folgen Standard-Social-Media-Sharing-Formaten:

- **Facebook**: `https://www.facebook.com/sharer/sharer.php?u={url}`
- **Twitter**: `https://twitter.com/intent/tweet?text={text}&url={url}`
- **LinkedIn**: `https://www.linkedin.com/sharing/share-offsite/?url={url}`
- **WhatsApp**: `https://wa.me/?text={text}%20{url}`
- **E-Mail**: `mailto:?subject={subject}&body={body}`

## Individueller Share-Text

Wenn ein individueller Share-Text konfiguriert ist, wird dieser anstelle des Standard-Titels und der Beschreibung des Events verwendet. Dies ermöglicht:
- Optimierte Social-Media-Nachrichten
- Verwendung von Hashtags
- Call-to-Action-Phrasen
- Plattformspezifische Formatierung

## Meta-Tags

Für optimales Social-Media-Sharing sollte Ihr Event-Detail-Template Open-Graph- und Twitter-Card-Meta-Tags enthalten:

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