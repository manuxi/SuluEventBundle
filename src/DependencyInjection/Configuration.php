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
                    ->useAttributeAsKey('key')
                    ->integerPrototype()->end()
                    ->info('Images of your own for imported events: category key => id of a media. An event without an image gets the one of its first category that is listed here. Images of the sources are never copied.')
                ->end()
                ->arrayNode('locales')
                    ->scalarPrototype()->end()
                    ->info('Locales an imported event should have a text in (for example ["de", "en"]). Events that lack one are listed as "needs work" (MCP tool sulu_event_import_pending).')
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
                    ->integerNode('priority')
                        ->defaultNull()
                        ->info('Which source maintains an event that several sources deliver: the higher number wins. Default: 100 for pages, 50 for calendars.')
                    ->end()
                    ->arrayNode('languages')
                        ->scalarPrototype()->end()
                        ->info('Language codes the events of the source are held in ("en", "de").')
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
