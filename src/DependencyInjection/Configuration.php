<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\DependencyInjection;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Repository\EventDimensionContentRepository;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
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
}
