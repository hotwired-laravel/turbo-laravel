---
layout: _layouts.docs
title: Validation Response
description: Validation Responses in Laravel and Hotwire
order: 9
---

# Validation Response

By default, Laravel redirects failed validation exceptions "back" to the page where the request came from. This isn't usually a problem, in fact it's the expected behavior, since that page usually is the one where the form which triggered the request renders.

However, this is a bit of a problem when it comes to Turbo Frames, since a form might get injected into a page that doesn't initially render it. The problem is that after a failed validation exception from that form, Laravel would redirect it "back" to the page where the form got injected and since the form is not rendered there initially, the user would see the form disappear.

In other words, we can't redirect "back" to display the form again with the error messages, because the form might not be re-rendered there originally. Instead, Turbo expects that we return a non-200 HTTP status code with the form and validation messages right way after a failed validation exception is thrown.

Turbo Laravel automatically prepends a `TurboMiddleware` on the web route group. The middleware will intercept the response when it detects that Laravel is responding after a `ValidationException`. Instead of letting it send the "redirect back" response, it will try to guess where the form for that request usually renders and send an internal request back to the app to render the form, then update the status code so it renders as a 422 instead of 200.

To guess where the form is located at we rely on the route resource naming convention. For any route name ending in `.store`, it will guess that the form can be located in a similar route ending with `.create` for the same resource. Similarly, for any route ending with `.update`, it will guess the form is located at a route ending with `.edit`. Addittionaly, for any route ending with `.destroy`, it will guess the form is located at a route ending with `.delete` (this is the only convention that is not there by default in Laravel's conventions.)

For this internal request, the middleware will pass along any resource the current route has as well as any query string that was passed.

Here are some examples:

- `posts.comments.store` will guess the form is at the `posts.comments.create` route with the `{post}` route param.
- `comments.store` will guess the form is at the `comments.create` route with no route params.
- `comments.update` will guess the form is at the `comments.edit` with the `{comment}` param.

If a guessed route name doesn't exist (which will always happen if you don't use the route resource convention), the middleware will not change the default handling of validation errors, so the regular "redirect back" behavior will act.

When you're not using the [resource route naming convention](/docs/{{version}}/conventions), you may override redirect behavior by catching the `ValidationException` and re-throwing it setting the correct location where the form renders using the `redirectTo` method. If the exception has that, the middleware will respect it and make a GET request to that location instead of trying to guess it:

```php
public function store()
{
  try {
     request()->validate(['name' => 'required']);
  } catch (\Illuminate\Validation\ValidationException $exception) {
    throw $exception->redirectTo(url('/somewhere'));
  }
}
```

If you want to register exceptions to this route guessing behavior, add the URIs to the `redirect_guessing_exceptions` key in the `config/turbo-laravel.php` config file:

```php
return [
    // ...
    'redirect_guessing_exceptions' => [
        '/some-page',
    ],
];
```

## The Turbo HTTP Middleware

Turbo Laravel ships with a middleware which applies some conventions on your redirects, like the one for how failed validations are handled automatically by Laravel as described before. Read more about this in the [Conventions](#conventions) section of the documentation.

**The middleware is automatically prepended to your web route group middleware stack**. You may want to add the middleware to other groups. When doing so, make sure it's at the top of the middleware stack:

```php
\HotwiredLaravel\TurboLaravel\Http\Middleware\TurboMiddleware::class,
```

Like so:

```php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middlewareGroups = [
        'web' => [
            \HotwiredLaravel\TurboLaravel\Http\Middleware\TurboMiddleware::class,
            // other middlewares...
        ],
    ];
}
```

[Continue to CSRF Protection...](/docs/{{version}}/csrf)
