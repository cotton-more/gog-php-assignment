<?php

namespace GOG\CatalogBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('gog_catalog')
            ->children()

                ->arrayNode('form')->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('product')->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('type')->defaultValue('GOG\CatalogBundle\Form\ProductType')->end()
                                ->scalarNode('name')->defaultValue('gog_catalog_product')->end()
                            ->end()
                        ->end()
                        ->arrayNode('update_product')->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('type')->defaultValue('GOG\CatalogBundle\Form\UpdateProductType')->end()
                                ->scalarNode('name')->defaultValue('gog_catalog_product')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

            ->end();

        return $treeBuilder;
    }
}
