<?php

namespace Nyholm\BundleTest\Tests\Functional;

use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class BundleShutdownResetTest extends KernelTestCase
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
        $kernel->setClearCacheAfterShutdown(true);
        $kernel->addTestConfig(__DIR__.'/../Fixtures/Resources/Reset/services.yml');

        return $kernel;
    }

    public function testResetLazyServiceAfterShutdown(): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();

        $container->get('test.resettable');

        $cacheDirectory = $kernel->getCacheDir();
        $filesystem = new Filesystem();

        self::assertTrue($filesystem->exists($cacheDirectory));

        self::ensureKernelShutdown();
        $kernel->clearCache();

        self::assertFalse($filesystem->exists($cacheDirectory));
    }
}
