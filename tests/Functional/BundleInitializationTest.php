<?php

namespace Nyholm\BundleTest\Tests\Functional;

use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class BundleInitializationTest extends KernelTestCase
{
    protected static function createKernel(array $options = []): KernelInterface
    {
        KernelTestCase::$class = TestKernel::class;

        /**
         * @var TestKernel $kernel
         */
        $kernel = parent::createKernel($options);
        $kernel->addBundle(FrameworkBundle::class);

        return $kernel;
    }

    public function testRegisterBundle()
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();
        $this->assertTrue($container->has('kernel'));
    }
}
