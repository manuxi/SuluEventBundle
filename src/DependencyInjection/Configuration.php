<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\DependencyInjection;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Repository\EventDimensionContentRepository;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sulu_event');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
            ->arrayNode('routing')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('route_schema')
                        //->defaultValue('/{translator.trans("sulu_event.events")}/{object.getTitle()}')
                        ->defaultValue('/{parent}/{object.getTitle()}')
                    ->end()
                ->end()
            ->end()

            ->arrayNode('types')
                ->useAttributeAsKey('key')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('name')->isRequired()->end()
                        ->scalarNode('color')->isRequired()->end()
                    ->end()
                ->end()
                ->defaultValue([
                    'default' => [
                        'name' => 'Default',
                        'color' => '#cccccc',
                    ],
                ])
            ->end()
            ->scalarNode('default_type')
                ->defaultValue('default')
            ->end()

            ->scalarNode('list_date_format')
                ->defaultValue('clock_format')
                ->info('Format for date display in list view: "default", "clock_format", "time_labels"')
            ->end()

            ->scalarNode('category_root')
                ->defaultNull()
                ->info('Key of the root category of the event topics. The filter options (sulu_event_filter_options) list only its descendants; the category selection in the admin is not restricted.')
            ->end()

            ->scalarNode('featured_tag')
                ->defaultNull()
                ->info('Name of the tag that marks an event as a highlight. Default: null (off). With it the calendar API, the feeds, the smart content and the filter options offer "highlights only" and events carry "featured".')
                ->validate()
                    ->ifTrue(static fn ($value): bool => null !== $value && (!\is_string($value) || '' === trim($value)))
                    ->thenInvalid('sulu_event.featured_tag is the name of a tag (for example "Highlight") or null.')
                ->end()
            ->end()

            ->arrayNode('sitemap')
                ->addDefaultsIfNotSet()
                ->children()
                    ->integerNode('hide_past_after_days')
                        ->defaultNull()
                        ->min(0)
                        ->info('Events that ended more than this many days ago are left out of the sitemap. Default: null (all published events stay in).')
                    ->end()
                ->end()
            ->end()

            ->scalarNode('timezone')
                ->defaultNull()
                ->info('IANA timezone (e.g. "Europe/Berlin") the stored event times are meant in. Defaults to the PHP default timezone.')
                ->validate()
                    ->ifTrue(static fn ($value): bool => null !== $value && !\in_array($value, \DateTimeZone::listIdentifiers(), true))
                    ->thenInvalid('"%s" is not a valid IANA timezone.')
                ->end()
            ->end()

            ->append($this->createImportNode())

            ->arrayNode('objects')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('event')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(Event::class)->end()
                            ->scalarNode('repository')->defaultValue(EventRepository::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('event_dimension_content')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(EventDimensionContent::class)->end()
                            ->scalarNode('repository')->defaultValue(EventDimensionContentRepository::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('location')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(Location::class)->end()
                            ->scalarNode('repository')->defaultValue(LocationRepository::class)->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    private function createImportNode(): ArrayNodeDefinition
    {
        $node = (new TreeBuilder('import'))->getRootNode();
        $node
            ->addDefaultsIfNotSet()
            ->info('Import of events from iCal feeds and web pages with schema.org data (command "sulu:event:import"). Off by default.')
            ->validate()
                ->ifTrue(static fn (array $import): bool => $import['enabled'] && (null === $import['user'] || '' === $import['user']))
                ->thenInvalid('sulu_event.import.user is required when the import is enabled: the user name of a Sulu user (for example "event-import") who is shown as creator of the imported events.')
            ->end()
            ->children()
                ->booleanNode('enabled')
                    ->defaultFalse()
                    ->info('Switches the import on. When it is off the command only tells so.')
                ->end()
                ->scalarNode('user')
                    ->defaultNull()
                    ->info('User name of the Sulu user who is creator and changer of imported events.')
                ->end()
                ->scalarNode('user_agent')
                    ->defaultValue('SuluEventBundle-Import (+https://github.com/manuxi/SuluEventBundle)')
                    ->cannotBeEmpty()
                    ->info('Name the import introduces itself with to the sources. Put a contact (url or e-mail) in it, so site owners can reach you.')
                ->end()
                ->floatNode('timeout')
                    ->defaultValue(15.0)
                    ->min(1.0)
                    ->info('Seconds to wait for a source.')
                ->end()
                ->booleanNode('auto_update_published_dates')
                    ->defaultFalse()
                    ->info('A change of date, time or status of a published event goes live by itself (only if nothing else is waiting in its draft). Everything else always stays a draft.')
                ->end()
                ->scalarNode('url_prefix')
                    ->defaultValue('/events')
                    ->cannotBeEmpty()
                    ->info('Path the urls of imported events start with.')
                    ->validate()
                        ->ifTrue(static fn ($value): bool => !\is_string($value) || !str_starts_with($value, '/'))
                        ->thenInvalid('sulu_event.import.url_prefix must start with a slash, for example "/events".')
                    ->end()
                ->end()
                ->scalarNode('webspace')
                    ->defaultNull()
                    ->info('Key of the webspace imported events belong to. Default: the default webspace of Sulu.')
                ->end()
                ->arrayNode('category_images')
                    // keys are category keys such as "web-php": Symfony would turn the dash into an underscore and no image is found
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('key')
                    ->scalarPrototype()
                        ->validate()
                            ->ifTrue(static fn ($value): bool => !\is_int($value) && !(\is_string($value) && '' !== trim($value)))
                            ->thenInvalid('An image is the id of a media or the name of its file (for example "event-topic-ai.jpg").')
                        ->end()
                    ->end()
                    ->info('Images of your own for imported events: category key => id of a media or name of its file. An event without an image gets the one of its first category that is listed here. Images of the sources are never copied.')
                ->end()
                ->arrayNode('series_images')
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('key')
                    ->scalarPrototype()
                        ->validate()
                            ->ifTrue(static fn ($value): bool => !\is_int($value) && !(\is_string($value) && '' !== trim($value)))
                            ->thenInvalid('An image is the id of a media or the name of its file.')
                        ->end()
                    ->end()
                    ->info('A photo for the events of a series: series key (the "series" of a source, "ifa") => id of a media or name of its file. It wins over the photo of the place and the placeholder of the topic.')
                ->end()
                ->scalarNode('location_image')
                    ->defaultNull()
                    ->validate()
                        ->ifTrue(static fn ($value): bool => null !== $value && !\is_int($value) && !(\is_string($value) && '' !== trim($value)))
                        ->thenInvalid('An image is the id of a media or the name of its file (for example "event-venue.jpg").')
                    ->end()
                    ->info('Image of your own for the places the import creates (a place has no photo from a source): id of a media or name of its file. Places without an image get it with sulu:event:fill-images.')
                ->end()
                ->booleanNode('admin_button')
                    ->defaultFalse()
                    ->info('Shows the button "Import now" in the tab "Import" of the settings. It needs the JavaScript of the bundle in the admin build (see docs/import.en.md), so it is off by default.')
                ->end()
                ->arrayNode('location_aliases')
                    // the variants are names: no key normalization
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('variant')
                    ->scalarPrototype()->end()
                    ->info('Other spellings of a place or a city and the one that leads, for example "Brüssel: Brussels", "Köln: Cologne". The import finds and creates the place under the leading name, so a source that writes the city in German does not make a second place.')
                ->end()
                ->arrayNode('featured_sources')
                    ->scalarPrototype()->end()
                    ->info('Source keys whose events get the tag sulu_event.featured_tag (for example "confs-tech"), for sources of the project. Sources from the configuration use "featured: true" instead.')
                ->end()
                ->arrayNode('locales')
                    ->scalarPrototype()->end()
                    ->info('Locales an imported event should have a text in (for example ["de", "en"]). Events that lack one are listed as "needs work" (MCP tool sulu_event_import_pending).')
                ->end()
                ->append($this->createAutoPublishNode())
                ->append($this->createFiltersNode('filters', 'The sieve of the import: which candidates become events at all. Rules are empty by default, so everything passes; the bundle brings no lists of countries or regions, the project sets them. See docs/import.en.md ("Sieve").'))
                ->arrayNode('source_filters')
                    // keys are source keys such as "jsonld.my-page": no key normalization
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('source')
                    ->variablePrototype()->end()
                    ->info('Rules of the sieve for single sources, any source (also those of the project, for example "my-list"): source key => the same keys as under "filters". A rule set here replaces the global one for this source; what is not set stays as it is globally. Sources from the configuration take them in their own "filters" as well.')
                ->end()
                ->arrayNode('sources')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->append($this->createSourcesNode('ical', 'iCalendar feeds (.ics): Meetup groups, Luma calendars, conference calendars'))
                        ->append($this->createSourcesNode('jsonld', 'Web pages with schema.org events (JSON-LD)'))
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * The rules of the sieve. Every rule is a variable node: a rule that is not set must stay absent (for a source it
     * means "as globally"), and the content is checked by FilterRules when the container is built.
     */
    private function createFiltersNode(string $name, string $info, bool $global = true): ArrayNodeDefinition
    {
        $node = (new TreeBuilder($name))->getRootNode();
        $node
            ->info($info)
            ->children()
                ->variableNode('countries')
                    ->info('Countries (ISO 3166-1 alpha-2, "DE"): {include: [...], exclude: [...]}. With "include" only these pass; "exclude" is always out. A candidate without a country is decided by unknown_country.')
                ->end()
                ->variableNode('unknown_country')
                    ->info('"allow" (default) or "deny": what happens to a candidate with a place but no country.')
                ->end()
                ->variableNode('online')
                    ->info('"allow" (default) or "deny": events that take place online only, without a place.')
                ->end()
                ->variableNode('types')
                    ->info('Allowed event types (keys of sulu_event.types); empty = all.')
                ->end()
                ->variableNode('topics')
                    ->info('Allowed topics (category keys); one of them must match; empty = all.')
                ->end()
                ->variableNode('title_exclude')
                    ->info('Regular expressions (without delimiters, case does not matter, for example "^Webinar"): a title that matches is sieved out. Always applies, also to series.')
                ->end()
                ->variableNode('host_exclude')
                    ->info('Host names (for example "tickets.example.com" or "example.com" with all subdomains): events whose link points there are sieved out. Always applies, also to series.')
                ->end()
                ->variableNode('max_months_ahead')
                    ->info('Events that start later than this many months from now are not imported yet (default: no limit).')
                ->end()
                ->variableNode('series_bypass')
                    ->info('Rules that candidates with a series do not have to meet: countries, unknown_country, online, topics, types, max_months_ahead (default: countries, unknown_country, online).')
                ->end()
                ->variableNode('tags_allowed')
                    ->info('Not a rule of the sieve: the tags an event may get (names, case does not matter). The import and the MCP tools take over only these and name the others in the report; empty = all.')
                ->end()
            ->end();

        if ($global) {
            $node->addDefaultsIfNotSet();
        }

        return $node;
    }

    /**
     * Rules for publishing complete imported events without a click (command "sulu:event:import:publish-ready").
     */
    private function createAutoPublishNode(): ArrayNodeDefinition
    {
        $node = (new TreeBuilder('auto_publish'))->getRootNode();
        $node
            ->addDefaultsIfNotSet()
            ->info('Publishes imported events by itself once they are complete (command "sulu:event:import:publish-ready"). Off by default. See docs/import.en.md ("Publish automatically").')
            ->validate()
                ->ifTrue(static fn (array $autoPublish): bool => $autoPublish['enabled'] && !$autoPublish['when']['series'] && [] === $autoPublish['when']['sources'] && [] === $autoPublish['when']['types'])
                ->thenInvalid('sulu_event.import.auto_publish.when needs at least one condition when auto_publish is enabled: series: true, sources: [...] or types: [...]. Otherwise no event would qualify.')
            ->end()
            ->children()
                ->booleanNode('enabled')
                    ->defaultFalse()
                    ->info('Switches the automatic publishing on. Without it the command only tells so.')
                ->end()
                ->arrayNode('when')
                    ->addDefaultsIfNotSet()
                    ->info('Which events may be published by themselves. One matching condition is enough (OR). Events that none of them matches stay drafts.')
                    ->children()
                        ->booleanNode('series')
                            ->defaultFalse()
                            ->info('true: events that belong to a series (the source or the import knows a series or a match key) qualify.')
                        ->end()
                        ->arrayNode('sources')
                            ->scalarPrototype()->end()
                            ->info('Source keys (for example "ical.my-group", "jsonld.my-page", "confs-tech"): events of these sources qualify.')
                        ->end()
                        ->arrayNode('types')
                            ->scalarPrototype()->end()
                            ->info('Event type keys (see sulu_event.types): events of these types qualify.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('require')
                    ->addDefaultsIfNotSet()
                    ->info('What an event needs before it is published. Per language title, subtitle, summary and text must not be empty.')
                    ->children()
                        ->arrayNode('locales')
                            ->scalarPrototype()->end()
                            ->info('Languages that must be complete, for example ["de", "en"]; these are published, too. Empty = the languages the event has.')
                        ->end()
                        ->booleanNode('category')
                            ->defaultTrue()
                            ->info('The event needs at least one category (topic). Default: true.')
                        ->end()
                        ->booleanNode('image')
                            ->defaultTrue()
                            ->info('The event needs an image. Default: true.')
                        ->end()
                        ->booleanNode('place_or_online')
                            ->defaultTrue()
                            ->info('The event needs a location or a link to take part online. Default: true.')
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('republish_changes')
                    ->defaultFalse()
                    ->info('true: an event that is published and got a new draft (a change of the source) is published again if it still meets the rules and no field was locked by hand. Default: false, such changes wait for an editor.')
                ->end()
            ->end();

        return $node;
    }

    private function createSourcesNode(string $name, string $info): ArrayNodeDefinition
    {
        $node = (new TreeBuilder($name))->getRootNode();
        $node
            ->info($info)
            ->arrayPrototype()
                ->children()
                    ->scalarNode('key')
                        ->isRequired()
                        ->cannotBeEmpty()
                        ->info('Short name of the source, used in reports and as part of the id of its events (letters, digits, "-" and "_").')
                        ->validate()
                            ->ifTrue(static fn ($value): bool => !\is_string($value) || 1 !== preg_match('/^[a-z0-9][a-z0-9_-]*$/', $value))
                            ->thenInvalid('The source key "%s" may only contain lower case letters, digits, "-" and "_".')
                        ->end()
                    ->end()
                    ->scalarNode('url')
                        ->isRequired()
                        ->cannotBeEmpty()
                        ->validate()
                            ->ifTrue(static fn ($value): bool => !\is_string($value) || 1 !== preg_match('#^https?://[^\s/]+#i', $value))
                            ->thenInvalid('The url "%s" of the source is not an http(s) url.')
                        ->end()
                    ->end()
                    ->scalarNode('type')
                        ->defaultNull()
                        ->info('Key of the event type (see sulu_event.types) all events of the source get.')
                    ->end()
                    ->arrayNode('categories')
                        ->scalarPrototype()->end()
                        ->info('Keys of Sulu categories all events of the source get.')
                    ->end()
                    ->arrayNode('tags')
                        ->scalarPrototype()->end()
                        ->info('Tags (names) all events of the source get. Unknown tags are created.')
                    ->end()
                    ->scalarNode('locale')
                        ->defaultValue('en')
                        ->cannotBeEmpty()
                        ->info('Locale the title (and the texts) of the source are in.')
                    ->end()
                    ->scalarNode('timezone')
                        ->defaultNull()
                        ->info('IANA time zone for events whose source gives no usable one.')
                        ->validate()
                            ->ifTrue(static fn ($value): bool => null !== $value && !\in_array($value, \DateTimeZone::listIdentifiers(), true))
                            ->thenInvalid('"%s" is not a valid IANA timezone.')
                        ->end()
                    ->end()
                    ->integerNode('image')
                        ->defaultNull()
                        ->info('Id of a media (an image of your own) every event of the source gets if it has none; wins over category_images.')
                    ->end()
                    ->scalarNode('series')
                        ->defaultNull()
                        ->info('Name of the event series ("ces"). With the year of the start it joins the same event from different sources ("ces-2027"). Events of one source are never joined.')
                    ->end()
                    ->booleanNode('featured')
                        ->defaultFalse()
                        ->info('true: the events of the source get the tag sulu_event.featured_tag (highlights), new ones and changed ones. Needs featured_tag.')
                    ->end()
                    ->integerNode('priority')
                        ->defaultNull()
                        ->info('Which source maintains an event that several sources deliver: the higher number wins. Default: 100 for pages, 50 for calendars.')
                    ->end()
                    ->arrayNode('languages')
                        ->scalarPrototype()->end()
                        ->info('Language codes the events of the source are held in ("en", "de").')
                    ->end()
                    ->append($this->createFiltersNode('filters', 'Rules of the sieve for this source: they replace the global ones (sulu_event.import.filters) rule by rule; what is not set stays as it is globally.', false))
                ->end()
            ->end();

        return $node;
    }
}
