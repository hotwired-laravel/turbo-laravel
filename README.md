<p align="center" style="margin-top: 2rem; margin-bottom: 2rem;"><img src="/art/turbo-laravel-logo.svg" alt="Logo Turbo Laravel" /></p>

<p align="center">
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

This package gives you a set of conventions to make the most out of [Hotwire](https://hotwired.dev/) in Laravel.

#### Inspiration

This package was inspired by the [Turbo Rails gem](https://github.com/hotwired/turbo-rails).

#### Demo App

If you want to see this package in action with actual code using it, head out to the [companion application repository](https://github.com/tonysm/turbo-demo-app). You can either run the code yourself or check out a live version of it [here](https://turbo-laravel.tonysm.com).

<a name="installation"></a>
## Installation

Turbo Laravel may be installed via composer:

```bash
composer require tonysm/turbo-laravel
```

After installing, you may execute the `turbo:install` Artisan command, which will add a couple JS dependencies to your `package.json` file (when you're using NPM) or to your `routes/importmap.php` file (when you're using [Importmap Laravel](https://github.com/tonysm/importmap-laravel)), publish some JS scripts to your `resources/js` folder that configures Turbo.js for you:

```bash
php artisan turbo:install
```

If you are using Jetstream with Livewire, you may add the `--jet` flag to the `turbo:install` Artisan command, which will add a couple more JS dependencies to make sure Alpine.js works nicely with Turbo.js. This will also change a couple of lines to the layout files that ships with Jetstream, which will make sure Livewire works nicely as well:

```bash
php artisan turbo:install --jet
```

When using Jetstream with Livewire, the [Livewire Turbo Plugin](https://github.com/livewire/turbolinks) is needed so Livewire works nicely with Turbo. This one will be added to your Jetstream layouts as script tags fetching from a CDN (both `app.blade.php` and `guest.blade.php`).

If you're not using [Importmap Laravel](https://github.com/tonysm/importmap-laravel), the install command will tell you to pull and compile the assets before proceeding:

```bash
npm install && npm run dev
```

You may also optionally install [Stimulus.js](https://stimulus.hotwired.dev/) passing `--stimulus` flag to the `turbo:install` Artisan command:

```bash
php artisan turbo:install --stimulus
```

You may also optionally install [Alpine.js](https://alpinejs.dev/) in a non-Jetstream context (maybe you're more into [Breeze](https://laravel.com/docs/9.x/starter-kits#laravel-breeze)) passing `--alpine` flag to the `turbo:install` Artisan command:

```bash
php artisan turbo:install --alpine
```

_Note: the `--jet` option also adds all the necessary Alpine dependencies since Jetstream depends on Alpine._

### Turbo HTTP Middleware

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

Keep reading the documentation to have a full picture on how you can make the most out of the technique.

<a name="documentation"></a>
## Documentation

It's highly recommended reading the [Turbo Handbook](https://turbo.hotwired.dev/). Out of everything Turbo provides, it's Turbo Streams that benefits the most from a tight integration with Laravel. We can generate [Turbo Streams](#turbo-streams) from your models and either [return them from HTTP responses](#turbo-stream-request-macro) or *broadcast* your model changes over [WebSockets using Laravel Echo](#turbo-streams-and-laravel-echo).

* [Conventions](#conventions)
* [Overview](#overview)
* [Notes on Turbo Drive and Turbo Frames](#notes-on-turbo-drive-and-turbo-frames)
* [Blade Directives and Helper Functions](#blade-directives-and-helper-functions)
* [Turbo Streams](#turbo-streams)
    * [Turbo Streams Combo](#turbo-streams-combo)
* [Custom Turbo Stream Views](#custom-turbo-stream-views)
* [Broadcasting Turbo Streams Over WebSockets with Laravel Echo](#broadcasting)
    * [Broadcasting Model Changes](#broadcasting-model-changes)
    * [Listening to Broadcasts](#listening-to-broadcasts)
    * [Broadcasting to Others](#broadcasting-to-others)
* [Validation Response Redirects](#redirects)
* [Turbo Native](#turbo-native)
* [Testing Helpers](#testing-helpers)
* [Known Issues](#known-issues)
* [Closing Notes](#closing-notes)

<a name="conventions"></a>
### Conventions

None of the conventions described bellow are mandatory. Feel free to pick the ones you like and also come up with your own conventions. With that out of the way, here's a list of conventions you may find helpful:

* You may want to use [resource routes](https://laravel.com/docs/8.x/controllers#resource-controllers) for most things (`posts.index`, `posts.store`, etc.)
* You may want to split up your views in smaller chunks (aka. "partials"), such as `comments/_comment.blade.php` which displays a comment resource, or `comments/_form.blade.php` for the form to either create/update comments. This will allow you to reuse these _partials_ in [Turbo Streams](#turbo-streams)
* Your models' partials (such as the `comments/_comment.blade.php` for a `Comment` model) may only rely on having a single `$comment` instance variable passed to them. That's because the package will, by default, figure out the partial for a given model when broadcasting and will also pass the model such partial, using the class basename as the variable instance in _camelCase_. Again, that's by default, you can customize most things
* You may use the model's Fully Qualified Class Name (aka. FQCN), on your Broadcasting Channel authorization routes with a wildcard, such as `App.Models.Comment.{comment}` for a `Comment` model living in `App\\Models\\` - the wildcard's name doesn't matter, as long as there is one. This is the default [broadcasting channel naming convention](https://laravel.com/docs/8.x/broadcasting#model-broadcasting-conventions) in Laravel

In the [Overview section](#overview) below you will see how to override most of the defaults here, if you want to.

<a name="overview"></a>
### Overview

When Turbo.js is installed, you will have Turbo-specific custom HTML tags that you may use in your views: Turbo Frames and Turbo Streams. This is vanilla Hotwire. Again, it's recommended to read the [Turbo Handbook](https://turbo.hotwired.dev/handbook/introduction). Once you understand how these few pieces work together, the challenge will be in decomposing your UI to work as you want them to.

<a name="notes-on-turbo-drive-and-turbo-frames"></a>
### Notes on Turbo Drive and Turbo Frames

To keep it short, Turbo Drive will turn links and form submissions into AJAX requests and will replace the page with the response. That's useful when you want to navigate to another page entirely.

If you want some elements to persist across these navigations, you may annotate these elements with a DOM ID and add the `data-turbo-permanent` custom attribute to them. As long as the response also contains an element with the same DOM ID and `data-turbo-permanent` attribute, Turbo will not touch it.

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

Turbo Frames also allows you to lazy-load the frame's content. You may do so by adding a `src` attribute to the Turbo Frame tag. The content of a lazy-loading Turbo Frame tag can be used to indicate "loading states", such as:

```blade
<turbo-frame id="my_frame" :src="route('my.page')">
    <p>Loading...</p>
</turbo-frame>
```

Turbo will automatically dispatch a GET AJAX request as soon as a lazy-loading Turbo Frame enters the DOM and replace its content with a matching Turbo Frame in the response.

You may also trigger a Turbo Frame with forms and links that are _outside_ of such frames by pointing to them with a `data-turbo-frame` attribute:

```blade
<div>
    <a href="/somewhere" data-turbo-frame="my_frame">I'm a link</a>

    <turbo-frame id="my_frame"></turbo-frame>
</div>
```

You could also "hide" this link and trigger a "click" event with JavaScript programmatically to trigger the Turbo Frame to reload, for example.

So far, all vanilla Hotwire and Turbo.

<a name="blade-directives-and-helper-functions"></a>
### Blade Components, Directives, and Helper Functions

Since Turbo relies a lot on DOM IDs, the package offers a helper to generate unique DOM IDs based on your models. You may use the `@domid` Blade Directive in your Blade views like so:

```blade
<turbo-frame id="@domid($comment)">
    <!-- Content -->
</turbo-frame>
```

This will generate a DOM ID string using your model's basename and its ID, such as `comment_123`. You may also give it a prefix that will added to the DOM ID, such as:

```blade
<turbo-frame id="@domid($post, 'comments_count')">(99)</turbo-frame>
```

Which will generate a `comments_count_post_123` DOM ID, assuming your Post model has an ID of `123`.

You may also prefer using the `<x-turbo-frame>` Blade component that ships with the package. This way, you don't need to worry about using the `@domid()` helper for your Turbo Frame:

```blade
<x-turbo-frame :id="$comment">
    <!-- Content -->
</x-turbo-frame>
```

To the `:id` prop, you may pass a string, which will be used as-is as the DOM ID, an Eloquent model instance, which will be passed to the `dom_id()` function that ships with the package (the same one as the `@domid()` Blade directive uses behind the scenes), or an array tuple where the first item is an instance of an Eloquent model and the second is the prefix of the DOM ID, something like this:

```blade
<x-turbo-frame :id="[$post, 'comments_count']">(99)</x-turbo-frame>
```

Additionally, you may also pass along any prop that is supported by the Turbo Frame custom Element to the `<x-turbo-frame>` Blade component, like `target`, `src`, or `loading`. These are the listed attributes, but any other attribute will also be forwarded to the `<turbo-frame>` tag that will be rendered by the `<x-turbo-frame>` component. For a full list of what's possible to do with Turbo Frames, see the [documentation](https://turbo.hotwired.dev/handbook/frames).

The mentioned namespaced `dom_id()` helper function may also be used from anywhere in your application, like so:

```php
use function Tonysm\TurboLaravel\dom_id;

dom_id($comment);
```

When a new instance of a model is passed to any of these DOM ID helpers, since it doesn't have an ID, it will prefix the resource name with a `create_` prefix. This way, new instances of an `App\\Models\\Comment` model will generate a `create_comment` DOM ID.

These helpers strip out the model's FQCN (see [config/turbo-laravel.php](config/turbo-laravel.php) if you use an unconventional location for your models).

<a name="turbo-streams"></a>
### Turbo Streams

As mentioned earlier, out of everything Turbo provides, it's Turbo Streams that benefits the most from a back-end integration.

Turbo Drive will get your pages behaving like an SPA and Turbo Frames will allow you to have a finer grained control of chunks of your page instead of replacing the entire page when a form is submitted or a link is clicked.

However, sometimes you want to update _multiple_ parts of your page at the same time. For instance, after a form submission to create a comment, you may want to append the comment to the comment's list and also update the comment's count in the page. You may achieve that with Turbo Streams.

Form submissions will get annotated by Turbo with a `Accept: text/vnd.turbo-stream.html` header (besides the other normal Content Types). This will indicate to your back-end that you can return a Turbo Stream response for that form submission if you want to.

Here's an example of a route handler detecting and returning a Turbo Stream response to a form submission:

```php
Route::post('posts/{post}/comments', function (Post $post) {
    $comment = $post->comments()->create(/** params */);

    if (request()->wantsTurboStream()) {
        return response()->turboStream($comment);
    }

    return back();
});
```

The `request()->wantsTurboStream()` macro added to the request will check if the request accepts Turbo Stream and return `true` or `false` accordingly.

Here's what the HTML response will look like:

```html
<turbo-stream action="append" target="comments">
    <template>
        <div id="comment_123">
            <p>Hello, World</p>
        </div>
    </template>
</turbo-stream>
```

Most of these things were "guessed" based on the [naming conventions](#conventions) we talked about earlier. But you can override most things, like so:

```php
return response()->turboStream($comment)->target('post_comments');
```

Although it's handy to pass the model instance to the `turboStream()` response macro - which will be used to decide the default values of the Turbo Stream response based on the model's current state, sometimes you may want to build a Turbo Stream response manually, which can be achieved like so:

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
response()->turboStream()->before($comment);
response()->turboStream()->after($comment);
response()->turboStream()->replace($comment);
response()->turboStream()->update($comment);
response()->turboStream()->remove($comment);
```

For these shorthand stream builders, you may pass an instance of an Eloquent model, which will be used to figure out things like `target`, `action`, and the `view` partial as well as the view data passed to them.

Alternativelly, you may also pass strings to the shorthand stream builders, which will be used as the target, and an optional content string, which will be rendered instead of a partial, for instance:

```php
response()->turboStream()->append('statuses', __('Comment was successfully created!'));
```

The optional content parameter expects either a string, a view instance, or an instance of Laravel's `Illuminate\Support\HtmlString`, so you could do something like:

```php
response()->turboStream()->append('some_dom_id', view('greetings', ['name' => 'Tester']));
```

Or more explicitly by passing an instance of the `HtmlString` as content:

```php
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

response()->turboStream()->append('statuses', new HtmlString(
    Blade::render('<div>Hello, {{ $name }}</div>', ['name' => 'Tony'])
));
```

Which will result in a Turbo Stream like this:

```html
<turbo-stream target="statuses" action="append">
    <template>
        <div>Hello, Tony</div>
    </template>
</turbo-stream>
```

For both the `before` and `after` methods you need additional calls to specify the view template you want to insert, since the given model/string will only be used to specify the target, something like:

```php
response()->turboStream()
    ->before($comment)
    ->view('comments._flash_message', [
        'message' => __('Comment was created!'),
    ]);
```

Just like the other shorthand stream builders, you may also pass an option content string or `HtmlString` instance to the `before` and `after` shorthands. When doing that, you don't need to specify the view section.

```php
response()->turboStream()->before($comment, __('Oh, hey!'));
```

You can read more about Turbo Streams in the [Turbo Handbook](https://turbo.hotwired.dev/handbook/streams).

The shorthand methods return a pending object for the response which you can chain and override everything you want before it's rendered:

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

You may also [target multiple elements](https://turbo.hotwired.dev/reference/streams#targeting-multiple-elements) using CSS classes using the `xAll` methods:

```php
response()->turboStream()->appendAll('.comment', 'Some content');
response()->turboStream()->prependAll('.comment', 'Some content');
response()->turboStream()->updateAll('.comment', 'Some content');
response()->turboStream()->removeAll('.comment');
response()->turboStream()->beforeAll('.comment', 'Some content');
response()->turboStream()->afterAll('.comment', 'Some content');
```

With the exception of the `removeAll` method, all these `xAll` accept as the second parameter a string of inline content, an instance of a View (which you may create using the `view()` function provided by Laravel), or an instance of the `HtmlSafe` class.

When creating Turbo Stream using the builders, you may also specify the CSS class using the `targets()` (plural) method instead of the singular version:

```php
return response()->turboStream()
    ->targets('.comment')
    ->action('append')
    ->view('comments._comment', ['comment' => $comment]);
```

<a name="turbo-streams-combo"></a>
#### Turbo Streams Combo

You may combine multiple Turbo Stream responses in a single one like so:

```php
return response()->turboStream([
    response()->turboStream()
        ->append($comment)
        ->target(dom_id($comment->post, 'comments')),
    response()->turboStream()
        ->update(dom_id($comment->post, 'comments_count'), view('posts._comments_count', ['post' => $comment->post])),
]);
```

Although this is a valid option, it might feel like too much work for a controller. If that's the case, use [Custom Turbo Stream Views](#custom-turbo-stream-views).

<a name="custom-turbo-stream-views"></a>
### Custom Turbo Stream Views

If you're not using the model partial [convention](#conventions) or if you have some more complex Turbo Stream constructs to build, you may use the `response()->turboStreamView()` version instead and specify your own Blade view where Turbo Streams will be created. This is what that looks like:

```php
return response()->turboStreamView('comments.turbo.created_stream', [
    'comment' => $comment,
]);
```

And here's an example of a more complex custom Turbo Stream view:

```blade
@include('layouts.turbo.flash_stream')

<turbo-stream target="@domid($comment->post, 'comments')" action="append">
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

Similar to the `<x-turbo-frame>` Blade component, there's also a `<x-turbo-stream>` Blade component that can simplify things quite a bit. It has the same convention of figureing out the DOM ID of the target when you're passing a model instance or an array as the `<x-turbo-frame>` component applied to the `target` attribute here. When using the component version, there's also no need to specify the template wrapper for the Turbo Stream tag, as that will be added by the component itself. So, the same example would look something like this:

```blade
@include('layouts.turbo.flash_stream')

<x-turbo-stream :target="[$comment->post, 'comments']" action="append">
    @include('comments._comment', ['comment' => $comment])
</x-turbo-stream>
```

I hope you can see how powerful this can be to reusing views.

<a name="broadcasting"></a>
### Broadcasting Turbo Streams Over WebSockets With Laravel Echo

So far, we have used Turbo Streams over HTTP to handle the case of updating multiple parts of the page for a single user after a form submission. In addition to that, you may want to broadcast model changes over WebSockets to all users that are viewing the same page. Although nice, **you don't have to use WebSockets if you don't have the need for it. You may still benefit from Turbo Streams over HTTP.**

We can broadcast to all users over WebSockets those exact same Turbo Stream tags we are returning to a user after a form submission. That makes use of Laravel Echo and Laravel's Broadcasting component.

You may still feed the user making the changes with Turbo Streams over HTTP and broadcast the changes to other users over WebSockets. This way, the user making the change will have an instant feedback compared to having to wait for a background worker to pick up the job and send it to them over WebSockets.

First, you need to uncomment the Laravel Echo setup on your [`resources/js/bootstrap.js`](stubs/resources/js/bootstrap.js) file and make sure you compile your assets after doing that by running:

```bash
npm run dev
```

Then, you'll need to setup the [Laravel Broadcasting](https://laravel.com/docs/8.x/broadcasting) component for your app. One of the first steps is to configure your environment variables to look something like this:

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

Secondly, it will send all changes to this model's broadacsting channel, except for when the model is created (since no one would be listening to its direct channel). In our case, we want to direct the broadcasts to the post linked to this model instead. We can achieve that by adding a `$broadcastsTo` property to the model, like so:

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

That property can either be a string that contains the name of a relationship of this model or an array of relationships.

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

Newly created models using the auto-broadcasting feature will be broadcasted to a pluralized version of the model's basename. So if you have a `App\Models\PostComment`, you may expect broadcasts of newly created models to be sent to a private channel called `post_comments`. Again, this convention is only valid for newly created models. Updates/Removals will still be sent to the model's own private channel by default using Laravel's convention for channel names. You may want to specify the channel name for newly created models to be broadcasted to with the `stream` key:

```php
class Comment extends Model
{
    use Broadcasts;

    protected $broadcasts = [
        'stream' => 'some_comments',
    ];
}
```

Having a `$broadcastsTo` property or implementing the `broadcastsTo()` method in your model will have precedence over this, so newly created models will be sent to the channels specified on those places instead of using the convention or the `stream` option.

<a name="listening-to-broadcasts"></a>
#### Listening to Turbo Stream Broadcasts

You may listen to a Turbo Stream broadcast message on your pages by adding the custom HTML tag `<turbo-echo-stream-source>` that is published to your application's assets (see [here](./stubs/resources/js/elements/turbo-echo-stream-tag.js)). You need to pass the channel you want to listen to broadcasts on using the `channel` attribute of this element, like so.

```blade
<turbo-echo-stream-source
    channel="App.Models.Post.{{ $post->id }}"
/>
```

You may prefer using the convenient `<x-turbo-stream-from>` Blade component, passing the model as the `source` prop to it, something like this:

```blade
<x-turbo-stream-from :source="$post" />
```

By default, it expects a private channel, so the it must be used in a page for already authenticated users. You may control the channel type in the tag with a `type` attribute.

```blade
<x-turbo-stream-from :source="$post" type="public" />
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
### Validation Response

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

If a guessed route name doesn't exist (which will always happen if you don't use the route resorce convention), the middleware will not change the default handling of validation errors. You may also override this behavior by catching the `ValidationException` yourself and re-throwing it overriding the redirect with the `redirectTo` method. If the exception has that, the middleware will respect it and make a GET request to that location instead of trying to guess it.

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

#### Interacting With Turbo Native Navigation

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

<a name="known-issues"></a>
### Known Issues

If you ever encounter an issue with the package, look here first for documented solutions.

#### Fixing Laravel's Previous URL Issue

Visits from Turbo Frames will hit your application and Laravel by default keeps track of previously visited URLs to be used with helpers like `url()->previous()`, for instance. This might be confusing because chances are that you wouldn't want to redirect users to the URL of the most recent Turbo Frame that hit your app. So, to avoid storying Turbo Frames visits as Laravel's previous URL, head to the [issue](https://github.com/tonysm/turbo-laravel/issues/60#issuecomment-1123142591) where a solution was discussed.

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
