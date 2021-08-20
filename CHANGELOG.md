# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release.

## 1.8.1

### Fixed

- Symfony 6 compatibility issues

## 1.8.0

### Added

- Support for Symfony 6
- Support for adding config with closures

### Fixed

- Routing with the Kernel
- Deprecation error in Symfony 5.3

## 1.7.0

### Added

- Support for PHP 8
- Automatically add `framework.router.utf8: true` on Symfony >= 5.1

## 1.6.1

### Fixed

- Catching exception in `BaseBundleTestCase::ensureKernelShutdown()`

## 1.6.0

### Added

- Support for Symfony 5

## 1.5.0

### Added

- Support for PHPUnit 8

## 1.4.0

### Added

- Aliases can be made public with the `PublicServicePass`.

## 1.3.1

### Fixed

- Show our dependencies directly in require section of composer.json.

## 1.3.0

### Added

- Adding compiler pass support.
- Adding support for making services public.

## 1.2.3

### Fixed

- Symfony 3.4/4.0 fix with annotations.

## 1.2.2

### Changed

- Only disable annotations if they are not installed.

## 1.2.1

### Fixed

- Better fix for annotation issue with Symfony 3.3.

## 1.2.0

### Added

- Allow tests with PHPUnit6

## 1.1.0

### Added

- `AppKernel::setProjectDir` and `AppKernel::setRootDir`

### Changed

- `%kernel.project_dir%` is now the directory of your root. (The directory with your composer.json)

## 1.0.2

### Fixed

- Fix for Symfony 3.3. We do not need cache on annotation.

## 1.0.1

### Fixed

- Bug with cache when running multiple tests

## 1.0.0

Initial release
