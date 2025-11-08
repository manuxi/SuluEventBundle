<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\DependencyInjection;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventExcerpt;
use Manuxi\SuluEventBundle\Entity\EventExcerptTranslation;
use Manuxi\SuluEventBundle\Entity\EventSeo;
use Manuxi\SuluEventBundle\Entity\EventSeoTranslation;
use Manuxi\SuluEventBundle\Entity\EventTranslation;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Repository\EventExcerptRepository;
use Manuxi\SuluEventBundle\Repository\EventExcerptTranslationRepository;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Repository\EventSeoRepository;
use Manuxi\SuluEventBundle\Repository\EventSeoTranslationRepository;
use Manuxi\SuluEventBundle\Repository\EventTranslationRepository;
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

            ->arrayNode('types')
                ->useAttributeAsKey('key')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('name')->isRequired()->end()
                        ->scalarNode('color')->isRequired()->end()
                    ->end()
                ->end()
                ->defaultValue([])
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
                    ->arrayNode('event_translation')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(EventTranslation::class)->end()
                            ->scalarNode('repository')->defaultValue(EventTranslationRepository::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('event_seo')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(EventSeo::class)->end()
                            ->scalarNode('repository')->defaultValue(EventSeoRepository::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('event_seo_translation')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(EventSeoTranslation::class)->end()
                            ->scalarNode('repository')->defaultValue(EventSeoTranslationRepository::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('event_excerpt')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(EventExcerpt::class)->end()
                            ->scalarNode('repository')->defaultValue(EventExcerptRepository::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('event_excerpt_translation')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(EventExcerptTranslation::class)->end()
                            ->scalarNode('repository')->defaultValue(EventExcerptTranslationRepository::class)->end()
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
