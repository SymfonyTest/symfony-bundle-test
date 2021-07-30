<?php

namespace Nyholm\BundleTest;

use Symfony\Bundle\FrameworkBundle\CacheWarmer\ConfigBuilderCacheWarmer;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class AppKernel extends Kernel
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
    private $cachePrefix = '';

    /**
     * @var string|null;
     */
    private $fakedProjectDir;

    /**
     * @var CompilerPassInterface[]
     */
    private $compilerPasses = [];

    /**
     * @var string|null
     */
    private $routingFile = null;

    /**
     * {@inheritDoc}
     */
    public function __construct(/*string $environment, bool $debug*/)
    {
        $args = \func_get_args();

        if (1 === \func_num_args()) {
            trigger_deprecation('nyholm/symfony-bundle-test', '1.9', 'The signature of the "%s($cachePrefix)" constructor is deprecated, use the constructor with 2 arguments: "string $environment, bool $debug".', self::class);

            parent::__construct($args[0], true);
        } elseif (2 === \func_num_args()) {
            parent::__construct($args[0], $args[1]);

            $this->cachePrefix = uniqid('cache', true);
        }

        $this->addBundle(FrameworkBundle::class);
        $this->addConfigFile(__DIR__.'/config/framework.yml');
        if (class_exists(ConfigBuilderCacheWarmer::class)) {
            $this->addConfigFile(__DIR__.'/config/framework-53.yml');
        } else {
            $this->addConfigFile(__DIR__.'/config/framework-52.yml');
        }
    }

    /**
     * @param string $bundle
     */
    public function addBundle($bundleClassName)
    {
        $this->bundlesToRegister[] = $bundleClassName;
    }

    /**
     * @param string $configFile path to config file
     */
    public function addConfigFile($configFile)
    {
        $this->configFiles[] = $configFile;
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/NyholmBundleTest/'.$this->cachePrefix;
    }

    public function getLogDir()
    {
        return sys_get_temp_dir().'/NyholmBundleTest/log';
    }

    public function getProjectDir()
    {
        if (null === $this->fakedProjectDir) {
            return realpath(__DIR__.'/../../../../');
        }

        return $this->fakedProjectDir;
    }

    /**
     * @param string|null $rootDir
     */
    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * @param string|null $projectDir
     */
    public function setProjectDir($projectDir)
    {
        $this->fakedProjectDir = $projectDir;
    }

    public function registerBundles()
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
        $routes = new RouteCollectionBuilder($loader);

        if ($this->routingFile) {
            $routes->import($this->routingFile);
        } else {
            $routes->import(__DIR__.'/config/routing.yml');
        }

        return $routes->build();
    }

    public function setCachePrefix($cachePrefix)
    {
        $this->cachePrefix = $cachePrefix;
    }

    /**
     * {@inheritdoc}
     */
    protected function buildContainer()
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
    public function addCompilerPasses(array $compilerPasses)
    {
        $this->compilerPasses = $compilerPasses;
    }

    /**
     * @param string|null $routingFile
     */
    public function setRoutingFile($routingFile)
    {
        $this->routingFile = $routingFile;
    }

    public function handleOptions(array $options)
    {
        if (array_key_exists('bundles', $options)) {
            foreach ($options['bundles'] as $bundle) {
                $this->addBundle($bundle);
            }
        }

        if (array_key_exists('configFiles', $options)) {
            foreach ($options['configFiles'] as $bundle) {
                $this->addConfigFile($bundle);
            }
        }

        if (array_key_exists('compilerPasses', $options)) {
            $this->addCompilerPasses($options['compilerPasses']);
        }

        if (array_key_exists('routingFile', $options)) {
            $this->setRoutingFile($options['routingFile']);
        }

        if (array_key_exists('cachePrefix', $options)) {
            $this->setCachePrefix($options['cachePrefix']);
        }
    }
}
