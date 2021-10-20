<p align="center" style="margin-top: 2rem; margin-bottom: 2rem;"><img src="/art/turbo-laravel-logo.svg" alt="Logo Turbo Laravel" /></p>

<p align="center">
    <a href="https://github.com/tonysm/turbo-laravel/workflows/Tests/badge.svg">
        <img src="https://img.shields.io/github/workflow/status/tonysm/turbo-laravel/Tests?label=tests" />
    </a>
    <a href="https://packagist.org/packages/tonysm/turbo-laravel">
        <img src="https://img.shields.io/packagist/dt/tonysm/turbo-laravel" alt="Total Downloads">
    </a>
    <a href="https://packagist.org/packages/tonysm/turbo-laravel">
        <img src="https://img.shields.io/packagist/v/tonysm/turbo-laravel" alt="Latest Stable Version">
    </a>
    <a href="https://packagist.org/packages/tonysm/turbo-laravel">
        <img src="https://img.shields.io/packagist/l/tonysm/turbo-laravel" alt="License">
    </a>
</p>

<a name="introduction"></a>
## Introduction

This package gives you a set of conventions to make the most out of [Hotwire](https://hotwired.dev/) in Laravel (inspired by the [turbo-rails](https://github.com/hotwired/turbo-rails) gem).

There is a [companion application](https://github.com/tonysm/turbo-demo-app) that shows how to use the package and its conventions.

<a name="installation"></a>
## Installation

Turbo Laravel may be installed via composer:

```bash
composer require tonysm/turbo-laravel
```

After installing, you may execute the `turbo:install` Artisan command, which will add a couple JS dependencies to your `package.json` file, publish some JS scripts to your `resources/js` folder that configures Turbo.js for you:

```bash
php artisan turbo:install
```

Next, you may install your JS dependencies and compile the assets so the changes take effect:

```bash
npm install
npm run dev
```

If you are using Jetstream with Livewire, you may add the `--jet` flag to the `turbo:install` Artisan command, which will add a couple more JS dependencies to make sure Alpine.js works nicely with Turbo.js. This will also changes a couple lines to the layout files that ships with Jetstream, which will make sure Livewire works nicely as well:

```bash
php artisan turbo:install --jet
```

Then, you can run install your NPM dependencies and compile your assets normally.

These are the dependencies needed so Jetstream with Livewire works with Turbo.js:

* [Livewire Turbo Plugin](https://github.com/livewire/turbolinks) needed so Livewire works nicely. This one will be added to your Jetstream layouts as script tags fetching from a CDN (both `app.blade.php` and `guest.blade.php`)

You may also optionally install [Stimulus.js](https://stimulus.hotwired.dev/) passing `--stimulus` flag to the `turbo:install` Artisan command:

```bash
php artisan turbo:install --stimulus
```

Here's the full list of flags:

```bash
php artisan turbo:install --jet --stimulus
```

### Turbo HTTP Middleware

The package ships with a middleware which applies some conventions on your redirects, specially around how failed validations are handled automatically by Laravel. Read more about this in the [Conventions](#conventions) section of the documentation.

You may add the middleware to the "web" route group on your HTTP Kernel:

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
            // ...
            \Tonysm\TurboLaravel\Http\Middleware\TurboMiddleware::class,
        ],
    ];
}
```

Keep reading the documentation to have a full picture on how you can make the most out of the technique.

<a name="documentation"></a>
## Documentation

It's highly recommended reading the [Turbo Handbook](https://turbo.hotwired.dev/). Out of everything Turbo provides, it's Turbo Streams that benefits the most from a tight integration with Laravel. We can generate [Turbo Streams](#turbo-streams) from your models and either [return them from HTTP responses](#turbo-stream-request-macro) or *broadcast* your model changes over [WebSockets using Laravel Echo](#turbo-streams-and-laravel-echo).

* [Conventions](#conventions)
* [Overview](#overview)
* [Notes on Turbo Drive and Turbo Frames](#notes-on-turbo-drive-and-turbo-frames)
* [Blade Directives and Helper Functions](#blade-directives-and-helper-functions)
* [Turbo Streams](#turbo-streams)
* [Custom Turbo Stream Views](#custom-turbo-stream-views)
* [Broadcasting Turbo Streams Over WebSockets with Laravel Echo](#broadcasting)
    * [Broadcasting Model Changes](#broadcasting-model-changes)
    * [Listening to Broadcasts](#listening-to-broadcasts)
    * [Broadcasting to Others](#broadcasting-to-others)
* [Validation Response Redirects](#redirects)
* [Turbo Native](#turbo-native)
* [Testing Helpers](#testing-helpers)
* [Closing Notes](#closing-notes)

<a name="conventions"></a>
### Conventions

First of all, none of these conventions are mandatory. Feel free to pick the ones you like and also add your own. With that out of the way, here's a list of some conventions that I find helpful:

* You may want to use resource routes for most things (`posts.index`, `posts.store`, etc)
* You may want to split your views into smaller chunks or _partials_ (small portions of HTML for specific fragments), such as `comments/_comment.blade.php` that displays a comment resource, or `comments/_form.blade.php` for the form to either create/update comments. This will allow you to reuse these partials in [Turbo Streams](#turbo-streams)
* Your model's partial (such as the `comments/_comment.blade.php` for a `Comment` model, for example) may only rely on having a `$comment` instance passed to it. When broadcasting your model changes and generating the Turbo Streams in background, the package will pass the model instance using the model's basename in _camelCase_ to that partial - although you can fully control this behavior
* You may use the model's Fully Qualified Class Name, or FQCN for short, on your Broadcasting Channel authorization routes with a wildcard, such as `App.Models.Comment.{comment}` for a `Comment` model living in `App\\Models\\` - the wildcard's name doesn't matter. This is now the default broadcasting channel in Laravel (see [here](https://laravel.com/docs/8.x/broadcasting#model-broadcasting-conventions)).

In the [Overview section](#overview) below you will see how to override most of the default behaviors, if you want to.

<a name="overview"></a>
### Overview

Once the assets are compiled, you will have Turbo-specific custom HTML tags that you may annotate your views with (Turbo Frames and Turbo Streams). This is vanilla Hotwire. Again, it's recommended to read the [Turbo Handbook](https://turbo.hotwired.dev/handbook/introduction). Once you understand how these few pieces work together, the challenge will be in decomposing your UI to work as you want them to.

<a name="notes-on-turbo-drive-and-turbo-frames"></a>
### Notes on Turbo Drive and Turbo Frames

To keep it short, Turbo Drive will turn links and form submissions into AJAX requests and will replace the page with the response. That's useful when you want to navigate to another page entirely.

If you want some elements to persist across these navigations, you may annotate these elements with a DOM ID and add the `data-turbo-permanent` custom attribute to them. As long as the response also contains an element with the same ID and `data-turbo-permanent`, Turbo will not touch it.

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

Turbo Frames also allows you to lazy-load the frame's content. You may do so by adding a `src` attribute to the Turbo Frame tag. The conetnt of a lazy-loading Turbo Frame tag can be used to indicate "loading states", such as:

```blade
<turbo-frame id="my_frame" src="{{ route('my.page') }}">
    <p>Loading...</p>
</turbo-frame>
```

Turbo will automatically fire a GET AJAX request as soon as a lazy-loading Turbo Frame enters the DOM and replace its content with a matching Turbo Frame in the response.

You may also trigger a Turbo Frame with forms and links that are _outside_ of such frames by pointing to them like so:

```blade
<div>
    <a href="/somewhere" data-turbo-frame="my_frame">I'm a link</a>

    <turbo-frame id="my_frame"></turbo-frame>
</div>
```

You could also "hide" this link and trigger a "click" event with JavaScript programmatically to trigger the Turbo Frame to reload, for example.

So far, all vanilla Hotwire and Turbo.

<a name="blade-directives-and-helper-functions"></a>
### Blade Directives and Helper Functions

Since Turbo rely a lot on DOM IDs, the package offers a helper to generate unique DOM IDs based on your models. You may use the `@domid` Blade Directive in your Blade views like so:

```blade
<turbo-frame id="@domid($comment)">
    <!-- Content -->
</turbo-frame>
```

This will generate a DOM ID string using your model's basename and its ID, such as `comment_123`. You may also give it a _content_ that will prefix your DOM ID, such as:

```blade
<turbo-frame id="@domid($post, 'comments_count')">(99)</turbo-frame>
```

Which will generate a `comments_count_post_123` DOM ID.

The package also ships with a namespaced `dom_id()` helper function so you can use it outside of your own views:

```php
use function Tonysm\TurboLaravel\dom_id;

dom_id($comment);
```

When a new instance of a model is passed to any of these DOM ID helpers, since it doesn't have an ID, it will prefix the resource anme with a `create_` prefix. This way, new instances of an `App\\Models\\Comment` model will generate a `create_comment` DOM ID.

These helpers strip out the model's FQCN (see [config/turbo-laravel.php](config/turbo-laravel.php) if you use an unconventional location for your models).

<a name="turbo-streams"></a>
### Turbo Streams

As mentioned earlier, out of everything Turbo provides, it's Turbo Streams that benefit the most from a back-end integration.

Turbo Drive will get your pages behaving like an SPA and Turbo Frames will allow you to have a finer grained control of chunks of your page instead of replace the entire page when a form is submitted or a link is clicked.

However, sometimes you want to update _multiple_ parts of you page at the same time. For instance, after a form submission to create a comment, you may want to append the comment to the comment's list and also update the comment's count in the page. You may achieve that with Turbo Streams.

Any non-GET form submission will get annotated by Turbo with a `Content-Type: text/vnd.turbo-stream.html` header (besides the other normal Content Types). This will indicate your back-end that you can return a Turbo Stream response for that form submission if you want to.

Here's an example of a route handler detecting and returning a Turbo Stream response to a form submission:

```php
Route::post('posts/{post}/comments', function (Post $post) {
    $comment = $post->comments()->create(/** params */);

    if (request()->wantsTurboStream()) {
        return response()->turboStream()->append($comment);
    }

    return back();
});
```

The `request()->wantsTurboStream()` macro added to the request will check if the request accepts Turbo Stream and return `true` or `false` accordingly.

Here's what the HTML response will look like:

```blade
<turbo-stream action="append" target="comments">
    <template>
        @include('comments._comment', ['comment' => $comment])
    </template>
</turbo-stream>
```

Most of these things were "guessed" based on the [naming conventions](#conventions) we talked about earlier. But you can override most things, like so:

```php
return response()->turboStream($comment)->target('post_comments');
```

The model is optional, as it's only used to figure out the defaults based on the model state. You could manually create that same response like so:

```php
return response()->turboStream()
    ->target('comments')
    ->action('append')
    ->view('comments._comment', ['comment' => $comment]);
```

There are 7 _actions_ in Turbo Streams. They are:

* `append` & `prepend`: to add the elements in the target element at the top or at the bottom of its contents, respectively
* `before` & `after`: to add the elements next to the target element before or after, respectively
* `replace`: will replace the existing element entirely with the contents of the `template` tag in the Turbo Stream
* `update`: will keep the target and only replace the contents of it with the contents of the `template` tag in the Turbo Stream
* `remove`: will remove the element. This one doesn't need a `<template>` tag. It accepts either an instance of a Model or the DOM ID of the element to be removed as a string.

Which means you will find shorthand methods for them all, like:

```php
response()->turboStream()->append($comment);
response()->turboStream()->prepend($comment);
response()->turboStream()->before($comment, 'target_dom_id');
response()->turboStream()->after($comment, 'target_dom_id');
response()->turboStream()->replace($comment);
response()->turboStream()->update($comment);
response()->turboStream()->remove($comment);
```

You can read more about Turbo Streams in the [Turbo Handbook](https://turbo.hotwired.dev/handbook/streams).

These shorthand methods return a pending object for the response which you can chain and override everything you want on it:

```php
return response()->turboStream()
    ->append($comment)
    ->view('comments._comment_card', ['comment' => $comment]);
```

As mentioned earlier, passing a model to the `response()->turboStream()` macro will pre-fill the pending response object with some defaults based on the model's state.

It will build a `remove` Turbo Stream if the model was deleted (or if it is trashed - in case it's a Soft Deleted model), an `append` if the model was recently created (which you can override the action as the second parameter of the macro), a `replace` if the model was just updated (you can also override the action as the second parameter.) Here's how overriding would look like:

```php
return response()->turboStream($comment, 'append');
```

You may combine multiple Turbo Stream responses in a single one like so:

```php
return response()->turboStream([
    response()->turboStream()->append($commend),
    response()->turboStream()->remove($commend)->target('remove-target-id'),
]);
```

Although this is an option, it might feel like too much work for a controller. If that's the case, use [Custom Turbo Stream Views](#custom-turbo-stream-views).

<a name="custom-turbo-stream-views"></a>
### Custom Turbo Stream Views

If you're not using the model partial [convention](#conventions) or if you have some more complex Turbo Stream constructs, you may use the `response()->turboStreamView()` version instead and specify your own Turbo Stream views.

This is what it looks like:

```php
return response()->turboStreamView('comments.turbo.created_stream', [
    'comment' => $comment,
]);
```

And here's an example of a more complex custom Turbo Stream view:

```blade
@include('layouts.turbo.flash_stream')

<turbo-stream target="comments" action="append">
    <template>
        @include('comments._comment', ['comment' => $comment])
    </template>
</turbo-stream>
```

Remember, these are Blade views, so you have the full power of Blade at your hands. In this example, we're including a shared Turbo Stream partial which could append any flash messages we may have. That `layouts.turbo.flash_stream` could look like this:

```blade
@if (session()->has('status'))
<turbo-stream target="notice" action="append">
    <template>
        @include('layouts._flash')
    </template>
</turbo-stream>
@endif
```

I hope you can see how powerful this can be to reusing views.

<a name="broadcasting"></a>
### Broadcasting Turbo Streams Over WebSockets With Laravel Echo

So far, we have used Turbo Streams over HTTP to handle the case of updating multiple parts of the page for a single user after a form submission. In addition to that, you may want to broadcast model changes over WebSockets to all users that are viewing the same page. Although nice, **you don't have to use WebSockets if you don't have the need for it. You may still benefit from Turbo Streams over HTTP.**

Those same Turbo Stream responses we are returning to a user after a form submission, we can also send those to other users connected to a Laravel Echo channel and have their pages update reflecting the model change made by other users.

You may still feed the user making the changes with Turbo Streams over HTTP and broadcast the changes to other users over WebSockets. This way, the user making the change will have an instant feedback compared to having to wait for a background worker to pick up the job and send it to them over WebSockets.

First, setup the [Laravel Broadcasting](https://laravel.com/docs/8.x/broadcasting) component for your app. One of the first steps is to configure your environment variables to something that looks like this:

```dotenv
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=us2
PUSHER_APP_HOST=websockets.test
PUSHER_APP_PORT=6001

MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
MIX_PUSHER_APP_HOST="localhost"
MIX_PUSHER_APP_PORT="${PUSHER_APP_PORT}"
MIX_PUSHER_APP_USE_SSL=false
```

Notice that some of these environment variables are used by your front-end assets during compilation. That's why you see some duplicates that are just prefixed with `MIX_`.

These settings assume you're using the [Laravel WebSockets](https://github.com/beyondcode/laravel-websockets) package. Check out the Echo configuration at [resources/js/bootstrap.js](stubs/resources/js/bootstrap.js) to see which environment variables are needed during build time. You may also use [Pusher](https://pusher.com/) or [Ably](https://ably.io/) instead of the Laravel WebSockets package, if you don't want to host it yourself.

<a name="broadasting-model-changes"></a>
#### Broadcasting Model Changes

With Laravel Echo properly configured, you may now broadcast model changes using WebSockets. First thing you need to do is use the `Broadcasts` trait in your model:

```php
use Tonysm\TurboLaravel\Models\Broadcasts;

class Comment extends Model
{
    use Broadcasts;
}
```

This trait will add some methods to your model that you can use to trigger broadcasts. Here's how you can broadcast appending a new comment to all users visiting the post page:

```php
Route::post('posts/{post}/comments', function (Post $post) {
    $comment = $post->comments()->create(/** params */);

    $comment->broadcastAppend()->later();

    if (request()->wantsTurboStream()) {
        return response()->turboStream($comment);
    }

    return back();
});
```

Here are the methods now available to your model:

```php
$comment->broadcastAppend();
$comment->broadcastPrepend();
$comment->broadcastBefore('target_dom_id');
$comment->broadcastAfter('target_dom_id');
$comment->broadcastReplace();
$comment->broadcastUpdate();
$comment->broadcastRemove();
```

These methods will assume you want to broadcast the Turbo Streams to your model's channel. However, you will also find alternative methods where you can specify either a model or the broadcasting channels you want to send the broadcasts to:

```php
$comment->broadcastAppendTo($post);
$comment->broadcastPrependTo($post);
$comment->broadcastBeforeTo($post, 'target_dom_id');
$comment->broadcastAfterTo($post, 'target_dom_id');
$comment->broadcastReplaceTo($post);
$comment->broadcastUpdateTo($post);
$comment->broadcastRemoveTo($post);
```

These `broadcastXTo()` methods accept either a model, a channel instance or an array containing both of these. When it receives a model, it will guess the channel name using the broadcasting channel convention (see [#conventions](#conventions)).

All of these broadcasting methods return an instance of a `PendingBroadcast` class that will only dispatch the broadcasting job when that pending object is being garbage collected. Which means that you can control a lot of the properties of the broadcast by chaining on that instance before it goes out of scope, like so:

```php
$comment->broadcastAppend()
    ->to($post)
    ->view('comments/_custom_view_partial', [
        'comment' => $comment,
        'post' => $post,
    ])
    ->toOthers() // Do not send to the current user.
    ->later(); // Dispatch a background job to send.
```

You may want to hook those methods in the model events of your model to trigger Turbo Stream broadcasts whenever your models are changed in any context, such as:

```php
class Comment extends Model
{
    use Broadcasts;

    protected static function booted()
    {
        static::created(function (Comment $comment) {
            $comment->broadcastPrependTo($comment->post)
                ->toOthers()
                ->later();
        });

        static::updated(function (Comment $comment) {
            $comment->broadcastReplaceTo($comment->post)
                ->toOthers()
                ->later();
        });

        static::deleted(function (Comment $comment) {
            $comment->broadcastRemoveTo($comment->post)
                ->toOthers()
                ->later();
        });
    }
}
```

In case you want to broadcast all these changes automatically, instead of specifying them all, you may want to add a `$broadcasts` property to your model, which will instruct the `Broadcasts` trait to trigger the Turbo Stream broadcasts for the created, updated and deleted model events, like so:

```php
class Comment extends Model
{
    use Broadcasts;

    protected $broadcasts = true;
}
```

This will achieve almost the same thing as the example where we registered the model events manually, with a couple nuanced differences. First, by default, it will broadcast an `append` Turbo Stream to newly created models. You may want to use `prepend` instead. You can do so by using an array with a `insertsBy` key and `prepend` action as value instead of a boolean, like so:

```php
class Comment extends Model
{
    use Broadcasts;

    protected $broadcasts = [
        'insertsBy' => 'prepend',
    ];
}
```

This will also automatically hook into the model events, but instead of broadcasting new instances as `append` it will use `prepend`.

Secondly, it will send all changes to this model's broadacsting channel. In our case, we want to direct the broadcasts to the post linked to this model instead. We can achieve that by adding a `$broadcastsTo` property to the model, like so:

```php
class Comment extends Model
{
    use Broadcasts;

    protected $broadcasts = [
        'insertsBy' => 'prepend',
    ];

    protected $broadcastsTo = 'post';

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
```

That property can either be a string that contains to the name of a relationship of this model or an array of relationships.

Alternatively, you may prefer to have more control over where these broadcasts are being sent to by implementing a `broadcastsTo` method in your model instead of using the property. This way, you can return a single model, a broadcasting channel instance or an array containing either of them, like so:

```php
use Illuminate\Broadcasting\Channel;

class Comment extends Model
{
    use Broadcasts;

    protected $broadcasts = [
        'insertsBy' => 'prepend',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function broadcastsTo()
    {
        return [
            $this,
            $this->post,
            new Channel('full-control'),
        ];
    }
}
```

<a name="listening-to-broadcasts"></a>
#### Listening to Turbo Stream Broadcasts

You may listen to a Turbo Stream broadcast message on your pages by adding the custom HTML tag `<turbo-echo-stream-source>` that is published to your application's assets (see [here](./stubs/resources/js/elements/turbo-echo-stream-tag.js)). You need to pass the channel you want to listen to broadcasts on using the `channel` attribute of this element, like so.

```blade
<turbo-echo-stream-source
    channel="App.Models.Comments.{{ $comment->id }}"
/>
```

By default, it expects a private channel, so the tag must be used in a page for already authenticated users. You can control the channel type in the tag with a `type` attribute.

```blade
<turbo-echo-stream-source
    channel="App.Models.Comments.{{ $comment->id }}"
    type="presence"
/>
```

As this convention is not built into Laravel, you can use the model's `broadcastChannel()` method:

```blade
<turbo-echo-stream-source
    channel="{{ $comment->broadcastChannel() }}"
/>
```

There is also a helper blade directive that you can use to generate the channel name for your models using the same convention if you want to:

```blade
<turbo-echo-stream-source
    channel="@channel($comment)"
/>
```

To register the Broadcast Auth Route you may use Laravel's built-in conventions as well:

```php
// file: routes/channels.php

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel(Post::class, function (User $user, Post $post) {
    return $user->belongsToTeam($post->team);
});
```

You may want to read the [Laravel Broadcasting](https://laravel.com/docs/8.x/broadcasting) documentation.

<a name="broadcasting-to-others"></a>
#### Broadcasting Turbo Streams to Other Users Only

As mentioned erlier, you may want to feed the current user with Turbo Streams using HTTP requests and only send the broadcasts to other users. There are a couple ways you can achieve that.

First, you can chain on the broadcasting methods, like so:

```php
$comment->broadcastAppendTo($post)
    ->toOthers();
```

Second, you can use the Turbo Facade like so:

```php
use Tonysm\TurboLaravel\Facades\Turbo;

Turbo::broadcastToOthers(function () {
    // ...
});
```

This way, any broadcast that happens inside the scope of the Closure will only be sent to other users.

Third, you may use that same method but without the Closure inside a ServiceProvider, for instance, to instruct the package to only send turbo stream broadcasts to other users globally:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Tonysm\TurboLaravel\Facades\Turbo;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Turbo::broadcastToOthers();
    }
}
```

<a name="redirects"></a>
### Validation Response Redirects

By default, Laravel will redirect failed validation exceptions "back" to the page the triggered the request. This is a bit problematic when it comes to Turbo Frames, since a form might be included in a page that don't render the form initially, and after a failed validation exception from a form submission we would want to re-render the form with the invalid messages.

In other words, a Turbo Frame inherits the context of the page where it was inserted in, and a form might not be part of that page itself. We can't redirect "back" to display the form again with the error messages, because the form might not be re-rendered there by default. Instead, we have two options:

1. Render a Blade view with the form as a non-200 HTTP Status Code, then Turbo will look for a matching Turbo Frame inside the response and replace only that portion or page, but it won't update the URL as it would for other Turbo Visits; or
2. Redirect the request to a page that renders the form directly instead of "back". There you can render the validation messages and all that. Turbo will follow the redirect (303 Status Code) and fetch the Turbo Frame with the form and invalid messages and update the existing one.

When using the `\Tonysm\TurboLaravel\Http\Middleware\TurboMiddleware` middleware that ships with the package on your HTTP Kernel's "web" route group, it will override Laravel's default handling for failed validation exceptions.

For any route name ending in `.store`, it will redirect to a `.create` route for the same resource with all the route params from the previous request. In the same way, for any `.update` routes, it will redirect to a `.edit` route of the same resource.

Examples:

- `posts.comments.store` will redirect to `posts.comments.create` with the `{post}` route param.
- `comments.store` will redirect to `comments.create` with no route params.
- `comments.update` will redirect to `comments.edit` with the `{comment}` param.

If a guessed route name doesn't exist, the middleware will not change the redirect response. You may override this behavior by catching the `ValidationException` yourself and re-throwing it overriding the redirect with the `redirectTo` method. If the exception has that, the middleware will respect it.

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

You may also catch the `ValidationException` and return a non-200 response, if you want to.

<a name="turbo-native"></a>
### Turbo Native

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

<a name="testing-helpers"></a>
### Testing Helpers

There are two aspects of your application using Turbo Laravel that are specific this approach itself:

1. **Turbo Stream HTTP responses.** As you return Turbo Stream responses from your route handlers/controllers to be applied by Turbo itself; and
1. **Turbo Stream broadcasts.** Which is the side-effect of certain model changes or whenever you call `$model->broadcastAppend()` on your models, for instance.

We're going to cover both of these scenarios here.

#### Making Turbo & Turbo Native HTTP requests

To enhance your testing capabilities here, Turbo Laravel adds a couple of macros to the TestResponse that Laravel uses under the hood. The goal is that testing Turbo Stream responses is as convenient as testing regular HTTP responses.

To mimic Turbo requests, which means sending a request setting the correct Content-Type in the `Accept:` HTTP header, you need to use the `InteractsWithTurbo` trait to your testcase. Now you can mimic a Turbo HTTP request by using the `$this->turbo()` method before you make the HTTP call itself. You can also mimic Turbo Native specific requests by using the `$this->turboNative()` also before you make the HTTP call. The first method will add the correct Turbo Stream content type to the `Accept:` header, and the second method will add Turbo Native `User-Agent:` value.

These methods are handy when you are conditionally returning Turbo Stream responses based on the `request()->wantsTurboStream()` helper, for instance. Or when using the `@turbonative` or `@unlessturbonative` Blade directives.

#### Testing Turbo Stream HTTP Responses

You can test if you got a Turbo Stream response by using the `assertTurboStream`. Similarly, you can assert that your response is _not_ a Turbo Stream response by using the `assertNotTurboStream()` macro:

```php
use Tonysm\TurboLaravel\Testing\InteractsWithTurbo;

class CreateTodosTest extends TestCase
{
    use InteractsWithTurbo;

    /** @test */
    public function creating_todo_from_turbo_request_returns_turbo_stream_response()
    {
        $response = $this->turbo()->post(route('todos.store'), [
            'content' => 'Test the app',
        ]);

        $response->assertTurboStream();
    }

    /** @test */
    public function creating_todo_from_regular_request_does_not_return_turbo_stream_response()
    {
        // Notice we're not chaining the `$this->turbo()` method here.
        $response = $this->post(route('todos.store'), [
            'content' => 'Test the app',
        ]);

        $response->assertNotTurboStream();
    }
}
```

The controller for such response would be something like this:

```php
class TodosController
{
    public function store()
    {
        $todo = auth()->user()->todos()->create(request()->validate([
            'content' => ['required'],
        ]));

        if (request()->wantsTurboStream()) {
            return response()->turboStream($todo);
        }

        return redirect()->route('todos.index');
    }
}
```

#### Fluent Turbo Stream Testing

You can get specific on your Turbo Stream responses by passing a callback to the `assertTurboStream(fn)` method. This can be used to test that you have a specific Turbo Stream tag being returned, or that you're returning exactly 2 Turbo Stream tags, for instance:

```php
/** @test */
public function create_todos()
{
    $this->get(route('todos.store'))
        ->assertTurboStream(fn (AssertableTurboStream $turboStreams) => (
            $turboStreams->has(2)
            && $turboStreams->hasTurboStream(fn ($turboStream) => (
                $turboStream->where('target', 'flash_messages')
                            ->where('action', 'prepend')
                            ->see('Todo was successfully created!')
            ))
            && $turboStreams->hasTurboStream(fn ($turboStream) => (
                $turboStream->where('target', 'todos')
                            ->where('action', 'append')
                            ->see('Test the app')
            ))
        ));
}
```

#### Testing Turbo Stream Broadcasts

Every broadcast will be dispatched using the `Tonysm\TurboLaravel\Jobs\BroadcastAction` job (either to a worker or process synchronously). You may also use that to test your broadcasts like so:

```php
use App\Models\Todo;
use Tonysm\TurboLaravel\Jobs\BroadcastAction;

class CreatesCommentsTest extends TestCase
{
    /** @test */
    public function creates_comments()
    {
        Bus::fake(BroadcastAction::class);

        $todo = Todo::factory()->create();

        $this->turbo()->post(route('todos.comments.store', $todo), [
            'content' => 'Hey, this is really nice!',
        ])->assertTurboStream();

        Bus::assertDispatched(function (BroadcastAction $job) use ($todo) {
            return count($job->channels) === 1
                && $job->channels[0]->name === sprintf('private-%s', $todo->broadcastChannel())
                && $job->target === 'comments'
                && $job->action === 'append'
                && $job->partial === 'comments._comment'
                && $job->partialData['comment']->is(
                    $todo->comments->first()
                );
        });
    }
}
```

*Note: make sure your `turbo-laravel.queue` config key is set to false, otherwise actions may not be dispatched during test because the model observer only fires them after the transaction is commited, which never happens in tests since they run inside a transaction.*

<a name="closing-notes"></a>
### Closing Notes

Try the package out. Use your Browser's DevTools to inspect the responses. You will be able to spot every single Turbo Frame and Turbo Stream happening.

> "The proof of the pudding is in the eating."

Make something awesome!

## Testing the Package

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

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

- [Tony Messias](https://github.com/tonysm)
- [All Contributors](./CONTRIBUTORS.md)
