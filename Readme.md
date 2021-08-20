# Symfony Bundle Test

[![Latest Version](https://img.shields.io/github/release/Nyholm/symfony-bundle-test.svg?style=flat-square)](https://github.com/Nyholm/symfony-bundle-test/releases)
[![Build Status](https://img.shields.io/travis/SymfonyTest/symfony-bundle-test/master.svg?style=flat-square)](https://travis-ci.org/SymfonyTest/symfony-bundle-test)
[![Total Downloads](https://img.shields.io/packagist/dt/nyholm/symfony-bundle-test.svg?style=flat-square)](https://packagist.org/packages/nyholm/symfony-bundle-test)

**Test if your bundle is compatible with different Symfony versions**

When you want to make sure that your bundle works with different versions of Symfony
you need to create a custom `AppKernel` and load your bundle and configuration.

Using this bundle test together with Matthias Nobacks's
[SymfonyDependencyInjectionTest](https://github.com/SymfonyTest/SymfonyDependencyInjectionTest)
will give you a good base for testing a Symfony bundle.

## Install

Via Composer

``` bash
$ composer require --dev nyholm/symfony-bundle-test
```

## Write a test

```php

use Nyholm\BundleTest\BaseBundleTestCase;
use Acme\AcmeFooBundle;
use Acme\Service\Foo;

class BundleInitializationTest extends BaseBundleTestCase
{
    protected function getBundleClass()
    {
        return AcmeFooBundle::class;
    }

    public function testInitBundle()
    {
        // Boot the kernel.
        $this->bootKernel();

        // Get the container
        $container = $this->getContainer();

        // Test if you services exists
        $this->assertTrue($container->has('acme.foo'));
        $service = $container->get('acme.foo');
        $this->assertInstanceOf(Foo::class, $service);
    }

    public function testBundleWithDifferentConfiguration()
    {
        // Create a new Kernel
        $kernel = $this->createKernel();

        // Add some configuration
        $kernel->addConfigFile(__DIR__.'/config.yml');

        // Add some other bundles we depend on
        $kernel->addBundle(OtherBundle::class);

        // Boot the kernel as normal ...
        $this->bootKernel();

        // ...
    }
}

```

## Private services in Symfony 4

In Symfony 4 services are private by default. This is a good thing, but in order to test them properly we need to make
them public when we are running the tests. This can easily be done with a compiler pass.

```php
class BundleInitializationTest extends BaseBundleTestCase
{
    protected function setUp()
    {
        parent::setUp();

        // Make all services public
        $this->addCompilerPass(new PublicServicePass());

        // Make services public that have an idea that matches a regex
        $this->addCompilerPass(new PublicServicePass('|my_bundle.*|'));
    }

    // ...
}
```

### Known Issue

Be aware that if you make all services public then you will not get any errors if your bundles access this services even if they are private by nature.

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
        php: [ '7.4', '8.0' ]
        symfony: ['^4.4', '^5.3']

    steps:
      - uses: actions/checkout@master

      - name: Setup PHP ${{ matrix.php }}
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          tools: flex

      - name: Download dependencies
        env:
          SYMFONY_REQUIRE: ${{ matrix.symfony }}
        uses: ramsey/composer-install@v1

      - name: Run test suite on PHP ${{ matrix.php }} and Symfony ${{ matrix.symfony }}
        run: ./vendor/bin/phpunit
```
