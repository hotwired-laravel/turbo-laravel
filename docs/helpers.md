# Helpers

[TOC]

## Blade Helpers

### The `@domid()` Blade Directive

Since Turbo relies a lot on DOM IDs, the package offers a helper to generate unique DOM IDs based on your models. You may use the `@domid` Blade Directive in your Blade views like so:

```blade
<turbo-frame id="@domid($post)">
    <!-- Content -->
</turbo-frame>
```

This will generate a DOM ID string using your model's basename and its ID, such as `post_123`. You may also give it a prefix that will be added to the DOM ID, such as:

```blade
<turbo-frame id="@domid($post, 'comments')">
    <!-- Comments -->
</turbo-frame>
```

Which will generate a `comments_post_123` DOM ID, assuming your Post model has an ID of `123`.

### Blade Components

You may also prefer using the `<x-turbo-frame>` Blade component that ships with the package. This way, you don't need to worry about using the `@domid()` helper for your Turbo Frame:

```blade
<x-turbo-frame :id="$post">
    <!-- Content -->
</x-turbo-frame>
```

To the `:id` prop, you may pass a string, which will be used as-is as the DOM ID, an Eloquent model instance, which will be passed to the `dom_id()` function that ships with the package (the same one as the `@domid()` Blade directive uses behind the scenes), or an array tuple where the first item is an instance of an Eloquent model and the second is the prefix of the DOM ID, something like this:

```blade
<x-turbo-frame :id="[$post, 'comments']">
    <!-- Comments -->
</x-turbo-frame>
```

Additionally, you may also pass along any prop that is supported by the Turbo Frame custom Element to the `<x-turbo-frame>` Blade component, like `target`, `src`, or `loading`. These are the listed attributes, but any other attribute will also be forwarded to the `<turbo-frame>` tag that will be rendered by the `<x-turbo-frame>` component. For a full list of what's possible to do with Turbo Frames, see the [documentation](https://turbo.hotwired.dev/handbook/frames).

## Helper Functions

The package ships with a set of helper functions. These functions are all namespaced under `HotwiredLaravel\\TurboLaravel\\` but we also add them globally for convenience.

### The `dom_id()`

The mentioned namespaced `dom_id()` helper function may also be used from anywhere in your application, like so:

```php
use function HotwiredLaravel\TurboLaravel\dom_id;

dom_id($comment);
```

When a new instance of a model is passed to any of these DOM ID helpers, since it doesn't have an ID, it will prefix the resource name with a `create_` prefix. This way, new instances of an `App\\Models\\Comment` model will generate a `create_comment` DOM ID.

These helpers strip out the model's FQCN (see [config/turbo-laravel.php](https://github.com/hotwired-laravel/turbo-laravel/blob/main/config/turbo-laravel.php) if you use an unconventional location for your models).

### The `dom_class()`

The `dom_class()` helper function may be used from anywhere in your application, like so:

```php
use function HotwiredLaravel\TurboLaravel\dom_class;

dom_class($comment);
```

This function will generate the DOM class named based on your model's classname. If you have an instance of a `App\Models\Comment` model, it will generate a `comment` DOM class.

Similarly to the `dom_id()` function, you may also pass a context prefix as the second parameter:

```php
dom_class($comment, 'reactions_list');
```

This will generate a DOM class of `reactions_list_comment`.

### The `turbo_stream()`

You may generate Turbo Streams using the `Response::turboStream()` macro, but you may also do so using the `turbo_stream()` helper function:

```php
use function HotwiredLaravel\TurboLaravel\turbo_stream;

turbo_stream()->append($comment);
```

Both the `Response::turboStream()` and the `turbo_stream()` function work the same way. The `turbo_stream()` function may be easier to use.

### The `turbo_stream_view()`

You may combo Turbo Streams using the `turbo_stream([])` function passing an array, but you may prefer to create a separate Blade view with all the Turbo Streams, this way you may also use template extensions and everything else Blade offers:

```php
use function HotwiredLaravel\TurboLaravel\turbo_stream_view;

return turbo_stream_view('comments.turbo.created', [
    'comment' => $comment,
]);
```

---

All these functions are also registered globally, so you may use it directly without the `use` statements (this is useful in contexts like Blade views, for instance).

## Request & Response Macros

### The `request()->wantsTurboStream()` macro

The `request()->wantsTurboStream()` macro added to the request class will check if the request accepts Turbo Stream and return `true` or `false` accordingly.

Turbo will add a `Accept: text/vnd.turbo-stream.html, ...` header to the requests. That's how we can detect if the request came from a client using Turbo.

### The `request()->wasFromTurboFrame()` macro

The `request()->wasFromTurboFrame()` macro added to the request class will check if the request was made from a Turbo Frame. When used without the `$frame` optional parameter, it returns `true` if the request has a `Turbo-Frame` header, no matter which specific Turbo Frame. Aditionally, you may specific the optional `$frame` optional parameter, when that's the case, it will return `true` if it has a `Turbo-Frame` header where the value matches the specified `$frame`. Otherwise, it will return `false`.

### The `request()->wasFromTurboNative()` macro

The `request()->wasFromTurboNative()` macro added to the request class will check if the request came from a Turbo Native client and returns `true` or `false` accordingly.

Turbo Native clients are encouraged to override the `User-Agent` header in the WebViews to mention the words `Turbo Native` on them. This is what this macro uses to detect if it came from a Turbo Native client.

### The `response()->turboStream()` macro

The `response()->turboStream()` macro works similarly to the `turbo_stream()` function above. It was only added to the response for convenience.

### The `response()->turboStreamView()` macro

The `response()->turboStreamView()` macro works similarly to the `turbo_stream_view()` function above. It was only added to the response for convenience.

[Continue to Turbo Streams...](/docs/{{version}}/turbo-streams)
