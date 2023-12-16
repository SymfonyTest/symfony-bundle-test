<?php

namespace Nyholm\BundleTest\Tests\Fixtures\ConfigurationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DeRegisterSomethingPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('something')) {
            $container->removeDefinition('something');
        }
    }
}
