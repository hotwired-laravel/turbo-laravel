# Installation

Turbo Laravel can be installed via [Composer](https://getcomposer.org/):

```bash
composer require hotwired-laravel/turbo-laravel:2.x-dev
```

After installing the package, you may run the `turbo:install` Artisan command. This command will add the Turbo.js dependency to your `package.json` file (when you're using Vite and NPM) or to your `routes/importmap.php` file (when it detects that you're using [Importmap Laravel](https://github.com/tonysm/importmap-laravel)). It also publishes some files to your `resources/js` folder, which imports Turbo for you:

```bash
php artisan turbo:install
```

If you're using [Laravel Jetstream](https://jetstream.laravel.com/introduction.html) with [Livewire](https://livewire.laravel.com/), you may add the `--jet` flag to the `turbo:install` Artisan command. This flag will add some required JS dependencies to make sure [Alpine.js](https://alpinejs.dev/) works well with Turbo. It also changes the layout files that ships with Jetstream adding the [Livewire Turbo Plugin](https://github.com/livewire/turbolinks) to make sure Livewire works well with Turbo, too:

```bash
php artisan turbo:install --jet
```

If you're using [Alpine.js](https://alpinejs.dev/) in a non-Jetstream context (maybe you're more into [Breeze](https://laravel.com/docs/starter-kits#laravel-breeze)), you may use the `--alpine` flag in the `turbo:install` Artisan command:

```bash
php artisan turbo:install --alpine
```

[Continue to Overview...](/docs/{{version}}/overview)
