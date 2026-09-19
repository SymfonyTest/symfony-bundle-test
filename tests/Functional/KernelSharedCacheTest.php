<?php

namespace Nyholm\BundleTest\Tests\Functional;

use Nyholm\BundleTest\TestKernel;
use Nyholm\BundleTest\Tests\Fixtures\ConfigurationBundle\ConfigurationBundle;
use Nyholm\BundleTest\Tests\Fixtures\ConfigurationBundle\DependencyInjection\Compiler\RegisterSomethingPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class KernelSharedCacheTest extends TestCase
{
    public function testDisabledByDefault(): void
    {
        $first = new TestKernel('test', true);
        $second = new TestKernel('test', true);

        self::assertNotSame($first->getCacheDir(), $second->getCacheDir());
    }

    public function testSharedCache(): void
    {
        $first = $this->createKernel();
        $second = $this->createKernel();

        self::assertSame($first->getCacheDir(), $second->getCacheDir());
    }

    /**
     * @dataProvider provideCacheKeyInput
     */
    public function testCacheKeyInput(callable $configure): void
    {
        $kernel = $this->createKernel();
        $other = $this->createKernel();

        $configure($other);

        self::assertNotSame($kernel->getCacheDir(), $other->getCacheDir());
    }

    /**
     * @return array<int, array<int, callable>>
     */
    public function provideCacheKeyInput(): array
    {
        return [
            [static function (TestKernel $kernel): void {
                $kernel->addTestBundle(ConfigurationBundle::class);
            }],
            [static function (TestKernel $kernel): void {
                $kernel->addTestConfig(__DIR__.'/../Fixtures/Resources/ConfigurationBundle/config.yml');
            }],
            [static function (TestKernel $kernel): void {
                $kernel->addTestConfig(static function (): void {
                });
            }],
            [static function (TestKernel $kernel): void {
                $kernel->addTestRoutingFile(__DIR__.'/../Fixtures/Resources/Routing/routes.yml');
            }],
            [static function (TestKernel $kernel): void {
                $kernel->addTestCompilerPass(new RegisterSomethingPass());
            }],
            [static function (TestKernel $kernel): void {
                $kernel->addTestCompilerPass(new RegisterSomethingPass(), PassConfig::TYPE_OPTIMIZE);
            }],
            [static function (TestKernel $kernel): void {
                $kernel->addTestCompilerPass(new RegisterSomethingPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);
            }],
            [static function (TestKernel $kernel): void {
                $kernel->setTestProjectDir(__DIR__);
            }],
        ];
    }

    public function testEnvironmentAndDebug(): void
    {
        $kernel = $this->createKernel();

        $otherEnvironment = new TestKernel('prod', true);
        $otherEnvironment->setSharedCache(true);

        $otherDebug = new TestKernel('test', false);
        $otherDebug->setSharedCache(true);

        self::assertNotSame($kernel->getCacheDir(), $otherEnvironment->getCacheDir());
        self::assertNotSame($kernel->getCacheDir(), $otherDebug->getCacheDir());
    }

    public function testSharedCacheKeepsTheCacheDirectory(): void
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        $cacheDirectory = $kernel->getCacheDir();
        $filesystem = new Filesystem();

        $kernel->shutdown();

        self::assertTrue($filesystem->exists($cacheDirectory));

        $filesystem->remove($cacheDirectory);
    }

    public function testSetClearCacheAfterShutdownOverridesSharedCache(): void
    {
        $kernel = $this->createKernel();
        $kernel->setClearCacheAfterShutdown(true);
        $kernel->boot();

        $cacheDirectory = $kernel->getCacheDir();
        $filesystem = new Filesystem();

        $kernel->shutdown();
        $kernel->clearCache();

        self::assertFalse($filesystem->exists($cacheDirectory));
    }

    public function testTestToken(): void
    {
        $kernel = $this->createKernel();
        $withoutToken = $kernel->getCacheDir();

        putenv('TEST_TOKEN=1');

        try {
            $first = $this->createKernel()->getCacheDir();

            putenv('TEST_TOKEN=2');

            $second = $this->createKernel()->getCacheDir();
        } finally {
            putenv('TEST_TOKEN');
        }

        self::assertNotSame($withoutToken, $first);
        self::assertNotSame($first, $second);
    }

    public function testTestTokenWithoutSharedCache(): void
    {
        $kernel = new TestKernel('test', true);
        $withoutToken = $kernel->getCacheDir();

        putenv('TEST_TOKEN=1');

        try {
            self::assertSame($withoutToken, $kernel->getCacheDir());
        } finally {
            putenv('TEST_TOKEN');
        }
    }

    public function testSetSharedCacheAfterBoot(): void
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        try {
            $this->expectException(\LogicException::class);

            $kernel->setSharedCache(false);
        } finally {
            $kernel->shutdown();
        }
    }

    private function createKernel(): TestKernel
    {
        $kernel = new TestKernel('test', true);
        $kernel->setSharedCache(true);

        return $kernel;
    }
}
