<?php

namespace Nyholm\BundleTest\Tests\Functional;

use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;

class BundleRoutingTest extends KernelTestCase
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
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function testAddRoutingFile(): void
    {
        $kernel = self::bootKernel([
            'config' => static function (TestKernel $kernel) {
                $kernel->addRoutingFile(__DIR__.'/../Fixtures/Resources/Routing/routes.yml');
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
}
