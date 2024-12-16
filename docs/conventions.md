---
layout: _layouts.docs
title: Convetions
description: All the (optional) conventions and recommendations
order: 4
---

# Conventions

The conventions described below are **NOT mandatory**. Feel free to pick what you like and also come up with your own conventions. With that out of the way, here's a list of conventions you may find helpful.

## Resource Routes

Laravel supports [resource routes](https://laravel.com/docs/controllers#resource-controllers) and that plays really well with Hotwire for most things. This creates route names such as `posts.index`, `posts.store`, etc.

If you don't want to use resource routes, at least consider using the naming convention: render forms in a route names ending in `.create`, `.edit`, or `.delete`, and name their handler routes ending with `.store`, `.update`, or `.destroy`, accordingly.

Turbo Laravel uses this naming convention so it doesn't redirect after failed valitions and, instead, triggers another internal request to the application as well so it can re-render the form returning a 422 response with. The form should re-render with the `old()` input values and any validation messages as well.

You may want to define exceptions to the route guessing behavior. In that's the case, set them in the `redirect_guessing_exceptions` in the `config/turbo-laravel.php` config file:

```php
return [
    // ...
    'redirect_guessing_exceptions' => [
        '/some-page',
    ],
];
```

When using this config, the redirection behavior will still happen, but the package will not attempt to guess the routes that render the forms on those routes. See the [Validation Response Redirects](/docs/{{version}}/validation-response-redirects) page to know more about why this happens.

## Partials

You may want to split up your views in smaller chunks (aka. "partials"), such as a `comments/_comment.blade.php` to display a comment resource, or `comments/_form.blade.php` to display the form for both creating and updating comments. This allows you to reuse these _partials_ in [Turbo Streams](/docs/{{version}}/turbo-streams).

Alternatively, you may override the pattern to a `{plural}.partials.{singular}` convention for your partials location by calling the `Turbo::usePartialsSubfolderPattern()` method of the Turbo Facade from your `AppServiceProvider::boot()` method:

```php
<?php

namespace App\Providers;

use HotwiredLaravel\TurboLaravel\Facades\Turbo;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Turbo::usePartialsSubfolderPattern();
    }
}
```

You may also want to define your own pattern for partials, which you can do using the `Turbo::resolvePartialsUsing()` method. This method accepts either a string pattern or a Closure. If you pass a string pattern, you'll have two replaceholders available: `{singular}` and `{plural}`, which will get replaced with the model's singular and plural names, respectively, when the pattern is used. If you pass a Closure, you'll have the model instance available and you must return the view pattern using Laravel's dot notation convention. The pattern returned from the Closure will also get the placeholders applied, if you need that. Here's how you could manually define the partials subfolder pattern:

```php
<?php

namespace App\Providers;

use HotwiredLaravel\TurboLaravel\Facades\Turbo;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Turbo::resolvePartialsPathUsing('{plural}.partials.{singular}');

        // Or...

        Turbo::resolvePartialsPathUsing(fn ($model) => 'partials.{singular}');
    }
}
```

You may also want to define your own pattern, which you can do by either specifying a string where you have the `{plural}` and `{singular}` placeholders available, but you can also specify a Closure, which will receive the model instance. On that Closure, you must return a string with the view location using the dot convention of Laravel. For instance, the subfolder pattern sets the config value to `{plural}.partials.{singular}` instead of the default, which is `{plural}._{singular}`. These will resolve to `comments.partials.comment` and `comments._comment` views, respectively.

The models' partials (such as a `comments/_comment.blade.php` for a `Comment` model) may only rely on having a single `$comment` variable passed to them. That's because Turbo Stream Model Broadcasts - which is an _optional_ feature, by the way - relies on these conventions to figure out the partial for a given model when broadcasting and will also pass the model to such partial, using the class basename as the variable instance in _camelCase_. Again, this is optional, you can customize most of these things or create your own model broadcasting convention. Read the [Broadcasting](/docs/{{version}}/broadcasting) section to know more.

## Turbo Stream Channel Names

_Note: Turbo Stream Broadcasts are optional._

You may use the model's Fully Qualified Class Name (aka. FQCN) as your Broadcasting Channel authorization routes with a wildcard, such as `App.Models.Comment.{comment}` for a `Comment` model living in `App\\Models\\` - the wildcard's name doesn't matter, as long as there is one. This is the default [broadcasting channel naming convention](https://laravel.com/docs/8.x/broadcasting#model-broadcasting-conventions) in Laravel.

[Continue to Helpers...](/docs/{{version}}/helpers)
