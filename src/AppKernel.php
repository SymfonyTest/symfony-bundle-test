<?php

namespace Nyholm\BundleTest;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
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
     * @param string $cachePrefix
     */
    public function __construct($cachePrefix)
    {
        parent::__construct('test', true);
        $this->cachePrefix = $cachePrefix;
        $this->addBundle(FrameworkBundle::class);
        $this->addConfigFile(__DIR__.'/config/framework.yml');
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
            $container->loadFromExtension('framework', array(
                'router' => array(
                    'resource' => 'kernel:loadRoutes',
                    'type' => 'service',
                ),
            ));

            $this->configFiles = array_unique($this->configFiles);
            foreach ($this->configFiles as $path) {
                $loader->load($path);
            }

            $container->addObjectResource($this);
        });
    }

    /**
     * (From MicroKernelTrait)
     * @internal
     */
    public function loadRoutes(LoaderInterface $loader)
    {
        $routes = new RouteCollectionBuilder($loader);
        $routes->import(__DIR__.'/config/routing.yml');

        return $routes->build();
    }
}
