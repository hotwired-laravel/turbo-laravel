---
layout: _layouts.v1-docs
title: Overview
description: Overview
order: 2
---

# Overview

When Turbo.js is installed, Turbo Drive will be enabled by default. Turbo Drive will turn links and form submissions into fetch requests (AJAX) and will replace the page with the response it gets.

You will also have Turbo-specific custom HTML tags that you may use in your views to enhance the user experience: Turbo Frames and Turbo Streams. This is vanilla Hotwire. It's recommended to read the [Turbo Handbook](https://turbo.hotwired.dev/handbook/introduction). Once you understand how these few pieces work together, the challenge will be in decomposing your UI to work as you want them to.

Turbo also allows you to persist across visits. If you want that to happen, you may annotate these elements with a DOM ID and add the `data-turbo-permanent` custom attribute to them. As long as the response also contains an element with the same DOM ID and `data-turbo-permanent` attribute, Turbo will not touch it.

Turbo Drive is nice when you want to have a full page visit. That's not what we always want, though. Sometimes we want to only swap a fragment of the page and keep everything else as is. That's what [Turbo Frames](https://turbo.hotwired.dev/handbook/frames) are all about. Links and Form submissions that are trapped inside a Turbo Frame tag (or that point to one using a `data-turbo-frame` attribute) will instruct Turbo Drive to **NOT** replace the entire body of the document, but instead to look for a matching Turbo Frame in the response using its DOM ID and replace that specific portion of the page.

Here's how you can use Turbo Frames:

```html
<turbo-frame id="my_frame">
    <h1>Hello, World!</h1>
    <a href="/somewhere">
        I'm a trigger. My response must have a matching Turbo Frame tag (same ID)
    </a>
</turbo-frame>
```

Turbo Frames also allows you to lazy-load the frame's content. You may do so by adding a `src` attribute to the Turbo Frame tag. The content of a lazy-loading Turbo Frame tag can be used to indicate "loading states":

```blade
<turbo-frame id="my_frame" :src="route('my.page')">
    <p>Loading...</p>
</turbo-frame>
```

Turbo will automatically dispatch a fetch request (AJAX) as soon as a lazy-loading Turbo Frame enters the DOM and replace its content with a matching Turbo Frame in the response.

As mentioned earlier, you may also trigger a Turbo Frame with forms and links that are _outside_ of such frames by pointing to them with a `data-turbo-frame` attribute:

```blade
<div>
    <a href="/somewhere" data-turbo-frame="my_frame">I'm a link</a>

    <turbo-frame id="my_frame">
        <!-- Content -->
    </turbo-frame>
</div>
```

You could also "hide" this link and trigger a "click" event with JavaScript programmatically to trigger the Turbo Frame to reload, for example.

So far, all vanilla Hotwire and Turbo.

[Continue to Conventions...](/docs/{{version}}/conventions)
