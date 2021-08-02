<?php

namespace Nyholm\BundleTest\Tests\Functional;

use Nyholm\BundleTest\BaseBundleTestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Routing\RouterInterface;

class BundleRoutingTest extends BaseBundleTestCase
{
    public function testSetRoutingFile()
    {
        $this->setRoutingFile(__DIR__.'/../Fixtures/Resources/Routing/routes.yml');

        $this->bootKernel();
        $container = $this->getContainer();
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
