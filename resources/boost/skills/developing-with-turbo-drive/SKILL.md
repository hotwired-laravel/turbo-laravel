---
name: developing-with-turbo-drive
description: >-
  Develops with Turbo Drive for SPA-like navigation. Activates when configuring page morphing with x-turbo::refreshes-with;
  working with data-turbo, data-turbo-track, data-turbo-permanent, or data-turbo-preload attributes; managing cache control
  with x-turbo::exempts-page-from-cache, x-turbo::exempts-page-from-preview, or x-turbo::page-requires-reload; enabling
  view transitions with x-turbo::page-view-transition; handling form redirects with TurboMiddleware; customizing the progress
  bar; or when the user mentions Turbo Drive, navigation, page morphing, prefetching, or asset tracking.
---

# Turbo Drive

Turbo Drive accelerates navigation by intercepting link clicks and form submissions, making them as `fetch` requests. It replaces the `<body>` and merges the `<head>` without tearing down the JavaScript environment, giving SPA-like speed with server-rendered HTML.

## How It Works

- **Link clicks**: Turbo intercepts clicks on `<a href>` links to the same domain, updates the URL via the History API, fetches the new page, and renders the HTML response.
- **Form submissions**: Form submissions become `fetch` requests. Turbo follows the redirect and renders the response.
- **Rendering**: Replaces `<body>`, merges `<head>`. The `window`, `document`, and `<html>` element persist across navigations.

## Navigation Types

- **Application Visits** (advance/replace): Initiated by clicking a link or calling `Turbo.visit()`. Issues a fetch, renders HTML. Advance pushes to history; replace modifies the current entry.
- **Restoration Visits**: Triggered by the browser Back/Forward buttons. Turbo restores from cache if available, otherwise fetches fresh content.

## Page Refreshes with Morphing

Instead of replacing the entire `<body>`, Turbo can morph the existing page to preserve DOM state (form inputs, scroll positions, focus):

@verbatim

<code-snippet name="Morphing config" lang="blade">
{{-- Add to your layout's <head> --}}
<x-turbo::refreshes-with method="morph" scroll="preserve" />
</code-snippet>

@endverbatim

When `method="morph"` is set, Turbo uses DOM morphing for same-page refreshes. When `scroll="preserve"` is set, scroll position is maintained.

## Disabling Turbo Drive

Disable Turbo on a link, form, or entire section by adding `data-turbo="false"`:

```html
<a href="/external" data-turbo="false">Regular navigation</a>

<div data-turbo="false">
    <!-- All links and forms inside are excluded from Turbo -->
    <a href="/also-regular">This too</a>
    <!-- Re-enable for specific elements -->
    <a href="/turbo-again" data-turbo="true">Back to Turbo</a>
</div>
```

## Form Submission Flow

1. Turbo intercepts the form submission and sends it as a `fetch` request.
2. The server processes the form and returns a redirect (status 303 for non-GET submissions).
3. Turbo follows the redirect and renders the response.

IMPORTANT: Turbo requires redirect responses (303 See Other) for non-GET form submissions. Laravel's `redirect()` helper handles this correctly when the `TurboMiddleware` is active — it automatically converts 302 redirects to 303 for Turbo requests.

For validation errors, return a 422 status with the form HTML so Turbo can render it in place. To make it easier, Turbo Laravel will catch validation exceptions and redirect to the form based on the resources automatically (`*.update` to `*.edit` or `*.store` to `*.create` or `*.destroy` to `*.delete`) when the form route exists.

## Asset Tracking

Mark assets with `data-turbo-track="reload"` so Turbo reloads the page when they change (e.g., after a deploy):

```html
<link rel="stylesheet" href="/app.css?v=123" data-turbo-track="reload">
<script src="/app.js?v=123" data-turbo-track="reload"></script>
```

When Turbo detects tracked assets have changed, it performs a full page reload instead of a Turbo visit.

## Progress Bar

Turbo shows a progress bar at the top of the page for navigations taking longer than 500ms. Customize it with CSS:

```css
.turbo-progress-bar {
    height: 3px;
    background-color: #3b82f6;
}
```

## Prefetching

Turbo prefetches links on `mouseenter` by default. Control this behavior:

```html
<!-- Disable prefetching for a specific link -->
<a href="/heavy-page" data-turbo-preload="false">Heavy page</a>

<!-- Eagerly preload a link (fetches on page load) -->
<a href="/likely-next" data-turbo-preload>Likely next page</a>
```

## Permanent Elements

Elements with `data-turbo-permanent` persist across navigations. Useful for media players, maps, or other stateful elements:

```html
<div id="audio-player" data-turbo-permanent>
    <audio src="/music.mp3"></audio>
</div>
```

The element must have a matching `id` in both the current and new page.

## Cache Control

@verbatim

Control how Turbo caches pages using Blade components in your layout's `<head>`:

<code-snippet name="Cache control" lang="blade">
{{-- Skip the cache entirely for this page --}}
<x-turbo::exempts-page-from-cache />

{{-- Cache the page but don't show preview during navigation --}}
<x-turbo::exempts-page-from-preview />

{{-- Force a full page reload when visiting this page --}}
<x-turbo::page-requires-reload />
</code-snippet>

@endverbatim

## View Transitions API

Enable the View Transitions API for smooth animated transitions between pages:

@verbatim

<code-snippet name="View transitions" lang="blade">
{{-- Add to your layout's <head> --}}
<x-turbo::page-view-transition />
</code-snippet>

@endverbatim

This renders `<meta name="view-transition" content="same-origin" />`, enabling CSS-driven page transitions.
