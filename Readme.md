# Symfony Bundle Test

[![Latest Version](https://img.shields.io/github/release/Nyholm/symfony-bundle-test.svg?style=flat-square)](https://github.com/Nyholm/symfony-bundle-test/releases)
[![Build Status](https://img.shields.io/travis/Nyholm/symfony-bundle-test/master.svg?style=flat-square)](https://travis-ci.org/Nyholm/symfony-bundle-test)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/Nyholm/symfony-bundle-test.svg?style=flat-square)](https://scrutinizer-ci.com/g/Nyholm/symfony-bundle-test)
[![Quality Score](https://img.shields.io/scrutinizer/g/Nyholm/symfony-bundle-test.svg?style=flat-square)](https://scrutinizer-ci.com/g/Nyholm/symfony-bundle-test)
[![Total Downloads](https://img.shields.io/packagist/dt/nyholm/symfony-bundle-test.svg?style=flat-square)](https://packagist.org/packages/nyholm/symfony-bundle-test)

**Test if your bundle is compatible with different Symfony versions**

When you want to make sure that your bundle works with different versions of Symfony
you need to create a custom `AppKernel` and load your bundle and configuration. 

Using this bundle test together with Matthias Nobacks's 
[SymfonyDependencyInjectionTest](https://github.com/matthiasnoback/SymfonyDependencyInjectionTest)
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

## Configure travis

You want travis to run the highest version of Symfony you support and the lowest
version. Same with PHP version. There is no need for testing for version in between
because both Symfony and PHP follow Semver. 

```yaml
language: php

php:
    - 5.5
    - 7.1
    - hhvm
env:
  global:
    - TEST_COMMAND="vendor/bin/phpunit"
  matrix:
    - SYMFONY_VERSION=3.2.*    
    - SYMFONY_VERSION=2.7.*

matrix:
  fast_finish: true
  include:
    - php: 5.5
      env: COMPOSER_FLAGS="--prefer-stable --prefer-lowest" SYMFONY_VERSION=2.7.* TEST_COMMAND="vendor/bin/phpunit --coverage-text --coverage-clover=build/coverage.xml"

install:
    - composer require symfony/symfony:${SYMFONY_VERSION} --no-update
    - travis_retry composer update ${COMPOSER_FLAGS} --prefer-dist --no-interaction

script:
    - $TEST_COMMAND
```

