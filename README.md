# Turbo Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tonysm/turbo-laravel.svg?style=flat-square)](https://packagist.org/packages/tonysm/turbo-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/tonysm/turbo-laravel/run-tests?label=tests)](https://github.com/tonysm/turbo-laravel/actions?query=workflow%3ATests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/tonysm/turbo-laravel.svg?style=flat-square)](https://packagist.org/packages/tonysm/turbo-laravel)

This package gives you a set of conventions to make the most out of the Hotwire stack in Laravel (inspired by turbo-rails gem).

## Installation

You can install the package via composer:

```bash
composer require tonysm/turbo-laravel
```

You can publish the asset files with:

```bash
php artisan turbo:install
```

## Usage

TODOS:

- ~Handle form validation (not sure yet what's the best way to use this)~
- Document using `Broadcasts` trait in a model
- Document overriding the `$broadcastsTo` in the model to point to a related model
- Document overriding conventions
- Document conventions (partials, variable names, dom IDs...)
- Extract "extension" point to contracts
- Document using the `@turbonative` directive
- Document Turbo Stream responses from the browser
- ~Update Livewire to work with Turbo~ (PRs sent [here](https://github.com/livewire/livewire/pull/2279) and [here](https://github.com/livewire/turbolinks/pull/12))

If you want a model to automatically publish Turbo Stream messages over WebSockets using Laravel Echo, you can use the trait that ships with this package, like so:

```php

use Illuminate\Database\Eloquent\Model;
use Tonysm\TurboLaravel\Models\Broadcasts;

class TaskList extends Model
{
    use Broadcasts;
}

class Task extends Model
{
    use Broadcasts;
    
    public $broadcastsTo = 'taskList';
    
    public function taskList()
    {
        return $this->belongsTo(TaskList::class);
    }
}
```

This package also ships with some Blade Components to help you building the turbo tags. They are probably very simple, but using the package components will make it easy to upgrade in the future, as the package will handle upgrading for you. You can use the blade components like so:

```html
<turbo-stream target="my_frame" action="append">
    <template>
        <h1>Here's the you want appended at the #my_frame elemenet.</h1>
    </template>
</turbo-stream>

<turbo-frame id="my_frame">
    <h1>Here's an example of a frame.</h1>
</turbo-frame>

<turbo-frame id="my_frame" src="{{ route('my.routes') }}">
    <h1>Loading...</h1>
    <p>This is an example of a lazy-loading frame. It will replace this content with a matching frame after the AJAX request is sent to the `src` location above.</p>
</turbo-frame>
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
