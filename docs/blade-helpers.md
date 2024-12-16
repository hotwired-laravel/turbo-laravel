---
layout: _layouts.v1-docs
title: Blade Directives
description: Blade Directives
order: 4
---

# Blade Directives and Components

## The `@domid()` Blade Directive

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

[Continue to Helper Functions...](/docs/{{version}}/helper-functions)
