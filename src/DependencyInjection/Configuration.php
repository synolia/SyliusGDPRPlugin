<?php

declare(strict_types=1);

namespace Synolia\SyliusGDPRPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('synolia_sylius_gdpr');
        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('disable_default_mappings')
                    ->defaultFalse()
                ->end()
                ->arrayNode('anonymization')
                    ->children()
                        ->arrayNode('mappings')
                            ->children()
                                ->arrayNode('paths')
                                    ->scalarPrototype()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
