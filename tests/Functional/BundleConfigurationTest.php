<?php

namespace Nyholm\BundleTest\Tests\Functional;

use Nyholm\BundleTest\AppKernel;
use Nyholm\BundleTest\Tests\Fixtures\ConfigurationBundle\ConfigurationBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Laurent VOULLEMIER <laurent.voullemier@gmail.com>
 */
final class BundleConfigurationTest extends KernelTestCase
{
    protected static function createKernel(array $options = [])
    {
        KernelTestCase::$class = AppKernel::class;

        /**
         * @var AppKernel $kernel
         */
        $kernel = parent::createKernel($options);
        $kernel->addBundle(ConfigurationBundle::class);
        $kernel->handleOptions($options);

        return $kernel;
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
        $kernel = self::bootKernel(['config' => function (AppKernel $kernel) use ($config) {
            $kernel->addConfigFile($config);
        }]);

        $container = $kernel->getContainer();

        $this->assertEquals('val1', $container->getParameter('app.foo'));
        $this->assertEquals(['val2', 'val3'], $container->getParameter('app.bar'));
    }
}
