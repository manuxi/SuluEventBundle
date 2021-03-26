<?php

namespace Manuxi\SuluEventBundle\DependencyInjection;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('sulu_event');
        $root = $treeBuilder->getRootNode();

//        $root->children()
//            ->arrayNode('objects')
//                ->addDefaultsIfNotSet()
//                ->children()
//                    ->arrayNode('news')
//                        ->addDefaultsIfNotSet()
//                            ->children()
//                                ->scalarNode('model')->defaultValue(Event::class)->end()
//                                ->scalarNode('repository')->defaultValue(EventRepository::class)->end()
//                            ->end()
//                        ->end()
//                    ->end()
//                ->end()
//            ->end();

        return $treeBuilder;
    }
}
