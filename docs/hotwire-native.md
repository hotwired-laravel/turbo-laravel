---
layout: _layouts.docs
title: Hotwire Native
description: Hotwire Native Helpers
order: 11
---

# Hotwire Native

Hotwire also has a [mobile side](https://native.hotwired.dev/) and Turbo Laravel provides some helpers to help integrating with that.

Turbo visits made by a Hotwire Native client should send a custom `User-Agent` header. Using that header, we can detect in the backend that a request is coming from a Hotwire Native client instead of a regular web browser.

This is useful if you want to customize the behavior a little bit different based on that information. For instance,
you may want to include some elements for mobile users, like a mobile-only CSS file include, for instance. To do that, you may use the `@hotwirenative` Blade directive in your Blade views:

```blade
@hotwirenative
    <link rel="stylesheet" href="mobile.css">
@endhotwirenative
```

Alternatively, you may want to include some elements only if the client requesting it is _NOT_ a Hotwire Native client using the `@unlesshotwirenative` Blade helpers:

```blade
@unlesshotwirenative
    <h1>Hello, Non-Hotwire Native Users!</h1>
@endunlesshotwirenative
```

You may also check if the request was made from a Hotwire Native visit using the request macro:

```php
if (request()->wasFromHotwireNative()) {
    // ...
}
```

Or the Turbo Facade directly, like so:

```php
use HotwiredLaravel\TurboLaravel\Facades\Turbo;

if (Turbo::isHotwireNativeVisit()) {
    // ...
}
```

## Interacting With Hotwire Native Navigation

Hotwire Native will hook into Turbo's visits so it displays them on mobile mimicking the mobile way of stacking screens instead of just replace elements on the same screen. This helps the native feel of our hybrid app.

However, sometimes we may need to customize the behavior of form request handler to avoid a weird screen jumping effect happening on the mobile client. Instead of regular redirects, we can send some signals by redirecting to specific routes that are detected by the Hotwire Native client.

For instance, if a form submission request came from a Hotwire Native client, the form was probably rendered on a native modal, which is not part of the screen stack, so we can just tell Turbo to `refresh` the current screen it has on stack instead. There are 3 signals we can send to the Hotwire Native client:

| Signal | Route| Description|
|---|---|---|
| `recede` | `/recede_historical_location` | Go back to previous screen |
| `resume` | `/resume_historical_location` | Stay on the current screen as is |
| `refresh`| `/refresh_historical_location` | Stay on the current screen but refresh |

Sending these signals is a matter of detecting if the request came from a Hotwire Native client and, if so, redirect the user to these signal URLs instead. The Hotwire Native client should detect the redirect was from one of these special routes and trigger the desired behavior.

You may use the `InteractsWithHotwireNativeNavigation` trait on your controllers to achieve this behavior and fallback to a regular redirect if the request wasn't from a Hotwire Native client:

```php
use HotwiredLaravel\TurboLaravel\Http\Controllers\Concerns\InteractsWithHotwireNativeNavigation;

class TraysController extends Controller
{
    use InteractsWithHotwireNativeNavigation;

    public function store()
    {
        // Tray creation...

        return $this->recedeOrRedirectTo(route('trays.show', $tray));
    }
}
```

In this example, when the request to create trays comes from a Hotwire Native client, we're going to redirect to the `/recede_historical_location` URL instead of the `trays.show` route. However, if the request was made from your web app, we're going to redirect the client to the `trays.show` route.

There are a couple of redirect helpers available:

```php
$this->recedeOrRedirectTo(string $url);
$this->resumeOrRedirectTo(string $url);
$this->refreshOrRedirectTo(string $url);
$this->recedeOrRedirectBack(string $fallbackUrl, array $options = []);
$this->resumeOrRedirectBack(string $fallbackUrl, array $options = []);
$this->refreshOrRedirectBack(string $fallbackUrl, array $options = []);
```

It's common to flash messages using the `->with()` method of the Redirect response in Laravel. However, since a Hotwire Native request will never actually redirect somewhere where the flash message will be rendered, the behavior of the `->with()` method was slightly modified too.

If you're setting flash messages like this after a form submission:

```php
use HotwiredLaravel\TurboLaravel\Http\Controllers\Concerns\InteractsWithHotwireNativeNavigation;

class TraysController extends Controller
{
    use InteractsWithHotwireNativeNavigation;

    public function store()
    {
        // Tray creation...

        return $this->recedeOrRedirectTo(route('trays.show', $tray))
            ->with('status', __('Tray created.'));
    }
}
```

If a request was sent from a Hotwire Native client, the flashed messages will be added to the query string instead of flashed into the session like they'd normally be. In this example, it would redirect like this:

```
/recede_historical_location?status=Tray%20created.
```

In the Hotwire Native client, you should be able to intercept these redirects, retrieve the flash messages from the query string and create native toasts, if you'd like to.

If the request wasn't from a Hotwire Native client, the message would be flashed into the session as normal, and the client would receive a redirect to the `trays.show` route in this case.

If you don't want these routes enabled, feel free to disable them by commenting out the feature on your `config/turbo-laravel.php` file (make sure the Turbo Laravel configs are published):

```php
return [
    'features' => [
        // Features::hotwireNativeRoutes(),
    ],
];
```

[Continue to Testing...](/docs/{{version}}/testing)
