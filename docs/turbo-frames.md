---
layout: _layouts.docs
title: Turbo Frames
description: The Turbo Frames Components and Helpers
order: 6
---

# Turbo Frames

The Turbo Frame tag that ships with Turbo can be used on your Blade views just like any other HTML tag:

```blade
<turbo-frame id="@domid($post, 'create_comment')">
    <p>Loading...</p>
</turbo-frame>
```

In this case, the `@domid()` directive is being used to create a dom ID that looks like this `create_comment_post_123`. There's also a Blade Component that ships with Turbo Laravel and can be used like this:

```blade
<x-turbo::frame :id="[$post, 'create_comment']">
    <p>Loading...</p>
</x-turbo::frame>
```

When using the Blade Component, you don't have to worry about using the `@domid()` directive or the `dom_id()` function, as this gets handled automatically by the package. You may also pass a string if you want to enforce your own DOM ID.

Any other attribute passed to the Blade Component will get forwarded to the underlying `<turbo-frame>` element, so if you want to turn a Turbo Frame into a lazy-loading Turbo Frame using the Blade Component, you can do it like so:

```blade
<x-turbo::frame :id="[$post, 'create_comment']" loading="lazy" :src="route('post.comments.create', $post)">
    <p>Loading...</p>
</x-turbo::frame>
```

This will work for any other attribute you want to forward to the underlying component.

## The `request()->wasFromTurboFrame()` Macro

You may want to detect if a request came from a Turbo Frame in the backend. You may use the `wasFromTurboFrame()` method for that:

```php
if ($request->wasFromTurboFrame()) {
    // ...
}
```

When used like this, the macro will return `true` if the `X-Turbo-Frame` custom HTTP header is present in the request (which Turbo adds automatically), or `false` otherwise.

You may also check if the request came from a specific Turbo Frame:

```php
if ($request->wasFromTurboFrame(dom_id($post, 'create_comment'))) {
    // ...
}
```

[Continue to Turbo Streams...](/docs/{{version}}/turbo-streams)
