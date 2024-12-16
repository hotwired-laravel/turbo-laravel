---
layout: _layouts.docs
title: Helpers
description: All the helpers the package provides
order: 5
---

# Helpers

Turbo Laravel has a set of Blade Directives, Components, helper functions, and request/response macros to help making the most out of Turbo in Laravel.

## Blade Directives

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

## Blade Components

### The `<x-turbo::frame>` Blade Component

You may also prefer using the `<x-turbo::frame>` Blade component that ships with the package. This way, you don't need to worry about using the `@domid()` helper for your Turbo Frame:

```blade
<x-turbo::frame :id="$post">
    <!-- Content -->
</x-turbo::frame>
```

To the `:id` prop, you may pass a string, which will be used as-is as the DOM ID, an Eloquent model instance, which will be passed to the `dom_id()` function that ships with the package (the same one as the `@domid()` Blade directive uses behind the scenes), or an array tuple where the first item is an instance of an Eloquent model and the second is the prefix of the DOM ID, something like this:

```blade
<x-turbo::frame :id="[$post, 'comments']">
    <!-- Comments -->
</x-turbo::frame>
```

Additionally, you may also pass along any prop that is supported by the Turbo Frame custom Element to the `<x-turbo::frame>` Blade component, like `target`, `src`, or `loading`. These are the listed attributes, but any other attribute will also be forwarded to the `<turbo-frame>` tag that will be rendered by the `<x-turbo::frame>` component. For a full list of what's possible to do with Turbo Frames, see the [documentation](https://turbo.hotwired.dev/handbook/frames).

### The `<x-turbo::stream>` Blade Component

If you're rendering a Turbo Stream inside a your Blade files, you may use the `<x-turbo::stream>` helper:

```blade
<x-turbo::stream :target="$post" action="update">
    @include('posts.partials.post', ['post' => $post])
<x-turbo::stream>
```

Just like in the Turbo Frames' `:id` prop, the `:target` prop of the Turbo Stream component accepts a string, a model instance, or an array to resolve the DOM ID using the `dom_id()` function.

### The `<x-turbo::refresh-method method="morph" />` Blade Component

We can configure which update method Turbo should so to update the document:

| Method | Description |
|---|---|
| `replace` | Updates the entire body of the document on Turbo Visits |
| `morph` | Uses DOM morphing to update the document instead of replacing everything |

Here's how you can use it:

```blade
<x-turbo::refresh-method method="morph" />
```

The output would be:

```blade
<meta name="turbo-refresh-method" content="morph">
```

### The `<x-turbo::refresh-scroll scroll="preserve" />` Blade Component

You can also configure the scroll behavior on Turbo:

| Behavior | Description |
|---|---|
| `reset` | Resets the scroll position to the top, mimicking for the browser handles new page visits |
| `preserve` | Preserves the current scroll position (usually results in a better UX when used with the `morph` method) |

Here's how you can use it:

```blade
<x-turbo::refresh-scroll scroll="preserve" />
```

The output would be:

```blade
<meta name="turbo-refresh-scroll" content="preserve">
```

### The `<x-turbo::refreshes-with>` Blade Component

You may configure both the refresh method and scroll behavior using the `<x-turbo::refreshes-with />` component in your main layout's `<head>` tag or on specific pages to configure how Turbo should update the page. Here's an example:

```blade
<x-turbo::refreshes-with method="morph" scroll="preserve" />
```

This will render two HTML `<meta>` tags:

```html
<meta name="turbo-refresh-method" content="morph">
<meta name="turbo-refresh-scroll" content="preserve">
```

### The `<x-turbo::exempts-page-from-cache />` Blade Component

This component may be added to any page you don't want Turbo to keep a cache in the page cache. Example:

```blade
<x-turbo::exempts-page-from-cache />
```

It will render the HTML `<meta>` tag:

```html
<meta name="turbo-cache-control" content="no-cache">
```

### The `<x-turbo::exempts-page-from-preview />` Blade Component

This component may be added to any page you don't want Turbo to show as a preview on regular navigation visits. No-preview pages will only be used in restoration visits (when you use the browser's back or forward buttons, or when when moving backward in the navigation stack). Example:

```blade
<x-turbo::exempts-page-from-preview />
```

It will render the HTML `<meta>` tag:

```html
<meta name="turbo-cache-control" content="no-preview">
```

### The `<x-turbo::page-requires-reload />` Blade Component

This component may be added to any page you want Turbo to reload. This will break out of Turbo Frame navigations. May be used at a login screen, for instance. Example:

```blade
<x-turbo::page-requires-reload />
```

It will render the HTML `<meta>` tag:

```html
<meta name="turbo-visit-control" content="reload">
```

## Helper Functions

The package ships with a set of helper functions. These functions are all namespaced under `HotwiredLaravel\\TurboLaravel\\` but we also add them globally for convenience, so you may use them directly without the `use` statements (this is useful in contexts like Blade views, for instance).

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

## Request & Response Macros

### The `request()->wantsTurboStream()` macro

The `request()->wantsTurboStream()` macro added to the request class will check if the request accepts Turbo Stream and return `true` or `false` accordingly.

Turbo will add a `Accept: text/vnd.turbo-stream.html, ...` header to the requests. That's how we can detect if the request came from a client using Turbo.

### The `request()->wasFromTurboFrame()` macro

The `request()->wasFromTurboFrame()` macro added to the request class will check if the request was made from a Turbo Frame. When used with no parameters, it returns `true` if the request has a `Turbo-Frame` header, no matter which specific Turbo Frame.

Aditionally, you may specific the optional `$frame` parameter. When that's passed, it returns `true` if it has a `Turbo-Frame` header where the value matches the specified `$frame`. Otherwise, it will return `false`:

```php
if (request()->wasFromTurboFrame(dom_id($post, 'create_comment'))) {
    // ...
}
```

### The `request()->wasFromHotwireNative()` macro

The `request()->wasFromHotwireNative()` macro added to the request class will check if the request came from a Hotwire Native client and returns `true` or `false` accordingly.

Hotwire Native clients are encouraged to override the `User-Agent` header in the WebViews to mention the words `Hotwire Native` on them. This is what this macro uses to detect if it came from a Hotwire Native client.

### The `response()->turboStream()` macro

The `response()->turboStream()` macro works similarly to the `turbo_stream()` function above. It was only added to the response for convenience.

### The `response()->turboStreamView()` macro

The `response()->turboStreamView()` macro works similarly to the `turbo_stream_view()` function above. It was only added to the response for convenience.

[Continue to Turbo Frames...](/docs/{{version}}/turbo-frames)
