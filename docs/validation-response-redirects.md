---
layout: _layouts.v1-docs
title: Validation Response
description: Validation Response
order: 9
---

# Validation Response

By default, Laravel will redirect failed validation exceptions "back" to the page the triggered the request. This is a bit problematic when it comes to Turbo Frames, since a form might be included in a page that don't render the form initially, and after a failed validation exception from a form submission we would want to re-render the form with the invalid messages.

In other words, a Turbo Frame inherits the context of the page where it was inserted in, and a form might not be part of that page itself. We can't redirect "back" to display the form again with the error messages, because the form might not be re-rendered there by default. Instead, we have two options:

1. Render a Blade view with the form as a non-200 HTTP Status Code, then Turbo will look for a matching Turbo Frame inside the response and replace only that portion or page, but it won't update the URL as it would for other Turbo Visits; or
2. Redirect the request to a page that renders the form directly instead of "back". There you can render the validation messages and all that. Turbo will follow the redirect (303 Status Code) and fetch the Turbo Frame with the form and invalid messages and update the existing one.

When using the `TurboMiddleware` that ships with this package, we'll override Laravel's default error handling for validation exceptions. Instead of redirecting "back", we'll guess the form route based on the route resource conventions (if you're using that) and make an internal GET request to that route and return its contents with a 422 status code. So, if you're using the route resource conventions, validation errors will not respond with redirects, but with 422 status codes instead.

To guess where the form is located at we rely on the route resource convention. For any route name ending in `.store`, it will guess that the form can be located at the `.create` route for the same resource with all the route params from the previous request. In the same way, for any `.update` routes, it will guess the form is located at the `.edit` route of the same resource.

Examples:

- `posts.comments.store` will guess the form is at the `posts.comments.create` route with the `{post}` route param.
- `comments.store` will guess the form is at the `comments.create` route with no route params.
- `comments.update` will guess the form is at the `comments.edit` with the `{comment}` param.

If a guessed route name doesn't exist (which will always happen if you don't use the route resource convention), the middleware will not change the default handling of validation errors.

When you're not using the [resource route naming convention](/docs/{{version}}/conventions), you can override redirect behavior by catching the `ValidationException` yourself and re-throwing it overriding the redirect with the `redirectTo` method. If the exception has that, the middleware will respect it and make a GET request to that location instead of trying to guess it.

Here's how you may set the `redirectTo` property:

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

You may want to have exceptions to the route guessing behavior, which you can use the `redirect_guessing_exceptions` config in the `config/turbo-laravel.php` config file:

```php
return [
    // ...
    'redirect_guessing_exceptions' => [
        '/some-page',
    ],
];
```

The internal redirect will still happen, but the resource route convention will not be used.

## Turbo HTTP Middleware

The package ships with a middleware which applies some conventions on your redirects, specially around how failed validations are handled automatically by Laravel. Read more about this in the [Conventions](#conventions) section of the documentation.

**The middleware is automatically prepended to your web route group middleware stack**. You may want to add the middleware to other groups, when doing so, make sure it's at the top of the middleware stack:

```php
\Tonysm\TurboLaravel\Http\Middleware\TurboMiddleware::class,
```

Like so:

```php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middlewareGroups = [
        'web' => [
            \Tonysm\TurboLaravel\Http\Middleware\TurboMiddleware::class,
            // other middlewares...
        ],
    ];
}
```

[Continue to CSRF Protection...](/docs/{{version}}/csrf)
