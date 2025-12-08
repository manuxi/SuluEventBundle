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
                        ->defaultValue('/{translator.trans("sulu_event.events")}/{object.getTitle()}')
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
