<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @var bool
     */
    private $debug;

    /**
     * Constructor.
     *
     * @param bool $debug
     */
    public function __construct($debug)
    {
        $this->debug = (bool) $debug;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('huh');

        $rootNode
            ->children()
                ->arrayNode('filter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('types')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->cannotBeEmpty()->end()
                                    ->scalarNode('class')->cannotBeEmpty()->end()
                                    ->scalarNode('type')->defaultValue('other')->end()
                                    ->booleanNode('wrapper')->defaultValue(false)->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('templates')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->cannotBeEmpty()->end()
                                    ->scalarNode('template')->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('template_prefixes')
                        ->prototype('scalar')
                        ->end()->end()
                    ->end()
                ->end()
                ->arrayNode('sort')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('types')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->cannotBeEmpty()->end()
                                    ->scalarNode('class')->cannotBeEmpty()->end()
                                    ->scalarNode('type')->defaultValue('other')->end()
                                    ->booleanNode('wrapper')->defaultValue(false)->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('directions')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('value')->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('classes')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->cannotBeEmpty()->end()
                                    ->scalarNode('class')->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
