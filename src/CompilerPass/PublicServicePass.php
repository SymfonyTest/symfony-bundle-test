<?php

namespace Nyholm\BundleTest\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @deprecated Deprecated since 1.9 and will be removed in 2.0, use the test.service_container service instead.
 */
class PublicServicePass implements CompilerPassInterface
{
    /**
     * A regex to match the services that should be public.
     *
     * @var string
     */
    private $regex;

    /**
     * @param string $regex
     */
    public function __construct($regex = '|.*|')
    {
        $this->regex = $regex;
    }

    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            if (preg_match($this->regex, $id)) {
                $definition->setPublic(true);
            }
        }

        foreach ($container->getAliases() as $id => $alias) {
            if (preg_match($this->regex, $id)) {
                $alias->setPublic(true);
            }
        }
    }
}
