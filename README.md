# Symfony Bundle Test

[![Total Downloads](https://img.shields.io/packagist/dt/nyholm/symfony-bundle-test.svg?style=flat-square)](https://packagist.org/packages/nyholm/symfony-bundle-test)

**Test if your bundle is compatible with different Symfony versions**

When you want to make sure that your bundle works with different versions of Symfony
you need to create a custom `TestKernel` and load your bundle and configuration.

Using this bundle test together with Matthias Nobacks's
[SymfonyDependencyInjectionTest](https://github.com/SymfonyTest/SymfonyDependencyInjectionTest)
will give you a good base for testing a Symfony bundle.

## Install

Via Composer

``` bash
composer require --dev nyholm/symfony-bundle-test
```

## Write a test

```php

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Nyholm\BundleTest\TestKernel;
use Acme\AcmeFooBundle;
use Acme\Service\Foo;
use Symfony\Component\HttpKernel\KernelInterface;

class BundleInitializationTest extends KernelTestCase
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
        $kernel->addTestBundle(AcmeFooBundle::class);
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function testInitBundle(): void
    {
        // Boot the kernel.
        $kernel = self::bootKernel();

        // Get the container
        $container = $kernel->getContainer();

        // Or for FrameworkBundle@^5.3.6 to access private services without the PublicCompilerPass
        // $container = self::getContainer();

        // Test if your services exists
        $this->assertTrue($container->has('acme.foo'));
        $service = $container->get('acme.foo');
        $this->assertInstanceOf(Foo::class, $service);
    }

    public function testBundleWithDifferentConfiguration(): void
    {
        // Boot the kernel with a config closure, the handleOptions call in createKernel is important for that to work
        $kernel = self::bootKernel(['config' => static function(TestKernel $kernel){
            // Add some other bundles we depend on
            $kernel->addTestBundle(OtherBundle::class);

            // Add some configuration
            $kernel->addTestConfig(__DIR__.'/config.yml');
        }]);

        // ...
    }
}

```

## Configure Github Actions

You want ["Github actions"](https://docs.github.com/en/actions) to run against each currently supported LTS version of Symfony (since there would be only one per major version), plus the current if it's not an LTS too. There is no need for testing against version in between because Symfony follows [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

Create a file in .github/workflows directory:
```yaml
#.github/workflows/php.yml
name: My bundle test

on:
  push: ~
  pull_request: ~

jobs:
  build:
    runs-on: ${{ matrix.operating-system }}
    name: PHP ${{ matrix.php }} and Symfony ${{ matrix.symfony }}
    strategy:
      matrix:
        operating-system: [ ubuntu-latest, windows-latest ]
        php: [ '7.4', '8.0', '8.1', '8.2' ]
        symfony: ['4.4.*', '5.4.*', '6.0.*', '6.1.*', '6.2.*']

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP ${{ matrix.php }}
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          tools: flex

      - name: Download dependencies
        env:
          SYMFONY_REQUIRE: ${{ matrix.symfony }}
        uses: ramsey/composer-install@v2

      - name: Run test suite on PHP ${{ matrix.php }} and Symfony ${{ matrix.symfony }}
        run: ./vendor/bin/phpunit
```
