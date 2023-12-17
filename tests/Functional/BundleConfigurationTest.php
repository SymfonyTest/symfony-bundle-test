<?php

namespace Nyholm\BundleTest\Tests\Functional;

use Nyholm\BundleTest\TestKernel;
use Nyholm\BundleTest\Tests\Fixtures\ConfigurationBundle\ConfigurationBundle;
use Nyholm\BundleTest\Tests\Fixtures\ConfigurationBundle\DependencyInjection\Compiler\DeRegisterSomethingPass;
use Nyholm\BundleTest\Tests\Fixtures\ConfigurationBundle\DependencyInjection\Compiler\RegisterSomethingPass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Laurent VOULLEMIER <laurent.voullemier@gmail.com>
 */
final class BundleConfigurationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        /**
         * @var TestKernel $kernel
         */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(ConfigurationBundle::class);
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function provideBundleWithDifferentConfigurationFormats(): array
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
    public function testBundleWithDifferentConfigurationFormats($config): void
    {
        $kernel = self::bootKernel(['config' => function (TestKernel $kernel) use ($config) {
            $kernel->addTestConfig($config);
        }]);

        $container = $kernel->getContainer();

        $this->assertEquals('val1', $container->getParameter('app.foo'));
        $this->assertEquals(['val2', 'val3'], $container->getParameter('app.bar'));
    }

    public function testAddCompilerPassPriority(): void
    {
        // CASE 1: Compiler pass without priority, should be prioritized by order of addition
        $kernel = self::bootKernel(['config' => function (TestKernel $kernel) {
            $kernel->addTestConfig(__DIR__.'/../Fixtures/Resources/ConfigurationBundle/config.php');
            $kernel->addTestCompilerPass(new DeRegisterSomethingPass());
            $kernel->addTestCompilerPass(new RegisterSomethingPass());
        }]);

        $container = $kernel->getContainer();

        $this->assertTrue($container->has('something'));

        // CASE 2: Compiler pass with priority, should be prioritized by priority
        $kernel = self::bootKernel(['config' => function (TestKernel $kernel) {
            $kernel->addTestConfig(__DIR__.'/../Fixtures/Resources/ConfigurationBundle/config.php');
            $kernel->addTestCompilerPass(new DeRegisterSomethingPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -5);
            $kernel->addTestCompilerPass(new RegisterSomethingPass());
        }]);

        $container = $kernel->getContainer();

        $this->assertFalse($container->has('something'));

        // CASE 3: Compiler pass without priority, should be prioritized by order of addition
        $kernel = self::bootKernel(['config' => function (TestKernel $kernel) {
            $kernel->addTestConfig(__DIR__.'/../Fixtures/Resources/ConfigurationBundle/config.php');
            // DeRegisterSomethingPass is now added as second compiler pass
            $kernel->addTestCompilerPass(new RegisterSomethingPass());
            $kernel->addTestCompilerPass(new DeRegisterSomethingPass());
        }]);

        $container = $kernel->getContainer();

        $this->assertFalse($container->has('something'));
    }
}
