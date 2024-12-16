---
layout: _layouts.v1-docs
title: Convetions
description: All the (optional) conventions and recommendations
order: 3
---

# Conventions

The conventions described below are **NOT mandatory**. Feel free to pick what you like and also come up with your own conventions. With that out of the way, here's a list of conventions you may find helpful.

## Resource Routes

Laravel supports [resource routes](https://laravel.com/docs/controllers#resource-controllers) and that plays really well with Hotwire for most things. This creates routes names such as `posts.index`, `posts.store`, etc.

If you don't want to use resource routes, at least consider using the naming convention: render forms in a route name ending in `.create` or `.edit` and name their handler routes ending with `.store` or `.update`.

You may want to define exceptions to the route guessing behavior. In that case, you may want to set the `redirect_guessing_exceptions` in the `config/turbo-laravel.php` config file:

```php
return [
    // ...
    'redirect_guessing_exceptions' => [
        '/some-page',
    ],
];
```

When using this config, the redirection behavior will still happen, but the package will not guess routes. See the [Validation Response Redirects](/docs/{{version}}/validation-response-redirects) page to know more about why this happens.

## Partials

You may want to split up your views in smaller chunks (aka. "partials"), such as `comments/_comment.blade.php` which displays a comment resource, or `comments/_form.blade.php` for the form to either create/update comments. This will allow you to reuse these _partials_ in [Turbo Streams](/docs/{{version}}/turbo-streams).

The models' partials (such as the `comments/_comment.blade.php` for a `Comment` model) may only rely on having a single `$comment` instance variable passed to them. That's because the package will, by default, figure out the partial for a given model when broadcasting and will also pass the model to such partial, using the class basename as the variable instance in _camelCase_. Again, that's by default, you can customize most things. Read the [Broadcasting](/docs/{{version}}/broadcasting) section to know more.

## Turbo Stream Channel Names

You may use the model's Fully Qualified Class Name (aka. FQCN) as your Broadcasting Channel authorization routes with a wildcard, such as `App.Models.Comment.{comment}` for a `Comment` model living in `App\\Models\\` - the wildcard's name doesn't matter, as long as there is one. This is the default [broadcasting channel naming convention](https://laravel.com/docs/8.x/broadcasting#model-broadcasting-conventions) in Laravel.

[Continue to Blade Helpers...](/docs/{{version}}/blade-helpers)
