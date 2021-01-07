<?php

namespace Nyholm\BundleTest\Tests\Fixtures\ConfigurationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * @author Laurent VOULLEMIER <laurent.voullemier@gmail.com>
 */
final class ConfigurationExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $container->setParameter('app.foo', $mergedConfig['foo']);
        $container->setParameter('app.bar', $mergedConfig['bar']);
    }
}
