---
layout: _layouts.docs
title: CSRF Protection
description: CSRF Protection
order: 10
---

# CSRF Protection

Laravel has built-in CSRF protection in place. It prevents our app from processing any non-GET requests that doesn't include a valid CSRF Token that was generated in our backend.

So, to allow a POST form to be processed, we usually need to add a `@csrf` Blade directive to our forms:

```blade
<form action="{{ route('chirps.store') }}" method="post">
    @csrf

    <!-- ... -->
</form>
```

Since Turbo.js intercepts form submissions and converts those to fetch requests (AJAX), we don't actually _need_ the `@csrf` token applied to each form. Turbo is smart enough to read our page's meta tags, look for one named `csrf-token` and use its contents to add the token to all form submissions it intercepts. Jetstream and Breeze both ship with such element in the layout files, but in case you're missing it in your views, it should look like this:

```blade
<meta name="csrf-token" content="{{ csrf_token() }}">
```

With that being said, you may still want to use the `@csrf` Blade directive if you want to support users with JavaScript disabled, since the forms will still work if they contain the CSRF token.

[Continue to Hotwire Native...](/docs/{{version}}/hotwire-native)
