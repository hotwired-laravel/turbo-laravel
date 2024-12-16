---
layout: _layouts.docs
title: Installation
description: Install Turbo Laravel in your Laravel app
order: 2
---

# Installation

Turbo Laravel can be installed via [Composer](https://getcomposer.org/):

```bash
composer require hotwired-laravel/turbo-laravel
```

After installing the package, you may run the `turbo:install` Artisan command. This command will add the Turbo.js dependency to your `package.json` file (when you're using Vite and NPM) or to your `routes/importmap.php` file (when it detects that you're using [Importmap Laravel](https://github.com/tonysm/importmap-laravel)). It also publishes some files to your `resources/js` folder, which imports Turbo for you:

```bash
php artisan turbo:install
```

Note: Turbo used to work with Livewire, but somewhere around Livewire V3 the bridges stopped working. There's an open issue to investigate Livewire V3 compatibility. If you're into Livewire and would love to use Turbo in a Livewire app (maybe you want to augment your Livewire & Turbo app with Hotwire Native or something like that), you're welcome to check out the issue and try to bring the compatibility back. If you wanted an application scaffolding like Laravel Breeze or Laravel Jetstream, checkout Turbo Breeze, our fork of Breeze that sets up a fresh Laravel app using Stimulus, Importmaps, TailwindCSS (via the CLI), and Turbo.

[Continue to Overview...](/docs/{{version}}/overview)
