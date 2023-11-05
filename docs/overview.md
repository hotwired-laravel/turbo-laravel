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

So far, all vanilla Hotwire and Turbo.

[Continue to Conventions...](/docs/{{version}}/conventions)
