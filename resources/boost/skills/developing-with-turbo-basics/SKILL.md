---
name: developing-with-turbo-basics
description: >-
  Basics of developing with Turbo Laravel. Activates when starting a new Turbo Laravel project; using dom_id, dom_class,
  turbo_stream(), or turbo_stream_view() helpers; working with Blade components like x-turbo::frame, x-turbo::stream,
  x-turbo::stream-from, or x-turbo::refreshes-with; using @domid, @domclass, @channel, or @turbonative directives;
  checking wantsTurboStream(), wasFromTurboFrame(), or wasFromHotwireNative() request macros; or when the user mentions
  Hotwire, Turbo, HTML over the wire, or partial page updates.
---

# Turbo Laravel Basics

Turbo Laravel is a package that integrates Turbo, a set of technologies for building modern web applications, with the Laravel framework. Turbo enhances user experience by enabling partial page updates, real-time interactions, and seamless navigation without full page reloads and with minimal JavaScript.

## Philosophy: HTML Over the Wire

Hotwire (HTML Over the Wire) sends HTML instead of JSON from the server, letting the server handle rendering while keeping the browser's job simple. It combines four techniques:

1. **Turbo Drive** — Accelerates links and forms by replacing the `<body>` without full page loads.
2. **Turbo Frames** — Decomposes pages into independent segments that scope navigation and can lazy-load.
3. **Turbo Streams** — Delivers partial page changes over WebSocket, SSE, or in response to form submissions using eight actions (append, prepend, replace, update, remove, before, after, refresh).
4. **Stimulus** — A modest JavaScript framework for the HTML you already have, connecting behavior via `data-controller`, `data-action`, and `data-target` attributes.

Turbo Laravel provides the server-side tooling (Blade components, helpers, broadcasting, and testing utilities) to make these techniques work seamlessly with Laravel.

## Turbo Drive

Turbo Drive intercepts all clicks on `<a href>` links to the same domain and all form submissions, turning them into `fetch` requests. It replaces the `<body>` and merges the `<head>`, keeping the JavaScript `window` and `document` objects persistent across navigations. This gives SPA-like speed without client-side routing.

Same deal with forms — their submissions become fetch requests and Turbo Drive follows the redirect and renders the HTML response.

IMPORTANT: Activate the `developing-with-turbo-drive` skill when starting out working on a feature.

## Turbo Frames

Turbo Frames let you place independent segments inside `<turbo-frame>` elements that scope their navigation. All interaction within a frame (clicking links, submitting forms) happens within that frame, keeping the rest of the page intact. Frames can also lazy-load their content via a `src` attribute.

@verbatim

<code-snippet name="Turbo Frame" lang="blade">
<x-turbo::frame :id="$post">
    <h3>{{ $post->title }}</h3>
    <a href="{{ route('posts.edit', $post) }}">Edit</a>
</x-turbo::frame>
</code-snippet>

@endverbatim

Benefits: independent caching, parallelized execution, and mobile-ready segments.

IMPORTANT: Activate the `developing-with-turbo-frames` skill when working with Turbo Frames.

## Turbo Streams

Turbo Streams let you change any part of the page using a `<turbo-stream>` element with eight actions: `append`, `prepend`, `replace`, `update`, `remove`, `before`, `after`, and `refresh`. They work both as HTTP responses and over WebSocket broadcasts.

@verbatim

<code-snippet name="Turbo Stream" lang="blade">
<x-turbo::stream action="append" target="messages">
    <div id="message_1">My new message!</div>
</x-turbo::stream>
</code-snippet>

@endverbatim

IMPORTANT: Activate the `developing-with-turbo-streams` skill when working with Turbo Streams.

## Blade Components

@verbatim

- `<x-turbo::frame :id="$model">` — Renders a `<turbo-frame>` with auto DOM ID resolution. `:id` accepts a string, model instance, or `[$model, 'prefix']` array.
- `<x-turbo::stream action="append" :target="$model">` — Renders a `<turbo-stream>` element. Omits the `<template>` wrapper for brevity.
- `<x-turbo::stream-from :source="$model" />` — Listens for broadcasts on the model's channel (private by default, add `type="public"` for public).
- `<x-turbo::refreshes-with method="morph" scroll="preserve" />` — Configures page refresh behavior (morphing and scroll preservation).
- `<x-turbo::exempts-page-from-cache />` — Adds `<meta name="turbo-cache-control" content="no-cache">`.
- `<x-turbo::exempts-page-from-preview />` — Adds `<meta name="turbo-cache-control" content="no-preview">`.
- `<x-turbo::page-requires-reload />` — Adds `<meta name="turbo-visit-control" content="reload">`.
- `<x-turbo::page-view-transition />` — Adds `<meta name="view-transition" content="same-origin">`.

@endverbatim

## Helpers & Directives

@verbatim

- DOM IDs: `dom_id($model)` returns e.g. `"post_1"`, `dom_id($model, 'edit')` returns `"edit_post_1"`. Blade directive: `@domid($model, 'prefix')`.
- DOM classes: `dom_class($model)` returns e.g. `"post"`, `dom_class($model, 'admin')` returns `"admin_post"`. Blade directive: `@domclass($model, 'prefix')`.
- Channel: `@channel($model)` outputs the model's broadcast channel name.
- Conditional: `@hotwirenative` / `@endhotwirenative` shows content only for Hotwire Native visits. Also: `@unlesshotwirenative`, `@turbonative` / `@endturbonative`.

@endverbatim

## Request Macros

@verbatim

<code-snippet name="Request macros" lang="php">
// Check if the request accepts Turbo Stream responses
$request->wantsTurboStream(); // bool

// Check if the request came from a Turbo Frame (optionally a specific one)
$request->wasFromTurboFrame(); // bool
$request->wasFromTurboFrame(dom_id($post, 'create_comment')); // bool

// Check if the request came from a Hotwire Native client
$request->wasFromHotwireNative(); // bool
</code-snippet>

@endverbatim

## Response Helpers

@verbatim

<code-snippet name="Response helpers" lang="php">
// Return a Turbo Stream response for a model (auto-detects action from context)
return turbo_stream($model);
return turbo_stream($model, 'prepend');

// Return a fluent Turbo Stream builder
return turbo_stream()->append('posts', view('posts._post', ['post' => $post]));

// Return multiple streams
return turbo_stream([
    turbo_stream()->append('posts', view('posts._post', ['post' => $post])),
    turbo_stream()->update('post_count', view('posts._count', ['count' => $count])),
]);

// Return a Turbo Stream view (renders a Blade view with the Turbo Stream content type)
return turbo_stream_view('posts.turbo.created', ['post' => $post]);
</code-snippet>

@endverbatim

## Form Handling

Laravel resource conventions auto-redirect on validation errors:

- `*.store` → `*.create`, `*.update` → `*.edit`, `*.destroy` → `*.delete`

## Performance & UX

@verbatim

- Preserve elements during navigation: `<div id="flash" data-turbo-permanent>...</div>`
- Disable preloading on specific links: `<a data-turbo-preload="false">...</a>`
- Disable Turbo on specific elements: `<a data-turbo="false">...</a>`
- Re-enable Turbo within a disabled container: `<a data-turbo="true">...</a>`

@endverbatim
