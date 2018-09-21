<?php

namespace Puzzle\Admin\ExpertiseBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('puzzle_admin_expertise');
        
        $rootNode
            ->children()
                ->scalarNode('title')->defaultValue('expertise.title')->end()
                ->scalarNode('description')->defaultValue('expertise.description')->end()
                ->scalarNode('icon')->defaultValue('expertise.icon')->end()
                ->arrayNode('roles')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('expertise')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('label')->defaultValue('ROLE_EXPERTISE')->end()
                                ->scalarNode('description')->defaultValue('expertise.role.default')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('dirname')->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
