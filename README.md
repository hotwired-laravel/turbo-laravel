# Turbo Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tonysm/turbo-laravel.svg?style=flat-square)](https://packagist.org/packages/tonysm/turbo-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/tonysm/turbo-laravel/run-tests?label=tests)](https://github.com/tonysm/turbo-laravel/actions?query=workflow%3ATests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/tonysm/turbo-laravel.svg?style=flat-square)](https://packagist.org/packages/tonysm/turbo-laravel)

This package gives you a set of conventions to make the most out of the Hotwire stack (inspired by turbo-rails gem).

## Installation

You can install the package via composer:

```bash
composer require tonysm/turbo-laravel
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Tonysm\TurboLaravel\TurboLaravelServiceProvider" --tag="config"
php artisan turbo:install
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
// TODO.
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Drop me an email at [tonysm@hey.com](mailto:tonysm@hey.com?subject=Security%20Vulnerability) if you want to report security vulnerabilities.

## Credits

- [Tony Messias](https://github.com/tonysm)
- [All Contributors](./CONTRIBUTORS.md)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
