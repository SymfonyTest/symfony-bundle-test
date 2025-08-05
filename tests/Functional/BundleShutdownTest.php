<?php

namespace Nyholm\BundleTest\Tests\Functional;

use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class BundleShutdownTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    public function testCleanupTemporaryDirectories(): void
    {
        $kernel = self::bootKernel();
        $cacheDirectory = $kernel->getCacheDir();
        $logDirectory = $kernel->getLogDir();
        $filesystem = new Filesystem();

        self::assertTrue($filesystem->exists($cacheDirectory));

        self::ensureKernelShutdown();

        self::assertFalse($filesystem->exists($cacheDirectory));
        self::assertFalse($filesystem->exists($logDirectory));
    }
}
