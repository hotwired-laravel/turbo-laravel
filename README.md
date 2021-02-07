<p align="center">

# ⚡ Turbo Laravel

<a href="https://github.com/tonysm/turbo-laravel/workflows/Tests/badge.svg">
    <img src="https://img.shields.io/github/workflow/status/tonysm/turbo-laravel/Tests?label=tests" />
</a>
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

**This package gives you a set of conventions to make the most out of [Hotwire](https://hotwire.dev/) in Laravel** (inspired by the [turbo-rails](https://github.com/hotwired/turbo-rails) gem). There is a [companion application](https://github.com/tonysm/turbo-demo-app) that shows how to use the package and its conventions in your application.

<a name="installation"></a>
## Installation

You may install the package via composer:

```bash
composer require tonysm/turbo-laravel
```

You may publish the asset files with:

```bash
php artisan turbo:install
```

You may also use Turbo Laravel with Jetstream if you use the Livewire stack. If you want to do so, you may want to publish the assets using the `--jet` flag:

```bash
php artisan turbo:install --jet
```

The install command will require and publish a couple JS files to your application. By default, it will add `@hotwired/turbo` to your `package.json` file and publish another custom HTML tag to integrate Turbo with Laravel Echo. With the `--jet` flag, it will also add a couple bridges libs needed to make sure you can use Hotwire with Jetstream, these are:

* [Alpine Turbo Bridge](https://github.com/SimoTod/alpine-turbo-drive-adapter), needed so Alpine.js works nicely; and
* [Livewire Turbo Plugin](https://github.com/livewire/turbolinks) needed so Livewire works nicely. This one will be added to your Jetstream layouts (both `app.blade.php` and `guest.blade.php`)

You can optionally also install Stimulus on top of this all by passing `--stimulus` flag to the `turbo:install` command. It's optional because we can either use Alpine.js or Stimulus (or both /shrug):

```bash
php artisan turbo:install --jet --stimulus
```

The package ships with a middleware that applies some conventions on your redirects, specially around how failed validations are redirected automatically by Laravel. To read more about this, check out the [Conventions](#conventions) section documentation. You may add the middleware to the "web" route group on your HTTP Kernel, like so:

```php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middlewareGroups = [
        'web' => [
            // ...
            \Tonysm\TurboLaravel\Http\Middleware\TurboMiddleware::class,
        ],
    ];
}
```

Keep reading the documentation to have a full picture on how you can make the most out of the technique.

### Closing Notes

Try the package out. Use your Browser's DevTools to inspect the responses. You will be able to spot every single Turbo Frame and Turbo Stream happening.

> "The proof of the pudding is in the eating."

Make something awesome!

<a name="documentation"></a>
## 🧐 Documentation

* [Conventions](./docs/01-CONVENTIONS.md#conventions)
* [Overview](./docs/02-OVERVIEW.md#overview)
    * [Notes on Turbo Drive and Turbo Frames](./docs/02-OVERVIEW.md#notes-on-turbo-drive-and-turbo-frames)
    * [Turbo Frames](./docs/02-OVERVIEW.md#turbo-frames)
    * [Turbo Streams](./docs/02-OVERVIEW.md#turbo-streams)
    * [Validation Response Redirects](./docs/02-OVERVIEW.md#validation-responses)
    * [Turbo Native](./docs/02-OVERVIEW.md#turbo-native)
    * [Testing Helpers](./docs/02-OVERVIEW.md#testing-helpers)

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## 🙏 Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## 🛡 Security Vulnerabilities

Drop me an email at [tonysm@hey.com](mailto:tonysm@hey.com?subject=Security%20Vulnerability) if you want to report
security vulnerabilities.

## 📝 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

- [Tony Messias](https://github.com/tonysm)
- [All Contributors](./CONTRIBUTORS.md)
