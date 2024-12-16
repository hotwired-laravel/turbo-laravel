---
layout: _layouts.v1-docs
title: Turbo Native
description: Turbo Native Helpers
order: 11
---

# Turbo Native

Hotwire also has a [mobile side](https://turbo.hotwired.dev/handbook/native), and the package provides some goodies on this front too.

Turbo Visits made by a Turbo Native client will send a custom `User-Agent` header. So we added another Blade helper you may use to toggle fragments or assets (such as mobile specific stylesheets) on and off depending on whether your page is being rendered for a Native app or a Web app:

```blade
@turbonative
    <h1>Hello, Turbo Native Users!</h1>
@endturbonative
```

Alternatively, you can check if it's not a Turbo Native visit using the `@unlessturbonative` Blade helpers:

```blade
@unlessturbonative
    <h1>Hello, Non-Turbo Native Users!</h1>
@endunlessturbonative
```

You may also check if the request was made from a Turbo Native visit using the request macro:

```php
if (request()->wasFromTurboNative()) {
    // ...
}
```

Or the Turbo Facade directly, like so:

```php
use Tonysm\TurboLaravel\Facades\Turbo;

if (Turbo::isTurboNativeVisit()) {
    // ...
}
```

## Interacting With Turbo Native Navigation

Turbo is built to work with native navigation principles and present those alongside what's required for the web. When you have Turbo Native clients running (see the Turbo iOS and Turbo Android projects for details), you can respond to native requests with three dedicated responses: `recede`, `resume`, `refresh`.

You may want to use the provided `InteractsWithTurboNativeNavigation` trait on your controllers like so:

```php
use Tonysm\TurboLaravel\Http\Controllers\Concerns\InteractsWithTurboNativeNavigation;

class TraysController extends Controller
{
    use InteractsWithTurboNativeNavigation;

    public function store()
    {
        $tray = /** Create the Tray */;

        return $this->recedeOrRedirectTo(route('trays.show', $tray));
    }
}
```

In this example, when the request to create trays comes from a Turbo Native request, we're going to redirect to the `turbo_recede_historical_location` URL route instead of the `trays.show` route. However, if the request was made from your web app, we're going to redirect the client to the `trays.show` route.

There are a couple of redirect helpers available:

```php
$this->recedeOrRedirectTo(string $url);
$this->resumeOrRedirectTo(string $url);
$this->refreshOrRedirectTo(string $url);
$this->recedeOrRedirectBack(string $fallbackUrl, array $options = []);
$this->resumeOrRedirectBack(string $fallbackUrl, array $options = []);
$this->refreshOrRedirectBack(string $fallbackUrl, array $options = []);
```

The Turbo Native client should intercept navigations to these special routes and handle them separately. For instance, you may want to close a native modal that was showing a form after its submission and _recede_ to the previous screen dismissing the modal, and not by following the redirect as the web does.

At the time of this writing, there aren't much information on how the mobile clients should interact with these routes. However, I wanted to be able to experiment with them, so I brought them to the package for parity (see this [comment here](https://github.com/hotwired/turbo-rails/issues/78#issuecomment-815897904)).

If you don't want these routes enabled, feel free to disable them by commenting out the feature on your `config/turbo-laravel.php` file (make sure the Turbo Laravel configs are published):

```php
return [
    'features' => [
        // Features::turboNativeRoutes(),
    ],
];
```

[Continue to Testing...](/docs/{{version}}/testing)
