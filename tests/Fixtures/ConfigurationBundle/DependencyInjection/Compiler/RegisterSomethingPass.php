<?php

namespace Nyholm\BundleTest\Tests\Fixtures\ConfigurationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterSomethingPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('something')) {
            return;
        }

        $definition = new Definition();
        $definition->setClass(\stdClass::class);
        $definition->setPublic(true);

        $container->setDefinition('something', $definition);
    }
}
