<?php

namespace Nyholm\BundleTest\Tests\Functional;

use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\KernelInterface;

class ValidationConfigurationTest extends KernelTestCase
{
    /** @var bool|null */
    public static $validatorRegistered;

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        /** @var TestKernel $kernel */
        $kernel = parent::createKernel($options);
        $kernel->handleOptions($options);

        return $kernel;
    }

    /**
     * The kernel must boot whether or not symfony/validator is installed
     * (on Symfony 8.2+, "framework.validation" requires the validator extension),
     * and validation must stay disabled by default.
     */
    public function testValidationIsDisabledByDefault(): void
    {
        self::$validatorRegistered = null;

        self::bootKernel(['config' => static function (TestKernel $kernel): void {
            // "validator" is private and removed when unused, so inspect the
            // definitions before the removing passes run.
            $kernel->addTestCompilerPass(new class implements CompilerPassInterface {
                public function process(ContainerBuilder $container): void
                {
                    ValidationConfigurationTest::$validatorRegistered = $container->has('validator');
                }
            }, PassConfig::TYPE_BEFORE_REMOVING);
        }]);

        $this->assertFalse(self::$validatorRegistered);
    }
}
