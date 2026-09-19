<?php

namespace Nyholm\BundleTest\Tests\Fixtures;

use Symfony\Contracts\Service\ResetInterface;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class ResettableService implements ResetInterface
{
    public function reset(): void
    {
    }
}
