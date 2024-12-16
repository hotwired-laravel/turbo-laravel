---
layout: _layouts.docs
title: Overview
description: A quick overview of Hotwire
order: 3
---

# Overview

It's recommended to read the entire [Turbo Handbook](https://turbo.hotwired.dev/handbook/introduction) before diving here. But here's a quick intro.

Turbo offers a set of components that helps us building modern web applications with minimal JavaScript. It relies on sending HTML Over The Wire (that's the the name comes from) instead of JSON, which is how common JavaScript-heavy web applications do.

When Turbo is started in the browser, it will intercet app link clicks and form submissions and convert those into fetch requests (aka. AJAX) instead of letting the browser do a full page refresh. The component in Turbo that handles this is called [Turbo Drive](https://turbo.hotwired.dev/handbook/drive).

Turbo Drive is nice when you want to have a full page visits. However, there are times where we want to focus the update on specific elements on the page, without affecting the other existing elements. To achieve that, Turbo adds two new custom HTML tags, which we can use to enchance the User Experience (UX): [Turbo Frames](https://turbo.hotwired.dev/handbook/frames) (`<turbo-frame>`) and Turbo Streams (`<turbo-stream>`). This is all available in the browser when we install Turbo, we only have to use these new tags if we want to.

Turbo Frames allows us to scope a Turbo visit to a specific `<turbo-frame>` element. When inside of a Turbo Frame tag, link clicks and form submissions will **NOT** replace the entire body of the document, but instead Turbo will look for a matching Turbo Frame tag in the response using its DOM ID and replace that specific portion of the page.

Here's how you can use Turbo Frames:

```html
<turbo-frame id="my_frame">
    <h1>Hello, World!</h1>
    <a href="/somewhere">
        I'm a trigger. My response must have a matching Turbo Frame tag (same ID)
    </a>
</turbo-frame>
```

We may also configure Turbo Frames to lazy-load its contents by adding a `src` attribute to the Turbo Frame tag. The content of a lazy-loading Turbo Frame tag can be used to indicate "loading states":

```blade
<turbo-frame id="my_frame" src="{{ route('my.page') }}">
    <p>Loading...</p>
</turbo-frame>
```

A lazy-loaded Turbo Frame will dispatch a fetch request (aka. AJAX) as soon as it enters the DOM, replacing its contents with the contents of a matching Turbo Frame in the response. We may defer the request so it's only dispatched when the Turbo Frame element is visible in the viewport with by setting the `loading="lazy"` attribute:

```blade
<turbo-frame id="my_frame" src="{{ route('my.page') }}" loading="lazy">
    <p>Loading...</p>
</turbo-frame>
```

You may also trigger a Turbo Frame with forms and links that are _outside_ of the frame tag by adding a `data-turbo-frame` attribute in the link, form, or submit buttons, passing the ID of the Turbo Frame:

```blade
<div>
    <a href="/somewhere" data-turbo-frame="my_frame">I'm a link</a>

    <turbo-frame id="my_frame">
        <!-- Content -->
    </turbo-frame>
</div>
```

Turbo Streams, the other custom HTML tag that ships with Turbo, is a bit different. We can use Turbo Stream tags to trigger some actions on the document. For instance, here's Turbo Stream that appends a new comment into the comments list that already lives on the page:

```html
<turbo-stream action="append" target="comments">
    <template>
        <div id="comment_123">
            <p>Lorem ipsum...</p>
        </div>
    </template>
</turbo-stream>
```

In this case, this new comment of DOM ID `#comment_123` will be _appended_ into the list of comments, which has the `#comments` DOM ID. All the default Turbo Stream actions, except the `refresh` one, require a `target` or a `targets` attribute. The difference here is that if you use the `target` attribute, it expects a DOM ID of the target element, and if you use the `targets` attribute, it expects a CSS selector of the target(s) element(s).

There are 8 default Turbo Stream actions in Turbo:

| Action | Description |
|---|---|
| `append` | Appends the contents of the `<template>` tag into the target or targets |
| `prepend` | Prepends the contents of the `<template>` tag to the target or targets |
| `update` | Updates the target or targets with the contents of the `<template>` tag (keeps the targeted elements around) |
| `replace` | Replaces the target or targets with the contents of the `<template>` tag (actually removes the targets) |
| `before` | Inserts the contents of the `<template>` tag _before_ the targeted elements |
| `after` | Inserts the contents of the `<template>` tag _after_ the targeted elements |
| `remove` | Removes the targeted elements (doesn't require a `<template>` tag) |
| `refresh` | Signals to Turbo Drive to do a page refresh (doesn't require a `<template>` tag, nor "target") |

All of the default actions require a the contents of the new or updated element to be wrapped in a `<template>` tag, except `remove` and `refresh`. That's because Turbo Stream tags can be activated by simply adding them to the document. They'll get activate based on the action and then get removed from the DOM. Having the `<template>` ensure the content is not visible in the browser as it gets activated, only after.

I keep saying "default action", well, that's because Turbo allows us to create our own [custom actions](https://turbo.hotwired.dev/handbook/streams#custom-actions):

```js
import { StreamActions } from "@hotwired/turbo"

StreamActions.log = function () {
  console.log(this.getAttribute("message"))
}
```

In this case, we can use this action like so:

```html
<turbo-stream action="log" message="Hello World"></turbo-stream>
```

This will get "Hello World" printed on the DevTools Console. With custom actions, you can do pretty much anything on the document.

So far, all vanilla Hotwire and Turbo.

[Continue to Conventions...](/docs/{{version}}/conventions)
