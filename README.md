# SuluEventBundle!
This bundle was made based on pr of https://github.com/sulu/sulu-workshop. 
I made it to quickly install those entities in my projects.
This bundle is still in development. Use at own risk :)

## Installation
Install the package with:
```console
composer require manuxi/sulu-event-bundle
```
If you're *not* using Symfony Flex, you'll also
need to add the bundle in your `config/bundles.php` file:
```php
Manuxi\SuluEventBundle\SuluEventBundle::class => ['all' => true],
```
Please add the following to your `routes_admin.yaml`:
```yaml
SuluEventBundle:
    resource: '@SuluEventBundle/Resources/config/routes_admin.yml'
```
...and the following to your `routes_website.yaml`:
```yaml
SuluEventBundle:
    resource: "@SuluEventBundle/Resources/config/routes.yml"
```
Last but not least the entity tables must be created in the database.
The tables which will be created are 
```
app_location, app_event_translation, app_event_seo_translation, app_event_seo and app_event
```
See the needed queries with:
```console
php bin/console doctrine:schema:update --dump-sql
```
Execute the queries above with:
```console
php bin/console doctrine:schema:update --force
```

## Usage
After granting the rights in the sulu admin area you should be able to create locations and events.
use smart_content property type to show a list of events, e.g.:
```xml
...
<block name="smart-content-events" default-type="smart-content-events">
    <types>
        <type name="smart-content-events">
            <properties>
                <property name="events" type="smart_content">
                    <meta>
                        <title lang="en">Events</title>
                        <title lang="de">Veranstaltungen</title>
                    </meta>

                    <params>
                        <param name="provider" value="events"/>
                        <param name="max_per_page" value="5"/>
                        <param name="page_parameter" value="page"/>
                    </params>
                </property>
            </properties>
        </type>
    </types>
</block>
...
```
Example of the corresponding twig template for the event list:
```html
{% for event in events %}
    <div class="col">
        <h2>
            {{ event.title }}
        </h2>
        <p>
            {{ event.startDate|format_datetime('full', 'none', locale=app.request.getLocale()) }}
            {% if endDate and startDate != endDate %}
                 - {{ event.endDate|format_datetime('full', 'none', locale=app.request.getLocale()) }}
            {% endif %}
        </p>
        <p>
            {{ event.teaser|raw }}
        </p>
        <p>
            <a class="btn btn-primary" href="{{ path('event', {id: event.id, slug: event.title|slugify}) }}" role="button">
                {{ "Read more..."|trans }} <i class="fa fa-angle-double-right"></i>
            </a>
        </p>
    </div>
{% endfor %}
```
The route to the single event follows the scheme:
```
    "en": "/events/{id}/{slug}",
    "de": "/veranstaltungen/{id}/{slug}"
```
At the moment the template for the single event is located here
`templates/pages/event.html.twig`

## Configuration
There exists no configuration yet. I'm on it :)

## Contributing
For the sake of simplicity this extension was kept small.
Please feel comfortable submitting issues or pull requests. As always I'd be glad to get your feedback to improve the extension :).
