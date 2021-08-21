<?php

namespace Nyholm\BundleTest;

use Symfony\Bundle\FrameworkBundle\CacheWarmer\ConfigBuilderCacheWarmer;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
    /**
     * @var string[]
     */
    private $bundlesToRegister = [];

    /**
     * @var array
     */
    private $configFiles = [];

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
        $this->configFiles[] = $configFile;
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/NyholmBundleTest/'.$this->cachePrefix;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/NyholmBundleTest/log';
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
        $bundles = [];
        foreach ($this->bundlesToRegister as $bundle) {
            $bundles[] = new $bundle();
        }

        return $bundles;
    }

    /**
     * (From MicroKernelTrait)
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) use ($loader) {
            $container->loadFromExtension('framework', [
                'router' => [
                    'resource' => 'kernel::loadRoutes',
                    'type' => 'service',
                ],
            ]);

            $this->configFiles = array_unique($this->configFiles, SORT_REGULAR);
            foreach ($this->configFiles as $path) {
                $loader->load($path);
            }

            $kernelClass = false !== strpos(static::class, "@anonymous\0") ? parent::class : static::class;

            if (!$container->hasDefinition('kernel')) {
                $container->register('kernel', $kernelClass)
                    ->addTag('controller.service_arguments')
                    ->setAutoconfigured(true)
                    ->setSynthetic(true)
                    ->setPublic(true)
                ;
            }

            $kernelDefinition = $container->getDefinition('kernel');
            $kernelDefinition->addTag('routing.route_loader');

            $container->addObjectResource($this);
        });
    }

    /**
     * (From MicroKernelTrait).
     *
     * @internal
     */
    public function loadRoutes(LoaderInterface $loader)
    {
        if (class_exists(RoutingConfigurator::class)) {
            $file = (new \ReflectionObject($this))->getFileName();
            /** @var RoutingPhpFileLoader $kernelLoader */
            $kernelLoader = $loader->getResolver()->resolve($file, 'php');
            $kernelLoader->setCurrentDir(\dirname($file));

            $collection = new RouteCollection();
            $configurator = new RoutingConfigurator($collection, $kernelLoader, $file, $file, $this->getEnvironment());

            foreach ($this->routingFiles as $routingFile) {
                $configurator->import($routingFile);
            }

            return $collection;
        } else {
            // Legacy symfony < 5.1
            $routes = new RouteCollectionBuilder($loader);

            foreach ($this->routingFiles as $routingFile) {
                $routes->import($routingFile);
            }

            return $routes->build();
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
     * @param CompilerPassInterface[] $compilerPasses
     */
    public function addCompilerPasses(array $compilerPasses): void
    {
        $this->compilerPasses = $compilerPasses;
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
}
