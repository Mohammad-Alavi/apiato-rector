<p align="center">
   <a href="https://github.com/Mohammad-Alavi/apiato-rector/actions/workflows/tests.yaml">
      <img src="https://img.shields.io/github/actions/workflow/status/Mohammad-Alavi/apiato-rector/tests.yaml?label=tests" alt="tests status">
   </a>
   <a href="https://codecov.io/gh/Mohammad-Alavi/apiato-rector">
      <img src="https://img.shields.io/codecov/c/github/Mohammad-Alavi/apiato-rector?token=c6e0b5g9GH" alt="code coverage"/>
   </a>
</p>

# Apiato Rector

A set of [Rector](https://getrector.org/) rules to automatically upgrade your Apiato project to the latest version.

## Installation

```bash
composer require --dev mohammad-alavi/apiato-rector dev-latest
```

Also ensure you have Rector itself installed:

```bash
composer require --dev rector/rector
```

## Usage

```bash
php vendor/bin/rector
```

### Rules

#### `TransformMethodToResponseFacadeRector`
Converts `$this->transform(...)` calls to `Response::create(...)`.

```php
use MohammadAlavi\ApiatoRector\Rules\TransformMethodToResponseFacadeRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/config',
    ])
    ->withImportNames(true, false, false, true)
    ->withRules([
        TransformMethodToResponseFacadeRector::class,
    ]);
```

#### `RefactorHttpExceptionRector`
Helps refactor exception classes to the new HTTP exception signature.

```php
use MohammadAlavi\ApiatoRector\Rules\RefactorHttpExceptionRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/config',
    ])
    ->withImportNames(true, false, false, true)
    ->withConfiguredRule(RefactorHttpExceptionRector::class, [
        'parent_class' => \App\Ship\Parents\Exceptions\HttpException::class,
    ]);
```
