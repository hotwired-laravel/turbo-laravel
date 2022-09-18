# Notes on Turbo Drive and Turbo Frames

To keep it short, Turbo Drive will turn links and form submissions into AJAX requests and will replace the page with the response. That's useful when you want to navigate to another page entirely.

If you want some elements to persist across these navigations, you may annotate these elements with a DOM ID and add the `data-turbo-permanent` custom attribute to them. As long as the response also contains an element with the same DOM ID and `data-turbo-permanent` attribute, Turbo will not touch it.

Sometimes you don't want the entire page to change, but instead just a portion of the page. That's what [Turbo Frames](https://turbo.hotwired.dev/handbook/frames) are all about. Links and Form submissions that are trapped inside a Turbo Frame tag (or that point to one!) will instruct Turbo Drive to **NOT** replace the entire body of the document, but instead to look for a matching Turbo Frame in the response using its DOM ID and replace that specific portion of the page.

Here's how you can use Turbo Frames:

```html
<turbo-frame id="my_frame">
    <h1>Hello, World!</h1>
    <a href="/somewhere">
        I'm a trigger. My response must have a matching Turbo Frame tag (same ID)
    </a>
</turbo-frame>
```

Turbo Frames also allows you to lazy-load the frame's content. You may do so by adding a `src` attribute to the Turbo Frame tag. The content of a lazy-loading Turbo Frame tag can be used to indicate "loading states", such as:

```blade
<turbo-frame id="my_frame" :src="route('my.page')">
    <p>Loading...</p>
</turbo-frame>
```

Turbo will automatically dispatch a GET AJAX request as soon as a lazy-loading Turbo Frame enters the DOM and replace its content with a matching Turbo Frame in the response.

You may also trigger a Turbo Frame with forms and links that are _outside_ of such frames by pointing to them with a `data-turbo-frame` attribute:

```blade
<div>
    <a href="/somewhere" data-turbo-frame="my_frame">I'm a link</a>

    <turbo-frame id="my_frame"></turbo-frame>
</div>
```

You could also "hide" this link and trigger a "click" event with JavaScript programmatically to trigger the Turbo Frame to reload, for example.

So far, all vanilla Hotwire and Turbo.

[Continue to Blade Helpers...](/docs/{{version}}/blade-helpers)
