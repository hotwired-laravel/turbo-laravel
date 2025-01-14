---
extends: _layouts.docs
title: Overview
description: A quick overview of Hotwire
order: 3
---

# Overview

It's highly recommended that you read the [Turbo Handbook](https://turbo.hotwired.dev/handbook/introduction) first before continuing here. However, a quick intro will be provided here and we'll link to the Turbo documentations when relevant.

Turbo is the heart of Hotwire. In essence, it's a JavaScript library that turns regular web applications (aka. multi-page web applications) into something that _feels_ like a single-page application (SPA).

It provides a bunch of components that allows us to build modern web applications with minimal JavaScript. It relies on sending **H**TML **O**ver **T**he **Wire** (hence the name), instead of JSON, which is how JavaScript-heavy web applications are built, typically consuming some sort of JSON API.

When Turbo.js is started in the browser, it intercepts link clicks and form submissions to convert those into fetch requests (aka. AJAX) instead of letting the browser do a full page refresh. The component in Turbo that handles this behavior is called [Turbo Drive](https://turbo.hotwired.dev/handbook/drive).

Turbo Drive will do the heavy-lifting of the _SPA feel_ in our application. Just by turning it on, the [perceived performance](https://developer.mozilla.org/en-US/docs/Learn_web_development/Extensions/Performance/Perceived_performance) should be noticeable. The default behavior of Turbo will be to _replace_ the contents of the `<body>` tag in our page with the one from the response it gets from the link or form submission.

Additionally, since Turbo 8, we can also instruct Turbo to [_morph_ the page](https://turbo.hotwired.dev/handbook/page_refreshes) instead of just replacing its contents by adding a meta tag on the pages we can it enabled:

```html
<meta name="turbo-refresh-method" content="morph">
<meta name="turbo-refresh-scroll" content="preserve">
```

Alternatively, Turbo Laravel provides some Blade components to make it easier (and autocomplete friendlier) to interact with these Turbo page configurations:

```blade
<x-turbo::refreshes-with method="morph" scroll="preserve" />
```

Turbo Drive does a lot for us, and with _morphing_ it gets even more powerful, but sometimes you can want to [decompose a page into independent sections](https://turbo.hotwired.dev/handbook/frames) (for different reasons, such as having more control over HTTP caching for these sections). For these use cases, Turbo offers _Turbo Frames_.

Turbo Frames are custom HTML tags that Turbo provides. You can think of those as "modern iframes", if you will. When link clicks or form submissions happen inside of a Turbo Frame, instead of replacing or morphing the entire page, Turbo will only affect that specific Turbo Frame's content. It will do so by extracting a _matching Turbo Frame_ (one that has the same DOM ID) on the response.

Here's how you can use Turbo Frames:

```html
<turbo-frame id="my_frame">
    <h1>Hello, World!</h1>

    <a href="/somewhere">Click me</a>
</turbo-frame>
```

Alternatively, you may want a Turbo Frame to immediately fetch its contents instead of waiting for a user interaction. For that, you may add a `[src]` attribute to the Turbo Frame tag with the URL of where Turbo should fetch that content from. This technique is called [Lazy-loading Turbo Frames](https://turbo.hotwired.dev/handbook/frames#lazy-loading-frames):

```blade
<turbo-frame id="my_frame" src="{{ route('my.page') }}">
    <p>Loading...</p>
</turbo-frame>
```

A lazy-loaded Turbo Frame will dispatch a fetch request (aka. AJAX) as soon as it enters the DOM, replacing its contents with the contents of a matching Turbo Frame in the response HTML. Optionally, you may add a `[loading=lazy]` attribute to the lazy-loaded Turbo Frame so Turbo will only fetch its content when the Turbo Frame is visible (within the viewport):

```blade
<turbo-frame id="my_frame" src="{{ route('my.page') }}" loading="lazy">
    <p>Loading...</p>
</turbo-frame>
```

You may also trigger a Turbo Frame with forms and links that are _outside_ of the frame tag by adding a `[data-turbo-frame]` attribute in the link, form, or submit buttons, passing the ID of the Turbo Frame:

```blade
<div>
    <a href="/somewhere" data-turbo-frame="my_frame">I'm a link</a>

    <turbo-frame id="my_frame">
        ...
    </turbo-frame>
</div>
```

Turbo Drive and Turbo Frames allows us to build A LOT of different sorts of interactions. However, sometimes you may want to update multiple sections of a page after a form submission, for instance. For those use cases, Turbo provides another custom HTML tag called [Turbo Streams](https://turbo.hotwired.dev/handbook/streams).

All link clicks and form submissions that Turbo intercepts are annotated by Turbo, which tells our back-end application that Turbo is _on_, so we can return a special type of response that only contains Turbo Streams. Turbo.js will do so by adding a custom [MIME type](https://developer.mozilla.org/en-US/docs/Web/HTTP/MIME_types/Common_types) of `text/vnd.turbo-stream.html` to the [`Accept` HTTP Header](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept).

Turbo Streams allows for a more fine-grained control over the page updates. For instance, here's an example of a Turbro Stream that appends a new comment to a comments section:

```html
<turbo-stream action="append" target="comments">
    <template>
        ...
    </template>
</turbo-stream>
```

The `[action=append]` will add the contents of what's inside the `<template></template>` tag into the element that has a DOM ID matching the `[target=comments]` attribute, so `#comments` in this case.

There are 8 _default_ Turbo Stream actions in Turbo:

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

All the default Turbo Stream actions, except the `refresh` one, require a `target` or a `targets` attribute. The difference here is that if you use the `target` attribute, it expects a DOM ID of the target element, and if you use the `targets` attribute, it expects a CSS selector of the target(s) element(s).

All of the default actions require the contents of the new or updated element to be wrapped inside a `<template>` tag, except for the `remove` and `refresh` actions. That's because Turbo Stream tags can be activated by simply adding them to the document. They'll get activate based on the action and then get removed from the DOM. Having the `<template>` ensure the content is not visible in the browser as it gets activated.

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
