<p align="center" style="margin-top: 2rem; margin-bottom: 2rem;"><img src="/art/turbo-laravel-logo.svg" alt="Logo Turbo Laravel" /></p>

<p align="center">
    <a href="https://packagist.org/packages/tonysm/turbo-laravel">
        <img src="https://img.shields.io/packagist/dt/tonysm/turbo-laravel" alt="Total Downloads">
    </a>
    <a href="https://packagist.org/packages/tonysm/turbo-laravel">
        <img src="https://img.shields.io/packagist/v/tonysm/turbo-laravel" alt="Latest Stable Version">
    </a>
    <a href="https://packagist.org/packages/tonysm/turbo-laravel">
        <img src="https://img.shields.io/packagist/l/tonysm/turbo-laravel" alt="License">
    </a>
</p>

## Introduction

This package gives you a set of conventions to make the most out of [Hotwire](https://hotwired.dev/) in Laravel.

#### Inspiration

This package was inspired by the [Turbo Rails gem](https://github.com/hotwired/turbo-rails).

#### Bootcamp

If you want a more hands-on introduction, head out to [Bootcamp](https://bootcamp.turbo-laravel.com). It covers building a multi-platform app in Turbo.

## Official Documentation

Documentation for Turbo Laravel can be found on the [Turbo Laravel website](https://turbo-laravel.com).

### Known Issues

If you ever encounter an issue with the package, look here first for documented solutions.

#### Fixing Laravel's Previous URL Issue

Visits from Turbo Frames will hit your application and Laravel by default keeps track of previously visited URLs to be used with helpers like `url()->previous()`, for instance. This might be confusing because chances are that you wouldn't want to redirect users to the URL of the most recent Turbo Frame that hit your app. So, to avoid storying Turbo Frames visits as Laravel's previous URL, head to the [issue](https://github.com/tonysm/turbo-laravel/issues/60#issuecomment-1123142591) where a solution was discussed.

### Closing Notes

Try the package out. Use your Browser's DevTools to inspect the responses. You will be able to spot every single Turbo Frame and Turbo Stream happening.

> "The proof of the pudding is in the eating."

Make something awesome!

## Testing the Package

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Drop me an email at [tonysm@hey.com](mailto:tonysm@hey.com?subject=Security%20Vulnerability) if you want to report
security vulnerabilities.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

- [Tony Messias](https://github.com/tonysm)
- [All Contributors](./CONTRIBUTORS.md)
