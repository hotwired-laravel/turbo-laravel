---
layout: _layouts.v1-docs
title: Helper Functions
description: All the helper functions the package provides
order: 5
---

# Helper Functions

The package ships with a set of helper functions. These functions are all namespaced under `Tonysm\\TurboLaravel\\` but we also add them globally for convenience.

## The `dom_id()`

The mentioned namespaced `dom_id()` helper function may also be used from anywhere in your application, like so:

```php
use function Tonysm\TurboLaravel\dom_id;

dom_id($comment);
```

When a new instance of a model is passed to any of these DOM ID helpers, since it doesn't have an ID, it will prefix the resource name with a `create_` prefix. This way, new instances of an `App\\Models\\Comment` model will generate a `create_comment` DOM ID.

These helpers strip out the model's FQCN (see [config/turbo-laravel.php](https://github.com/tonysm/turbo-laravel/blob/main/config/turbo-laravel.php) if you use an unconventional location for your models).

## The `dom_class()`

The `dom_class()` helper function may be used from anywhere in your application, like so:

```php
use function Tonysm\TurboLaravel\dom_class;

dom_class($comment);
```

This function will generate the DOM class named based on your model's classname. If you have an instance of a `App\Models\Comment` model, it will generate a `comment` DOM class.

Similarly to the `dom_id()` function, you may also pass a context prefix as the second parameter:

```php
dom_class($comment, 'reactions_list');
```

This will generate a DOM class of `reactions_list_comment`.

## The `turbo_stream()`

You may generate Turbo Streams using the `Response::turboStream()` macro, but you may also do so using the `turbo_stream()` helper function:

```php
use function Tonysm\TurboLaravel\turbo_stream;

turbo_stream()->append($comment);
```

Both the `Response::turboStream()` and the `turbo_stream()` function work the same way. The `turbo_stream()` function may be easier to use.

## The `turbo_stream_view()`

You may combo Turbo Streams using the `turbo_stream([])` function passing an array, but you may prefer to create a separate Blade view with all the Turbo Streams, this way you may also use template extensions and everything else Blade offers:

```php
use function Tonysm\TurboLaravel\turbo_stream_view;

return turbo_stream_view('comments.turbo.created', [
    'comment' => $comment,
]);
```

---

All these functions are also registered globally, so you may use it directly without the `use` statements (this is useful in contexts like Blade views, for instance).

[Continue to Turbo Streams...](/docs/{{version}}/turbo-streams)
