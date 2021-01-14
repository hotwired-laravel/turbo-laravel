# Turbo Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tonysm/turbo-laravel.svg?style=flat-square)](https://packagist.org/packages/tonysm/turbo-laravel)
[![GitHub Tests Action Status](https://github.com/tonysm/turbo-laravel/workflows/Tests/badge.svg)](https://github.com/tonysm/turbo-laravel/workflows/Tests/badge.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/tonysm/turbo-laravel.svg?style=flat-square)](https://packagist.org/packages/tonysm/turbo-laravel)

This package gives you a set of conventions to make the most out of [Hotwire](https://hotwire.dev/) in Laravel (inspired by [turbo-rails](https://github.com/hotwired/turbo-rails) gem). There is a [companion application](https://github.com/tonysm/turbo-demo-app) that shows how to use this package and the conventions in your application.

<a name="documentation"></a>
## Documentation Index

* [Installation](#installation)
* [Conventions](#conventions)
* [Getting Started](#getting-started)

<a name="installation"></a>
## Installation

You can install the package via composer:

```bash
composer require tonysm/turbo-laravel
```

You can publish the asset files with:

```bash
php artisan turbo:install
```

You can also use Turbo Laravel with Jetstream if you use the Livewire stack. If you want to do so, publish the assets with a `--jet` flag:

```bash
php artisan turbo:install --jet
```

This will publish the JavaScript files to your application. You must install and compile the assets before continuing. The `--jet` flag will also install alpine and add the [`livewire/turbolinks`](https://github.com/livewire/turbolinks) bridge to your `app.blade.php` layout for you.

<a name="conventions"></a>
## Conventions

Before we get started, it's important to note that the package does not enforce any convention over your application. All conventions used are aimed at reducing the amount of boilerplate you would have to write yourself. However, if you don't want to follow them, you don't have to. Most pieces allow you to override the default behavior with either implementing some Hotwire specific methods on your models or, you know, simply not using the goodies the package provide.

However, I do think that conventions over configuration is an important goal, so here's a list with the conventions you can follow to make your life easier using the package:

* You might want to have your controllers using the resource routes for most things, or follow the resource routes naming convention (`posts.index`, `posts.store`, ...)
* You might want to have your views decoupled using partials (small portions of HTML for specific fragments, such as `comments/_comment.blade.php` for displaying a specific comment, or `comments/_form.blade.php` to display the comments form)
* Your model partial (`comments/_comment.blade.php` for a `Comment` model, for example) should only rely on having a `$comment` variable on it (when processing Turbo Streams partials in background, the package will provide a variable using the model's basename in _camelCase_ to the partial)
* Your Broadcasting channel authorization should use a dotted version of the model's FQCN ending with a `.{id}` at the end (such as `App.Models.Comment.{comment}` for a `Comment` model living in `App\\Models`)

In the [Getting Started section](#getting-started) you can see how to override most of the default behaviors, if you want to.

Again, you don't have to follow of these conventions. Also, feel free to suggest any change you think makes sense.

<a name="getting-started"></a>
## Getting Started

Once your assets are compiled, you will have some new custom HTML tags that you can use to annotate your Turbo Frames and Turbo Streams. This is vanilla Hotwire stuff. There is not a lot in the tech itself. Once you understand how the few underlying pieces work together, the challenge will be in decomposing your UI to work as you want them to.

This package aims to make the integration seamlessly. It offers a couple macros, some traits, and some conventions borrowed from Rails itself to find a partial for a respective model, but it also allows you to override these conventions per model or not use the convenient bits at all, if you want to.

### Turbo Drive

Turbo Drive is the spiritual successor of Turbolinks. It will hijack your links and forms and turn them into AJAX requests, updating your browser history, and caching visited pages, so it can serve from it again from Cache on a second visit while loading an updated version of the page. The main difference here is that Turbolinks didn't play well with regular forms. Turbo Drive does. You can use it just for its SPA behavior.

It replaces the page with the response from new visits without a browser fresh. That's useful when you want to navigate to other completely different pages, but if you want to persist certain pieces of HTML (with its state!) across visits, you can annotate them with a `data-turbo-permanent` attribute and some ID. If a matching element exists on the next Turbo visit, Turbo Drive won't touch that specific element. Otherwise, the whole page will be changed. This is used in Basecamp's navigation bar, for instance.

That's essentially what Turbo Drive does.

### Turbo Frames

Sometimes you don't want to replace the entire page, but instead wants to have more granular control of a fragment of your page. You can do that with Turbo Frames. This is what a Turbo Frame looks like:

```blade
<turbo-frame id="my_frame">
    <h1>Hello, World!</h1>
    <a href="/somewhere">I'm a trigger. My response must have a matching Turbo Frame tag (same ID)</a>
</turbo-frame>
```

Turbo Frames can also lazy-load content:

```blade
<turbo-frame id="my_frame" src="{{ route('my.page') }}">
    <p>Loading...</p>
</turbo-frame>
```

This will essentially replace the contents of the frame with a matching frame in the page specified as the `src=` attribute. The request will be made as soon as the Frame renders. You can also annotate it with `loading="lazy"` and the request will only be sent when the frame appears in the viewport (when visible). You could also trigger a frame visit with a link outside the frame itself:

```blade
<div>
    <a href="/somewhere" data-turbo-frame="my_frame">I'm a link</a>

    <turbo-frame id="my_frame"></turbo-frame>
</div>
```

When that link is clicked (either by the user or programmatically from JavaScript!), a visit will be made to its `href`
URL and a matching frame is expected there and will be injected into the Turbo Frame below it.

So far, all vanilla Hotwire stuff.

Since Turbo Frames rely a lot on DOM IDs, there is a helper for generating DOM IDs for your models:

```blade
<turbo-frame id="@domid($comment)">
    <!-- More stuff -->
</turbo-frame>
```

This will generate a `comment_123` DOM ID. You can also give it a context, such as:

```blade
<turbo-frame id="@domid($post, 'comments_count')">(99)</turbo-frame>
```

Which will generate a `comments_count_post_123` ID. This API was borrowed from Rails.

When you have a link or form inside a Turbo Frame, Turbo Drive will make a visit and look for matching Turbo Frame (using the ID) on the response, and only replace that portion of the current page. Everything else gets to keep their current state (like other form fields, for instance).

That's essentially what you can do with Turbo Frames. Turbo Drive and Turbo Frames can get you 80% there.

### Turbo Streams

Sometimes you do need to update multiple different parts of your application at the same time (not just a single Frame). For instance, maybe after a form submission to create a comment in a post, you might want to append the comment to the comment's list and also update the comment's count. You can do that with Turbo Streams. A Turbo Stream response consists of one or more `<turbo-stream>` tags and the correct header of `Content-Type: text/vnd.turbo-stream.html`. If these are returned from a Turbo Visit from a controller, then Turbo will do the rest to apply your changes.

A Turbo Visit is annotated by Turbo itself with an `Accept` header that indicates that you can return a Turbo Stream response. You can check that using the `wantsTurboStream` macro in the Request class, passing it any given Eloquent Model:

```php
class PostCommentsController
{
  public function store(Post $post)
  { 
    $comment = $post->comments()->create([]);
    
    if (request()->wantsTurboStream()) {
      // Return the Turbo Stream response.
      return response()->turboStream($comment);
    }
    
    return redirect()->route('...');
  }
}
```

The `turbosStream` macro in the ResponseFactory will generate a Turbo Stream response for the changes made to your model (either you created, updated, or deleted it). We try to follow Rails' conventions for finding partials for your models. For the example above, by default, we'll look for a partial located at `comments/_comment.blade.php`. This follows the convention of *plural resource name* for the folders and *singular resource name* for the partial itself, prefixed with an underscore.

Your partial will receive a variable named after your class basename in _camelCase_. So, in this case, it will receive a `$comment` variable that you can use.

If you want to control the partial name by implementing the `hotwirePartialName` in your Comment model. You can have more control over the data passed to the partial by implementing the `hotwirePartialData` method, like so:

```php
class Comment extends Model
{
  public function hotwirePartialName()
  {
    return 'my.non.conventional.partial.name';    
  }
  
  public function hotwirePartialData()
  {
    return [
      'lorem' => false,
      'ipsum' => true,
      'comment' => $this,
    ];
  }
}
```

The macro will look for a partial for your model, render it inside a Turbo Stream tag.

If the model was recently created (created during the request itself), the `target` of the Turbo Stream will be the plural version of the model's basename, and the action will be "append" or whatever action you pass it as the second parameter in the `turboStream` macro. If the model was updated, the `target` of the Turbo Stream tag will be the DOM ID of the model itself (using the `@domid()` helper's conventions), and the default `action` will be `replace`. For deleted models, the `target` will also be the DOM ID, but the `action` will be `remove`. No template will be used for deleted Turbo Stream messages.

In this example, a model named `App\\Models\\Comment` will look for its partial inside `resources/views/comments/_comment.blade.php`. To that partial, a reference of the model itself will be passed down having the model's basename as name for the variable (in _camelCase_). So, for a `App\\Models\\Comment` model, you will have a `$comment` variable available inside the partial.

Both the partial name and the data can be overwritten, as you saw earlier. The resource name used as the Turbo Stream `target` can also be overwritten, as well as the DOM ID for the model when you're generating the Turbo Stream response for an already existing, but updated model, like so:

```php
class Comment extends Model
{
  public function hotwireTargetResourcesName()
  {
    return 'admin_comments';
  }
  
  public function hotwireTargetDomId()
  {
    return "admin_comment_{$this->id}";
  }
}
```

One example for a recently created comment model would be:

```blade
<turbo-stream target="comments" action="append">
  <template>
    @include('comments._comment', ['comment' => $comment])
  </template>
</turbo-stream>
```

An example for a model that was updated:

```blade
<turbo-stream target="comment_123" action="update">
  <template>
    @include('comments._comment', ['comment' => $comment])
  </template>
</turbo-stream>
```

An example for a model that was deleted:

```blade
<turbo-stream target="comment_123" action="remove"></turbo-stream>
```

If you want to have more control over your streamed responses, for instance, you can use the `response()->turboStreamView()` macro instead. Here's an example:

```php
return response()->turboStreamView(view('comments.turbo_created_stream', [
  'comment' => $comment,
]));
```

That view is a regular blade view that you can add your `<turbo-stream>` tags yourself. One example of such a view that appends the comment to the page and updates the comments count in the page:

```blade
<turbo-stream target="@domid($comment->post, 'comments_count')" action="update">
    <template>({{ $comment->post->comments()->count() }})</template>
</turbo-stream>

<turbo-stream target="comments" action="append">
  <template>
    @include('comments._comment', ['comment' => $comment'])
  </template>
</turbo-stream>
```

The `turboStreamView` Response macro will take your view, render it and apply the correct `Content-Type` for you.

However, if you want more control over your Turbo Stream views, you can add them to a `turbo` folder inside your model's resource folder. For instance, if you want to control the Turbo Stream generated for a comment that was created, you might add a: `resources/comments/turbo/created_stream.blade.php` view. This portion should only contain `<turbo-stream>` tags and will be given a reference to the model itself following the same convention as the partial data (_camelCase_ version of the model's basename).

You can also have `updated_stream.blade.php` and `deleted_stream.blade.php` (this follows Eloquent's model events patterns, but only these 3 events are supported for now).

### Turbo Streams and Laravel Echo

So far, we have been using Turbo Streams over HTTP to update multiple parts of your page after a Turbo Visit. However, you may want to also broadcast Turbo Stream changes for your model's over WebSockets to other users on the same pages. Although nice, **you don't have to use WebSockets in your app if you don't have the need for it. You can rely on only returning Turbo Stream responses from your controller.** You can also mix HTTP Turbo Stream responses with Turbo Stream Broadcasts sent over WebSockets to other users.

If you want to augment your app with WebSockets continue reading.

First, you need to make sure your Laravel Echo set up is properly done, should be something like this:

```dotenv
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=us2
PUSHER_APP_HOST=websockets.test

MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
MIX_PUSHER_HOST="localhost"
MIX_PUSHER_PORT="${LARAVEL_WEBSOCKETS_PORT}"
MIX_PUSHER_USE_SSL=false
```

These settings are assuming you're using the [Laravel WebSockets](https://github.com/beyondcode/laravel-websockets) package locally. Check out the [resources/js/echo.js](resources/js/echo.js) for the suggested dotenv credentials you need. You can also set up [Pusher](https://pusher.com/), if you want to.

With that out of the way, you can broadcast changes from your models using WebSockets for each "created", "updated", or "deleted" events, like so:

```php
use Tonysm\TurboLaravel\Events\TurboStreamModelCreated;
use Tonysm\TurboLaravel\Events\TurboStreamModelUpdated;
use Tonysm\TurboLaravel\Events\TurboStreamModelDeleted;

class Comment extends Model
{
    protected $dispatchesEvents = [
        'created' => TurboStreamModelCreated::class,
        'updated' => TurboStreamModelUpdated::class,
        'deleted' => TurboStreamModelDeleted::class,
    ];
}
```

This will automatically propagate changes of this model to its desired channels following the convention of using the model's FQCN using a dotted notation suffixed with the model ID. To follow our `App\\Models\\Comment` example, the changes would broadcast to the channel named: `App.Models.Comment.{id}` (the name of the wildcard is not enforced, you can use whatever you want, but we'll use the model's ID as its value). You can pick only the events you want to broadcast.

If you want to control the channel you're broadcasting to, maybe passing it to a related model, or send it out to a couple different related models, you can also do it like so:

```php
use Tonysm\TurboLaravel\Events\TurboStreamModelCreated;
use Tonysm\TurboLaravel\Events\TurboStreamModelUpdated;
use Tonysm\TurboLaravel\Events\TurboStreamModelDeleted;

class Comment extends Model
{
    public $broadcastsTo = [
        'post',
    ];

    protected $dispatchesEvents = [
        'created' => TurboStreamModelCreated::class,
        'updated' => TurboStreamModelUpdated::class,
        'deleted' => TurboStreamModelDeleted::class,
    ];
    
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
```

This will broadcast the comment's changes to `App.Models.Post.{id}` where `{id}` would be the Post ID (again, assuming your FQCN is `App\\Models\\Post`). You can also do that using a `broadcastsTo()` method:

```php
use Tonysm\TurboLaravel\Events\TurboStreamModelCreated;
use Tonysm\TurboLaravel\Events\TurboStreamModelUpdated;
use Tonysm\TurboLaravel\Events\TurboStreamModelDeleted;

class Comment extends Model
{
    protected $dispatchesEvents = [
        'created' => TurboStreamModelCreated::class,
        'updated' => TurboStreamModelUpdated::class,
        'deleted' => TurboStreamModelDeleted::class,
    ];
    
    public function broadcastsTo()
    {
        return $this->post;
    }
    
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
```

You can return a model, an array or a collection of models, or an array or a collection of _Channels_, giving you full control over where you want the broadcasting to be sent to.

The same partial conventions apply here too. If a broadcast was triggered by a comment that was recently created, it will look for a `resources/views/comments/turbo/created_stream.blade.php` view, if that doesn't exist, it will expect a partial at `resources/views/comments/_comment.blade.php` to be there. You can also override the partial name in the same way we already covered adding a `hotwirePartialName` method to your `Comment` model.

If you want to broadcast all changes of a model (created, updated, and deleted events), we provide a trait named `Tonysm\TurboLaravel\Models\Broadcasts` that you can use in your model. Something like:

```php
use Tonysm\TurboLaravel\Models\Broadcasts;

class Comment extends Model
{
  use Broadcasts;
}
```

This will apply the same conventions mentioned for the model events, and doing it this way will automatically dispatch the broadcasting in background, using queued jobs.

To listen for the events, we ship with a custom HTML tag `<turbo-echo-stream-source>` that you can add to any page you want to receive broadcasts. This tag will connect to the `channel` attribute you provide to it and will start receiving Turbo Streams messages over WebSockets and applying them to the page. When you leave the page, it will also leave the channel. Here's an example of how you can use it:

```blade
<turbo-echo-stream-source
    channel="App.Models.Comments.{{ $comment->id }}"
/>
```

This assumes you have your Laravel Echo properly configured. By default, it expects a private channel, so the tag must be used in a page for already authenticated users. You can control the type of the channel in the tag with a `type` attribute.

```blade
<turbo-echo-stream-source
    channel="App.Models.Comments.{{ $comment->id }}"
    type="presence"
/>
```

You might want to read [Laravel's Broadcasting](https://laravel.com/docs/8.x/broadcasting) documentation.

### Validation Responses

By default, Laravel's failed exception redirects the user back to the page that sent the request. This is a bit problematic when it comes to Turbo Frames, since a form might be included in tha Turbo Frame that inherits the context of the page where it was inserted, and the form isn't part that page itself (it was included via Turbo Frame afterwards). We can't redirect "back" to display the form again with the error messages, because "back" might not have the form or might not even have a matching Turbo Frame. Instead, we have two options:

1. Render a Blade view with the form as a non-200 HTTP Status code, which Turbo will look for a matching Turbo Frame inside the response and replace only that portion or page, but not changing pages with the Visit; or
2. Redirect the request to a page that contains the form directly instead of "back". There you can render the validation messages and all that. Turbo will follow the redirect (303 Status Code) and fetch the Turbo Frame with the new form and update the existing one.

The package ships with a middleware that you can apply to your web route group (in your `app/Http/Kernel.php` file). The middleware will catch any redirects triggered by failed validation exceptions and will apply some conventions to it.

For any route name ending in `.store`, it will redirect back to a `.create` route with all the route params from the previous route. In the same way, for any `.update` routes, it will redirect back to a `.edit` route of the same resource.

Examples:

- `posts.comments.store` will redirect to `posts.comments.create` with the `{post}` route param.
- `comments.store` will redirect to `comments.create` with no route params.
- `comments.update` will redirect to `comments.edit` with the `{comment}` param.

If a guessed route name doesn't exist, the middleware will not change the redirect response. If you want to have more control over the redirect URL, you can catch the `Illuminate\Validation\ValidationException` exception yourself and use the `redirectTo` method in it. If the exception has that attribute, the middleware will also not touch it. You can also return Blade view using a non-200 status code after catching that exception, if you want to. 

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

If you want to take the "mixed" approach I mentioned earlier, you can tell Turbo Laravel to only broadcast changes _to other_ users, and feed the current user's with Turbo Stream messages in the HTTP response they triggered. Do tell Turbo Laravel to broadast only to others, add the following like to your `AppServiceProvider` in the `boot` method:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Tonysm\TurboLaravel\TurboFacade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        TurboFacade::broadcastToOthers(true);
    }
}
```

That's Turbo Stream over WebSockets using Laravel Echo.

### Turbo Native

Turbo Visits made by the Turbo Native client will send a custom `User-Agent` header. So we added another Blade helper you can use to toggle fragments or assets (like mobile specific stylesheets) on and off depending on whether your page is being rendered for a Native app or a web app:

```blade
@turbonative
    <h1>Hello, Mobile Users!</h1>
@endturbonative
```

We also ship a Facade that you can use in your code controllers as you want:

```php
if (\Tonysm\TurboLaravel\TurboFacade::isTurboNativeVisit()) {
    // Do something for mobile specific requests.
}
```

### Closing Notes

Try the package out. Use your Browser's DevTools to inspect the responses. You will be able to spot every single Turbo Frame and Turbo Stream happening.

> "The proof of the pudding is in the eating."

Make something awesome!

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Drop me an email at [tonysm@hey.com](mailto:tonysm@hey.com?subject=Security%20Vulnerability) if you want to report
security vulnerabilities.

## Credits

- [Tony Messias](https://github.com/tonysm)
- [All Contributors](./CONTRIBUTORS.md)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
