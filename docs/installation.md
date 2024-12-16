---
layout: _layouts.v1-docs
title: Installation
description: Installation
order: 1
---

# Installation

Turbo Laravel may be installed via Composer:

```bash
composer require hotwired/turbo-laravel
```

After installing, you may execute the `turbo:install` Artisan command, which will add a couple JS dependencies to your `package.json` file (when you're using Vite and NPM) or to your `routes/importmap.php` file (when you're using [Importmap Laravel](https://github.com/tonysm/importmap-laravel)), publish some JS scripts to your `resources/js` folder that configure Turbo.js for you:

```bash
php artisan turbo:install
```

If you are using Jetstream with Livewire, you may add the `--jet` flag to the `turbo:install` Artisan command, which will add a couple more JS dependencies to make sure Alpine.js works nicely with Turbo.js. This will also change the layout that ships with Jetstream files a bit, which will make sure Livewire works nicely as well:

```bash
php artisan turbo:install --jet
```

When using Jetstream with Livewire, the [Livewire Turbo Plugin](https://github.com/livewire/turbolinks) is needed so Livewire works nicely with Turbo. This one will be added to your Jetstream layouts as script tags fetching from a CDN (both `app.blade.php` and `guest.blade.php`).

If you're not using [Importmap Laravel](https://github.com/tonysm/importmap-laravel), the install command will tell you to pull and compile the assets before proceeding:

```bash
npm install && npm run build
```

You may also optionally install [Alpine.js](https://alpinejs.dev/) in a non-Jetstream context (maybe you're more into [Breeze](https://laravel.com/docs/9.x/starter-kits#laravel-breeze)) passing `--alpine` flag to the `turbo:install` Artisan command:

```bash
php artisan turbo:install --alpine
```

_Note: the `--jet` option also adds all the necessary Alpine dependencies since Jetstream depends on Alpine._

[Continue to Overview...](/docs/{{version}}/overview)
