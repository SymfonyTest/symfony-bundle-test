<?php

namespace Nyholm\BundleTest;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 *
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class AppKernel extends Kernel
{
    use MicroKernelTrait;

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

    public function __construct($cachePrefix)
    {
        parent::__construct('test', true);
        $this->cachePrefix = $cachePrefix;
        $this->addBundle(FrameworkBundle::class);
        $this->addConfigFile(__DIR__.'/config/framwork.yml');
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


    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        array_unique($this->configFiles);
        foreach ($this->configFiles as $path) {
            $loader->load($path);
        }
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/NyholmBundleTest/'.$this->cachePrefix;
    }

    /**
     * Load routes
     *
     * @param RouteCollectionBuilder $routes
     */
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->import('config/routing.yml');
    }

    public function registerBundles()
    {
        array_unique($this->bundlesToRegister);
        $bundles = [];
        foreach ($this->bundlesToRegister as $bundle) {
            $bundles = new $bundle();
        }

        return $bundles;
    }
}
