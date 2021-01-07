<?php

namespace Nyholm\BundleTest\Tests\Fixtures\ConfigurationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Laurent VOULLEMIER <laurent.voullemier@gmail.com>
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder('configuration');

        if (method_exists($tree, 'getRootNode')) {
            $root = $tree->getRootNode();
        } else {
            $root = $tree->root('configuration');
        }

        $root
            ->children()
                ->scalarNode('foo')->isRequired()->end()
                ->arrayNode('bar')
                    ->isRequired()
                    ->scalarPrototype()
                ->end()
            ->end();

        return $tree;
    }
}
