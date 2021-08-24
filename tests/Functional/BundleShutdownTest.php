<?php

namespace Nyholm\BundleTest\Tests\Functional;

use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class BundleShutdownTest extends KernelTestCase
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
        $kernel->addBundle(FrameworkBundle::class);

        return $kernel;
    }

    public function testCleanupTemporaryDirectories(): void
    {
        $kernel = self::bootKernel();
        $cacheDirectory = $kernel->getCacheDir();
        $logDirectory = $kernel->getLogDir();

        self::assertDirectoryExists($cacheDirectory);
        self::assertDirectoryExists($logDirectory);

        self::ensureKernelShutdown();

        self::assertDirectoryDoesNotExist($cacheDirectory);
        self::assertDirectoryDoesNotExist($logDirectory);
    }
}
