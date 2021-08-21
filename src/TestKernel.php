<?php

namespace Nyholm\BundleTest;

use Symfony\Bundle\FrameworkBundle\CacheWarmer\ConfigBuilderCacheWarmer;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Loader\PhpFileLoader as RoutingPhpFileLoader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @var string[]
     */
    private $bundlesToRegister = [];

    /**
     * @var string[]|callable[]
     */
    private $configs = [];

    /**
     * @var string
     */
    private $cachePrefix;

    /**
     * @var string|null;
     */
    private $fakedProjectDir;

    /**
     * @var CompilerPassInterface[]
     */
    private $compilerPasses = [];

    /**
     * @var array<int, string>
     */
    private $routingFiles = [];

    /**
     * {@inheritDoc}
     */
    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);

        $this->cachePrefix = uniqid('cache', true);

        $this->addBundle(FrameworkBundle::class);
        $this->addConfig(__DIR__.'/config/framework.yml');
        if (class_exists(ConfigBuilderCacheWarmer::class)) {
            $this->addConfig(__DIR__.'/config/framework-53.yml');
        } else {
            $this->addConfig(__DIR__.'/config/framework-52.yml');
        }
    }

    /**
     * @psalm-param class-string<BundleInterface> $bundleClassName
     *
     * @param string $bundleClassName
     */
    public function addBundle($bundleClassName): void
    {
        $this->bundlesToRegister[] = $bundleClassName;
    }

    /**
     * @param string|callable $configFile path to a config file or a callable which get the {@see ContainerBuilder} as its first argument
     */
    public function addConfig($configFile): void
    {
        $this->configs[] = $configFile;
    }

    public function getCacheDir(): string
    {
        return realpath(sys_get_temp_dir()).'/NyholmBundleTest/'.$this->cachePrefix;
    }

    public function getLogDir(): string
    {
        return realpath(sys_get_temp_dir()).'/NyholmBundleTest/log';
    }

    public function getProjectDir(): string
    {
        if (null === $this->fakedProjectDir) {
            return realpath(__DIR__.'/../../../../');
        }

        return $this->fakedProjectDir;
    }

    /**
     * @param string|null $projectDir
     */
    public function setProjectDir($projectDir): void
    {
        $this->fakedProjectDir = $projectDir;
    }

    public function registerBundles(): iterable
    {
        $this->bundlesToRegister = array_unique($this->bundlesToRegister);

        foreach ($this->bundlesToRegister as $bundle) {
            yield new $bundle();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function buildContainer(): ContainerBuilder
    {
        $container = parent::buildContainer();

        foreach ($this->compilerPasses as $pass) {
            $container->addCompilerPass($pass);
        }

        return $container;
    }

    /**
     * @param CompilerPassInterface $compilerPasses
     */
    public function addCompilerPass($compilerPasses): void
    {
        $this->compilerPasses[] = $compilerPasses;
    }

    /**
     * @param string $routingFile
     */
    public function addRoutingFile($routingFile): void
    {
        $this->routingFiles[] = $routingFile;
    }

    public function handleOptions(array $options): void
    {
        if (array_key_exists('config', $options) && is_callable($configCallable = $options['config'])) {
            $configCallable($this);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param LoaderInterface  $loader
     */
    protected function configureContainer($container, $loader): void
    {
        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }

    /**
     * @param RoutingConfigurator|RouteCollectionBuilder $routes
     */
    protected function configureRoutes($routes): void
    {
        foreach ($this->routingFiles as $routingFile) {
            $routes->import($routingFile);
        }
    }
}
