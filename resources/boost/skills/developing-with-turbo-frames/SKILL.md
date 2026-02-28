---
name: developing-with-turbo-frames
description: >-
  Develops with Turbo Frames for scoped navigation and lazy loading. Activates when using the x-turbo::frame Blade
  component or turbo-frame HTML element; working with data-turbo-frame targeting, frame lazy loading via src attribute,
  or data-turbo-action for URL updates; detecting frame requests with wasFromTurboFrame(); using frame morphing with
  refresh="morph"; or when the user mentions Turbo Frame, turbo frame, scoped navigation, inline editing, lazy loading
  frames, or breaking out of a frame with _top.
---

# Turbo Frames

Turbo Frames decompose pages into independent segments that scope navigation. Clicking links or submitting forms inside a `<turbo-frame>` only updates that frame, keeping the rest of the page intact.

## The Frame Component

Use the `<x-turbo::frame>` Blade component to render a `<turbo-frame>` element:

@verbatim

<code-snippet name="Basic frame" lang="blade">
<x-turbo::frame :id="$post">
    <h3>{{ $post->title }}</h3>
    <a href="{{ route('posts.edit', $post) }}">Edit</a>
</x-turbo::frame>
</code-snippet>

@endverbatim

### The `:id` Prop

The `:id` prop accepts multiple formats and auto-generates DOM IDs:

@verbatim

<code-snippet name="ID prop formats" lang="blade">
{{-- String: uses as-is --}}
<x-turbo::frame id="new_post">...</x-turbo::frame>

{{-- Model instance: generates dom_id($post) e.g. "post_1" --}}
<x-turbo::frame :id="$post">...</x-turbo::frame>

{{-- Array [model, prefix]: generates dom_id($post, 'edit') e.g. "edit_post_1" --}}
<x-turbo::frame :id="[$post, 'edit']">...</x-turbo::frame>
</code-snippet>

@endverbatim

## Scoped Navigation

By default, links and forms inside a frame target that same frame. When the server responds, Turbo extracts the matching `<turbo-frame>` from the response and swaps its content:

@verbatim

<code-snippet name="Scoped navigation" lang="blade">
<x-turbo::frame :id="$post">
    {{-- Clicking this link fetches the edit page and extracts the matching frame --}}
    <a href="{{ route('posts.edit', $post) }}">Edit</a>

    {{-- Submitting this form updates only this frame with the response --}}
    <form action="{{ route('posts.update', $post) }}" method="POST">
        @csrf
        @method('PUT')
        <input name="title" value="{{ $post->title }}">
        <button type="submit">Save</button>
    </form>
</x-turbo::frame>
</code-snippet>

@endverbatim

## Targeting Other Frames

Override the default frame target using `data-turbo-frame`:

@verbatim

<code-snippet name="Targeting" lang="blade">
{{-- Target a specific frame by its DOM ID --}}
<a href="{{ route('posts.show', $post) }}" data-turbo-frame="post_detail">View</a>

{{-- Break out of the frame and navigate the entire page --}}
<a href="{{ route('posts.show', $post) }}" data-turbo-frame="_top">View full page</a>
</code-snippet>

@endverbatim

You can also set a default target on the frame itself:

@verbatim

<code-snippet name="Frame target attribute" lang="blade">
{{-- All navigation within this frame targets "_top" by default --}}
<x-turbo::frame :id="$post" target="_top">
    <a href="{{ route('posts.show', $post) }}">View</a>
</x-turbo::frame>
</code-snippet>

@endverbatim

## Lazy Loading

Frames can defer loading their content using the `:src` attribute. The frame fetches its content automatically:

@verbatim

<code-snippet name="Lazy loading" lang="blade">
{{-- Eager lazy load: fetches immediately when the page loads --}}
<x-turbo::frame :id="$post" :src="route('posts.comments.index', $post)">
    <p>Loading comments...</p>
</x-turbo::frame>

{{-- Viewport lazy load: fetches when the frame enters the viewport --}}
<x-turbo::frame :id="$post" :src="route('posts.comments.index', $post)" loading="lazy">
    <p>Loading comments...</p>
</x-turbo::frame>
</code-snippet>

@endverbatim

## Promoting Frame Navigations to Page Visits

Use `data-turbo-action` to make a frame navigation also update the browser URL and history:

```html
<a href="/posts/1" data-turbo-frame="post_detail" data-turbo-action="advance">View</a>
```

This updates the frame content AND pushes the URL to the browser history, allowing Back button navigation.

## Detecting Frame Requests on the Server

Use request macros to detect if a request came from a Turbo Frame:

@verbatim

<code-snippet name="Detecting frame requests" lang="php">
// Check if the request came from any Turbo Frame
if ($request->wasFromTurboFrame()) {
    // Return frame-specific response
}

// Check if it came from a specific frame
if ($request->wasFromTurboFrame(dom_id($post, 'create_comment'))) {
    // Return response for that specific frame
}
</code-snippet>

@endverbatim

## Morphing Within Frames

Add `refresh="morph"` to morph frame content instead of replacing it, preserving DOM state:

```html
<turbo-frame id="post_1" refresh="morph">
    <!-- Content will be morphed on refresh -->
</turbo-frame>
```

## Frame Rendering Customization

Customize how frame content is rendered using the `turbo:before-frame-render` event in JavaScript:

```javascript
document.addEventListener("turbo:before-frame-render", (event) => {
    // Access event.detail.newFrame to modify before rendering
});
```

## Benefits of Frames

1. **Efficient caching**: Each frame is cached independently, giving longer-lived caches.
2. **Parallelized execution**: Lazy-loaded frames are fetched concurrently, reducing total page load time.
3. **Mobile-ready**: Frames with independent URLs can be rendered as native sheets/screens in Hotwire Native apps.
