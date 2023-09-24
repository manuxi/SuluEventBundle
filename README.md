# SuluEventBundle!
<div>
    <a href="https://www.travis-ci.com/manuxi/SuluEventBundle" target="_blank">
        <img src="https://www.travis-ci.com/manuxi/SuluEventBundle.svg?branch=main" alt="Travis status">
    </a>
    <a href="https://github.com/manuxi/SuluEventBundle/blob/main/LICENSE" target="_blank">
        <img src="https://img.shields.io/github/license/manuxi/SuluEventBundle" alt="GitHub license">
    </a>
    <a href="https://github.com/manuxi/SuluEventBundle/tags" target="_blank">
        <img src="https://img.shields.io/github/v/tag/manuxi/SuluEventBundle" alt="GitHub license">
    </a>

</div>

This bundle was made based on [Sulu workshop](https://github.com/sulu/sulu-workshop). 
I made it to have the possibility to manage events in my projects and to get a better knowledge of sulu. 
The events and their meta information is translatable. Please feel comfortable submitting feature requests. 
This bundle is still in development. Use at own risk 🤞🏻


## 👩🏻‍🏭 Installation
Install the package with:
```console
composer require manuxi/sulu-event-bundle
```
If you're *not* using Symfony Flex, you'll also
need to add the bundle in your `config/bundles.php` file:
```php
return [
    //...
    Manuxi\SuluEventBundle\SuluEventBundle::class => ['all' => true],
];
```
Please add the following to your `routes_admin.yaml`:
```yaml
SuluEventBundle:
    resource: '@SuluEventBundle/Resources/config/routes_admin.yml'
```
Last but not least the schema of the database needs to be updated.  

Some tables will be created (prefixed):  
location, event, event_seo, event_excerpt
(plus some ManyToMany relation tables).  

See the needed queries with `php bin/console doctrine:schema:update --dump-sql`.  
Update the schema by executing `php bin/console doctrine:schema:update --force`.  

Make sure you only process the bundles schema updates!

## 🎣 Usage
First: Grant permissions for events. 
After page reload you should see the event item in the navigation. 
Start to create locations, then events.
Use smart_content property type to show a list of events, e.g.:
```xml
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
            {{ event.summary|raw }}
        </p>
        <p>
            {{ event.text|raw }}
        </p>
        <p>
            {{ event.footer|raw }}
        </p>
        <p>
            <a class="btn btn-primary" href="{{ event.routePath }}" role="button">
                {{ "Read more..."|trans }} <i class="fa fa-angle-double-right"></i>
            </a>
        </p>
    </div>
{% endfor %}
```
The template for the single event is located here
`templates/pages/event.html.twig`.
Since the seo and excerpt tabs are available in the event editor, 
meta information can be provided like it's done as usual when rendering your pages. 

## 🧶 Configuration
There exists no configuration yet. I'm on it :)

## 👩‍🍳 Contributing
For the sake of simplicity this extension was kept small.
Please feel comfortable submitting issues or pull requests. As always I'd be glad to get your feedback to improve the extension :).
