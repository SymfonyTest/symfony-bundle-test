<?php

namespace Nyholm\BundleTest\Tests\Functional;

use Nyholm\BundleTest\AppKernel;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;

class BundleRoutingTest extends KernelTestCase
{
    protected static function createKernel(array $options = []): KernelInterface
    {
        KernelTestCase::$class = AppKernel::class;

        /**
         * @var AppKernel $kernel
         */
        $kernel = parent::createKernel($options);
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function testSetRoutingFile()
    {
        $kernel = self::bootKernel([
            'config' => static function (AppKernel $kernel) {
                $kernel->setRoutingFile(__DIR__.'/../Fixtures/Resources/Routing/routes.yml');
            },
        ]);

        $container = $kernel->getContainer();
        $container = $container->get('test.service_container');
        /**
         * @var RouterInterface $router
         */
        $router = $container->get(RouterInterface::class);
        $routeCollection = $router->getRouteCollection();
        $routes = $routeCollection->all();

        $this->assertCount(2, $routes);
        $this->assertNotNull($routeCollection->get('app_home'));
        $this->assertNotNull($routeCollection->get('app_blog'));
    }

    protected function getBundleClass()
    {
        return FrameworkBundle::class;
    }
}
