<?php

namespace Nyholm\BundleTest\Tests\Functional;

use Nyholm\BundleTest\BaseBundleTestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class BundleInitializationTest extends BaseBundleTestCase
{
    protected function getBundleClass()
    {
        return FrameworkBundle::class;
    }

    public function testRegisterBundle()
    {
        $this->bootKernel();
        $container = $this->getContainer();
        $this->assertTrue($container->has('kernel'));
    }
}
