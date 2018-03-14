# Symfony Bundle Test

[![Latest Version](https://img.shields.io/github/release/Nyholm/symfony-bundle-test.svg?style=flat-square)](https://github.com/Nyholm/symfony-bundle-test/releases)
[![Build Status](https://img.shields.io/travis/SymfonyTest/symfony-bundle-test/master.svg?style=flat-square)](https://travis-ci.org/SymfonyTest/symfony-bundle-test)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/Nyholm/symfony-bundle-test.svg?style=flat-square)](https://scrutinizer-ci.com/g/Nyholm/symfony-bundle-test)
[![Quality Score](https://img.shields.io/scrutinizer/g/Nyholm/symfony-bundle-test.svg?style=flat-square)](https://scrutinizer-ci.com/g/Nyholm/symfony-bundle-test)
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

## Configure Travis

You want Travis to run against each currently supported LTS version of Symfony (since there would be only one per major version), plus the current if it's not an LTS too. There is no need for testing against version in between because Symfony follows [Semantic Versioning](http://semver.org/spec/v2.0.0.html). 

```yaml
language: php
sudo: false
cache:
    directories:
        - $HOME/.composer/cache/files
        - $HOME/symfony-bridge/.phpunit

env:
    global:
        - PHPUNIT_FLAGS=""
        - SYMFONY_PHPUNIT_DIR="$HOME/symfony-bridge/.phpunit"

matrix:
    fast_finish: true
    include:
          # Minimum supported dependencies with the latest and oldest PHP version
        - php: 7.2
          env: COMPOSER_FLAGS="--prefer-stable --prefer-lowest" SYMFONY_DEPRECATIONS_HELPER="weak_vendors"
        - php: 5.5
          env: COMPOSER_FLAGS="--prefer-stable --prefer-lowest" SYMFONY_DEPRECATIONS_HELPER="weak_vendors"

          # Test the latest stable release
        - php: 5.5
        - php: 5.6
        - php: 7.0
        - php: 7.1
        - php: 7.2
          env: COVERAGE=true PHPUNIT_FLAGS="-v --testsuite main --coverage-text --coverage-clover=build/coverage.xml"

        - php: 7.1
          env: DEPENDENCIES="dunglas/symfony-lock:^2"
        - php: 7.1
          env: DEPENDENCIES="dunglas/symfony-lock:^3"
        - php: 7.1
          env: DEPENDENCIES="dunglas/symfony-lock:^4"

          # Latest commit to master
        - php: 7.2
          env: STABILITY="dev"

    allow_failures:
          # Dev-master is allowed to fail.
        - env: STABILITY="dev"

before_install:
    - if [[ $COVERAGE != true ]]; then phpenv config-rm xdebug.ini || true; fi
    - if ! [ -z "$STABILITY" ]; then composer config minimum-stability ${STABILITY}; fi;
    - composer require --no-update symfony/phpunit-bridge:^4.0 ${DEPENDENCIES}

install:
    # To be removed when this issue will be resolved: https://github.com/composer/composer/issues/5355
    - if [[ "$COMPOSER_FLAGS" == *"--prefer-lowest"* ]]; then composer update --prefer-dist --no-interaction --prefer-stable --quiet; fi
    - composer update ${COMPOSER_FLAGS} --prefer-dist --no-interaction
    - ./vendor/bin/simple-phpunit install

script:
    - composer validate --strict --no-check-lock
    - ./vendor/bin/simple-phpunit $PHPUNIT_FLAGS

```

## Known Issue

Since this makes all seervices public, as a consequence errors like accessing private services in your code will not be caught in the tests.
