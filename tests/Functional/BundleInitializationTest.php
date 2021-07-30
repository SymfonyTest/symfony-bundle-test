<?php

namespace Nyholm\BundleTest\Tests\Functional;

use Nyholm\BundleTest\AppKernel;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class BundleInitializationTest extends KernelTestCase
{
    protected static function createKernel(array $options = [])
    {
        KernelTestCase::$class = AppKernel::class;

        /**
         * @var AppKernel $kernel
         */
        $kernel = parent::createKernel($options);
        $kernel->addBundle(FrameworkBundle::class);

        return $kernel;
    }

    public function testRegisterBundle()
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->assertTrue($container->has('kernel'));
    }
}
