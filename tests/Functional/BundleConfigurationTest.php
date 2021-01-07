<?php

namespace Nyholm\BundleTest\Tests\Functional;

use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\Tests\Fixtures\ConfigurationBundle\ConfigurationBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Laurent VOULLEMIER <laurent.voullemier@gmail.com>
 */
final class BundleConfigurationTest extends BaseBundleTestCase
{
    protected function getBundleClass()
    {
        return ConfigurationBundle::class;
    }

    public function provideBundleWithDifferentConfigurationFormats()
    {
        return [
            [__DIR__.'/../Fixtures/Resources/ConfigurationBundle/config.yml'],
            [__DIR__.'/../Fixtures/Resources/ConfigurationBundle/config.xml'],
            [__DIR__.'/../Fixtures/Resources/ConfigurationBundle/config.php'],
            [function (ContainerBuilder $container) {
                $container->loadFromExtension('configuration', [
                    'foo' => 'val1',
                    'bar' => ['val2', 'val3'],
                ]);
            }],
        ];
    }

    /**
     * @dataProvider provideBundleWithDifferentConfigurationFormats
     *
     * @param string|callable $config
     */
    public function testBundleWithDifferentConfigurationFormats($config)
    {
        $kernel = $this->createKernel();
        $kernel->addConfigFile($config);
        $this->bootKernel();
        $this->assertEquals('val1', $kernel->getContainer()->getParameter('app.foo'));
        $this->assertEquals(['val2', 'val3'], $kernel->getContainer()->getParameter('app.bar'));
    }
}
