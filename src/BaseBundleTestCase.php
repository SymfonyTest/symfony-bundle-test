<?php

namespace Nyholm\BundleTest;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ResettableContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
abstract class BaseBundleTestCase extends TestCase
{
    /**
     * @var AppKernel
     */
    private $kernel;

    /**
     * @var CompilerPassInterface[]
     */
    private $compilerPasses = [];

    /**
     * @return string
     */
    abstract protected function getBundleClass();

    /**
     * @var string|null
     */
    private $routingFile = null;

    /**
     * Boots the Kernel for this test.
     *
     * @param array $options
     */
    protected function bootKernel()
    {
        $this->ensureKernelShutdown();

        if (null === $this->kernel) {
            $this->createKernel();
        }

        $this->kernel->boot();
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->kernel->getContainer();
    }

    /**
     * Get a kernel which you may configure with your bundle and services.
     *
     * @return AppKernel
     */
    protected function createKernel()
    {
        if (!class_exists(Kernel::class)) {
            throw new \LogicException('You must install symfony/symfony to run the bundle test.');
        }

        require_once __DIR__.'/AppKernel.php';
        $class = 'Nyholm\BundleTest\AppKernel';

        $this->kernel = new $class(uniqid('cache'));
        $this->kernel->addBundle($this->getBundleClass());
        $this->kernel->addCompilerPasses($this->compilerPasses);
        $this->kernel->setRoutingFile($this->routingFile);

        return $this->kernel;
    }

    /**
     * Shuts the kernel down if it was used in the test.
     *
     * @after
     */
    public function ensureKernelShutdown()
    {
        if (null !== $this->kernel) {
            try {
                $container = $this->kernel->getContainer();
            } catch (\LogicException $e) {
                $container = null;
            }
            $this->kernel->shutdown();
            if ($container instanceof ResettableContainerInterface) {
                $container->reset();
            }
        }
    }

    protected function addCompilerPass(CompilerPassInterface $compilerPass)
    {
        $this->compilerPasses[] = $compilerPass;
    }

    /**
     * @param string|null $routingFile
     */
    public function setRoutingFile($routingFile)
    {
        $this->routingFile = $routingFile;
    }
}
