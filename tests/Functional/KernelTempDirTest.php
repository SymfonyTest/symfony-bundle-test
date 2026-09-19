<?php

namespace Nyholm\BundleTest\Tests\Functional;

use Nyholm\BundleTest\TestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class KernelTempDirTest extends TestCase
{
    /**
     * @var string
     */
    private $customTempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customTempDir = realpath(sys_get_temp_dir()).'/NyholmBundleTestCustom';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $filesystem = new Filesystem();

        if ($filesystem->exists($this->customTempDir)) {
            $filesystem->remove($this->customTempDir);
        }
    }

    public function testDefaultTempDir(): void
    {
        $kernel = new TestKernel('test', true);

        self::assertSame(realpath(sys_get_temp_dir()).'/NyholmBundleTest', $kernel->getTempDir());
    }

    public function testCacheAndLogDirectories(): void
    {
        $kernel = new TestKernel('test', true);

        self::assertStringStartsWith($kernel->getTempDir().'/', $kernel->getCacheDir());
        self::assertSame($kernel->getTempDir().'/log', $kernel->getLogDir());
    }

    public function testSetTempDir(): void
    {
        $kernel = new TestKernel('test', true);
        $kernel->setTempDir($this->customTempDir);

        $tempDir = $this->customTempDir.'/NyholmBundleTest';

        self::assertSame($tempDir, $kernel->getTempDir());
        self::assertStringStartsWith($tempDir.'/', $kernel->getCacheDir());
        self::assertSame($tempDir.'/log', $kernel->getLogDir());
    }

    public function testSetTempDirWithNull(): void
    {
        $kernel = new TestKernel('test', true);
        $kernel->setTempDir($this->customTempDir);
        $kernel->setTempDir(null);

        self::assertSame(realpath(sys_get_temp_dir()).'/NyholmBundleTest', $kernel->getTempDir());
    }

    /**
     * @dataProvider provideSetTempDirWithEmptyDirectory
     */
    public function testSetTempDirWithEmptyDirectory(string $tempDir): void
    {
        $kernel = new TestKernel('test', true);

        $this->expectException(\InvalidArgumentException::class);

        $kernel->setTempDir($tempDir);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function provideSetTempDirWithEmptyDirectory(): array
    {
        return [
            [''],
            ['   '],
            ['/'],
            [' / '],
        ];
    }

    public function testSetTempDirAfterBoot(): void
    {
        $kernel = new TestKernel('test', true);
        $kernel->boot();

        try {
            $this->expectException(\LogicException::class);

            $kernel->setTempDir($this->customTempDir);
        } finally {
            $kernel->shutdown();
        }
    }

    /**
     * @dataProvider provideSetTempDirWithUntrimmedDirectory
     */
    public function testSetTempDirWithUntrimmedDirectory(string $tempDir): void
    {
        $kernel = new TestKernel('test', true);
        $kernel->setTempDir($tempDir);

        self::assertSame($this->customTempDir.'/NyholmBundleTest', $kernel->getTempDir());
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function provideSetTempDirWithUntrimmedDirectory(): array
    {
        $tempDir = realpath(sys_get_temp_dir()).'/NyholmBundleTestCustom';

        return [
            [$tempDir.'/'],
            [' '.$tempDir.' '],
            [' '.$tempDir.'/ '],
        ];
    }

    public function testCleanupCustomTemporaryDirectory(): void
    {
        $kernel = new TestKernel('test', true);
        $kernel->setTempDir($this->customTempDir);
        $kernel->boot();

        $cacheDirectory = $kernel->getCacheDir();
        $filesystem = new Filesystem();

        self::assertTrue($filesystem->exists($cacheDirectory));

        $kernel->shutdown();

        self::assertFalse($filesystem->exists($cacheDirectory));
    }
}
