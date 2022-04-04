<?php

namespace Nyholm\BundleTest;

use Symfony\Bundle\FrameworkBundle\CacheWarmer\ConfigBuilderCacheWarmer;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
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
    private $testBundle = [];

    /**
     * @var string[]|callable[]
     */
    private $testConfigs = [];

    /**
     * @var string
     */
    private $testCachePrefix;

    /**
     * @var string|null;
     */
    private $testProjectDir;

    /**
     * @var CompilerPassInterface[]
     */
    private $testCompilerPasses = [];

    /**
     * @var array<int, string>
     */
    private $testRoutingFiles = [];

    /**
     * Internal config.
     *
     * @var bool
     */
    private $clearCache = true;

    /**
     * {@inheritDoc}
     */
    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);

        $this->testCachePrefix = uniqid('cache', true);

        $this->addTestBundle(FrameworkBundle::class);
        $this->addTestConfig(__DIR__.'/config/framework.yml');
        if (class_exists(ConfigBuilderCacheWarmer::class)) {
            $this->addTestConfig(__DIR__.'/config/framework-53.yml');
        } else {
            $this->addTestConfig(__DIR__.'/config/framework-52.yml');
        }
    }

    /**
     * @psalm-param class-string<BundleInterface> $bundleClassName
     *
     * @param string $bundleClassName
     */
    public function addTestBundle($bundleClassName): void
    {
        $this->testBundle[] = $bundleClassName;
    }

    /**
     * @param string|callable $configFile path to a config file or a callable which get the {@see ContainerBuilder} as its first argument
     */
    public function addTestConfig($configFile): void
    {
        $this->testConfigs[] = $configFile;
    }

    public function getCacheDir(): string
    {
        return realpath(sys_get_temp_dir()).'/NyholmBundleTest/'.$this->testCachePrefix;
    }

    public function getLogDir(): string
    {
        return realpath(sys_get_temp_dir()).'/NyholmBundleTest/log';
    }

    public function getProjectDir(): string
    {
        if (null === $this->testProjectDir) {
            return realpath(__DIR__.'/../../../../');
        }

        return $this->testProjectDir;
    }

    /**
     * @param string|null $projectDir
     */
    public function setTestProjectDir($projectDir): void
    {
        $this->testProjectDir = $projectDir;
    }

    public function registerBundles(): iterable
    {
        $this->testBundle = array_unique($this->testBundle);

        foreach ($this->testBundle as $bundle) {
            yield new $bundle();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function buildContainer(): ContainerBuilder
    {
        $container = parent::buildContainer();

        foreach ($this->testCompilerPasses as $pass) {
            $container->addCompilerPass($pass);
        }

        return $container;
    }

    /**
     * @param CompilerPassInterface $compilerPasses
     */
    public function addTestCompilerPass($compilerPasses): void
    {
        $this->testCompilerPasses[] = $compilerPasses;
    }

    /**
     * @param string $routingFile
     */
    public function addTestRoutingFile($routingFile): void
    {
        $this->testRoutingFiles[] = $routingFile;
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
        foreach ($this->testConfigs as $config) {
            $loader->load($config);
        }
    }

    /**
     * @param RoutingConfigurator|RouteCollectionBuilder $routes
     */
    protected function configureRoutes($routes): void
    {
        foreach ($this->testRoutingFiles as $routingFile) {
            $routes->import($routingFile);
        }
    }

    public function shutdown($clearCache = true): void
    {
        parent::shutdown();

        if (!$this->clearCache) {
            return;
        }

        $cacheDirectory = $this->getCacheDir();
        $logDirectory = $this->getLogDir();

        $filesystem = new Filesystem();

        if ($filesystem->exists($cacheDirectory)) {
            $filesystem->remove($cacheDirectory);
        }

        if ($filesystem->exists($logDirectory)) {
            $filesystem->remove($logDirectory);
        }
    }

    public function setClearCacheAfterShutdown(bool $clearCache): void
    {
        $this->clearCache = $clearCache;
    }
}
