<?php

namespace Nyholm\BundleTest\Tests\Functional;

use Nyholm\BundleTest\TestKernel;
use Nyholm\BundleTest\Tests\Fixtures\ConfigurationBundle\ConfigurationBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Laurent VOULLEMIER <laurent.voullemier@gmail.com>
 */
final class BundleConfigurationTest extends KernelTestCase
{
    protected static function createKernel(array $options = []): KernelInterface
    {
        KernelTestCase::$class = TestKernel::class;

        /**
         * @var TestKernel $kernel
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
        $kernel = self::bootKernel(['config' => function (TestKernel $kernel) use ($config) {
            $kernel->addConfigFile($config);
        }]);

        $container = $kernel->getContainer();

        $this->assertEquals('val1', $container->getParameter('app.foo'));
        $this->assertEquals(['val2', 'val3'], $container->getParameter('app.bar'));
    }
}
