# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release. 

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
