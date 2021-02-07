<center>

# ⚡ Turbo Laravel

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

</center>

**This package gives you a set of conventions to make the most out of [Hotwire](https://hotwire.dev/) in Laravel** (inspired by the [turbo-rails](https://github.com/hotwired/turbo-rails) gem). There is a [companion application](https://github.com/tonysm/turbo-demo-app) that shows how to use the package and its conventions in your application.

<a name="installation"></a>
## Installation

You may install the package via composer:

```bash
composer require tonysm/turbo-laravel
```

You may publish the asset files with:

```bash
php artisan turbo:install
```

You may also use Turbo Laravel with Jetstream if you use the Livewire stack. If you want to do so, you may want to publish the assets using the `--jet` flag:

```bash
php artisan turbo:install --jet
```

The install command will require and publish a couple JS files to your application. By default, it will add `@hotwired/turbo` to your `package.json` file and publish another custom HTML tag to integrate Turbo with Laravel Echo. With the `--jet` flag, it will also add a couple bridges libs needed to make sure you can use Hotwire with Jetstream, these are:

* [Alpine Turbo Bridge](https://github.com/SimoTod/alpine-turbo-drive-adapter), needed so Alpine.js works nicely; and
* [Livewire Turbo Plugin](https://github.com/livewire/turbolinks) needed so Livewire works nicely. This one will be added to your Jetstream layouts (both `app.blade.php` and `guest.blade.php`)

You can optionally also install Stimulus on top of this all by passing `--stimulus` flag to the `turbo:install` command. It's optional because we can either use Alpine.js or Stimulus (or both /shrug):

```bash
php artisan turbo:install --jet --stimulus
```

The package ships with a middleware that applies some conventions on your redirects, specially around how failed validations are redirected automatically by Laravel. To read more about this, check out the [Conventions](#conventions) section documentation. You may add the middleware to the "web" route group on your HTTP Kernel, like so:

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

* [Conventions](#conventions)
* [Overview](#overview)
    * [Turbo Drive](#turbo-drive)
        * [Permanent Fragments](#turbo-drive-permanent)
    * [Turbo Frames](#turbo-frames)
        * [Generating DOM IDs from Models](#dom-ids)
    * [Turbo Streams](#turbo-streams)
        * [request()->wantsTurboStream()](#wants-turbo-stream)
        * [response()->turboStream()](#turbo-stream-response)
        * [Overriding Default Partial and Data](#override-turbo-stream-partials-and-data)
        * [Overriding Default Resource Name and DOM ID for a Model](#override-turbo-stream-resource-and-dom-id)
        * [response()->turboStreamView()](#turbo-stream-view)
        * [Overriding Default Turbo Streams Views](#override-turbo-stream-views)
    * [Turbo Streams and Laravel Echo](#turbo-streams-and-laravel-echo)
        * [Broadcasting Turbo Streams with Model Events](#turbo-stream-broadcasting-with-events)
        * [Broadcasting Turbo Streams to related Model or Channels](#turbo-stream-broadcasting-destination)
        * [Broadcasting Turbo Streams with the Broadcasts trait](#turbo-stream-broadcasting-using-trait)
        * [Listening to Broadcasting from Laravel Echo](#turbo-streams-listening-to-echo-events)
    * [Validation Responses](#validation-responses)
    * [Turbo Native](#turbo-native)
    * [Testing Helpers](#testing-helpers)

<a name="conventions"></a>
## Conventions

It's important to note that this package does not enforce any of these conventions on your application. All conventions aim at reducing the boilerplate you would have to write yourself. However, if you don't want to follow them, you don't have to. Most conventions allow you to override the default behavior by either implementing some Hotwire specific methods or, you know, simply not using the goodies the package provide (or using only what you want).

With that being said, I think "convention over configuration" is an important goal, so here's a list with the conventions you may follow:

* You may want to use resource routes for most things (`posts.index`, `posts.store`, etc)
* You may want to split your views into small partials (small portions of HTML for specific fragments, such as `comments/_comment.blade.php` for displaying a specific comment, or `comments/_form.blade.php` for the comments' form). This will allow you to reuse these partials on your [Turbo Streams](#turbo-streams);
* Your model partial (such as `comments/_comment.blade.php` for a `Comment` model, for instance) may only rely on having a `$comment` instance passed to it. When broadcasting Turbo Streams in background, the package will pass the model instance using the model's basename in _camelCase_ to that partial);
* You may use the models' FQCN name on your Broadcasting channel authorization with a wildcard such as `.{id}` (`App.Models.Comment.{comment}` for a `Comment` model living in `App\\Models` - the name of the wildcard doesn't really matter)

In the [Overview section](#overview) you will see how to override most of the default behaviors, if you want to.

<a name="overview"></a>
## Overview

Once you compile your assets, you will have a couple custom HTML tags that you may annotate your Turbo Frames and Turbo Streams. This is vanilla Hotwire. It's recommended to read [Turbo's documentation](https://turbo.hotwire.dev/handbook/introduction). Once you understand how the few underlying pieces work together, the challenge will be in decomposing your UI to work as you want them to.

This package offers a couple macros, a trait for your models, and some conventions borrowed from Rails to find a partial for its respective model, but it allows you to override these conventions per model or not use the convenient bits at all.

<a name="notes-on-turbo-drive-and-turbo-frame"></a>
### Notes on Turbo Drive and Turbo Frames

Again, checkout [Turbo's documentation](https://turbo.hotwire.dev/handbook/introduction) to read mode on how Turbo Drive and Turbo Frames work. These are essentially front-end techniques and will work the same on any web application.

<a name="turbo-drive"></a>
Essentially, Turbo Drive will turn links and form submissions in AJAX requests and will replace the page with its response. That's useful when you want to navigate to another page completely, but if you want to persist certain pieces of HTML (and its state!) across visits, you can annotate them with a `data-turbo-permanent` attribute and an ID. If a matching element exists on the next Turbo visit, Turbo Drive won't touch that specific element in the DOM. Otherwise, the entire page will be replaced. This is used in Basecamp's navigation bar, for instance.

That's what Turbo Drive does.

<a name="turbo-frames"></a>
### Turbo Frames

[Turbo Frames](https://turbo.hotwire.dev/handbook/frames) allow you to decompose your UI. Any links or form submissions inside a Turbo Frame will still get hijacked by Turbo Drive, but instead of replace the entire page, it will look for a matching Turbo Frame on the response (following any possible redirect) and replace just that Turbo Frame.

This is how you can annotate Turbo Frames:

```blade
<turbo-frame id="my_frame">
    <h1>Hello, World!</h1>
    <a href="/somewhere">
        I'm a trigger. My response must have a matching Turbo Frame tag (same ID)
    </a>
</turbo-frame>
```

<a name="turbo-frame-lazy"></a>
A Turbo Frame can also lazy-load its content:

```blade
<turbo-frame id="my_frame" src="{{ route('my.page') }}">
    <p>Loading...</p>
</turbo-frame>
```

Once that Turbo Frame enters the DOM, it will trigger an AJAX request to replace the Frame with a matching Frame on the response. You can also use the `loading="lazy"` on the lazy-loaded Turbo Frame, and this will only trigger the AJAX request if that Turbo Frame is visible (on the viewport).

You may also trigger a frame visit with a link outside the frame itself, like so:

```blade
<div>
    <a href="/somewhere" data-turbo-frame="my_frame">I'm a link</a>

    <turbo-frame id="my_frame"></turbo-frame>
</div>
```

When that link is clicked (either by the user or programmatically using JavaScript!), a visit will add the link's `href` as the `src` attribute and trigger the AJAX request on the Turbo Frame, which will replace its content with a matching Turbo Frame.

So far, all vanilla Hotwire and Turbo.

<a name="dom-ids"></a>
Since Turbo Frames rely a lot on DOM IDs, the package offers a helper for generating DOM IDs for your models. You may use the `@domid` Blade directive on your Blade views like so:

```blade
<turbo-frame id="@domid($comment)">
    <!-- Content -->
</turbo-frame>
```

This will generate a `comment_123` DOM ID. You can also give it a context, such as:

```blade
<turbo-frame id="@domid($post, 'comments_count')">(99)</turbo-frame>
```

Which will generate a `comments_count_post_123` ID. This API was borrowed from Rails. You may also use the namespaced `dom_id` function outside your views, if you need to:

```php
use function Tonysm\TurboLaravel\dom_id;

dom_id($comment);
```

When a new instance of a model is passed to any of these `domid` helpers, since it doesn't have an ID, it will prefix the resource name with a `create_` prefix. So a new instance of `App\\Models\\Comment` will generate `create_comment`. This will only strip out the root namespaces of the model's FQCN (see [config/turbo-laravel.php](config/turbo-laravel.php)).

That's Turbo Frames. It allows you to update a single element. However, you may want to update multiple parts of your page at the same time. You may achieve that with [Turbo Streams](#turbo-streams).

<a name="turbo-streams"></a>
### Turbo Streams

Out of everything Turbo provides, it's Turbo Streams that can benefit most from a back-end integration.

You may use Turbo Streams to update multiple parts of your page at the same time. For instance, after a form submission to create a comment, you may want to append the comment to the comment's list and also update the comment's counter. You may achieve that with Turbo Streams.

A Turbo Stream response consists of one or more `<turbo-stream>` tags and the correct header of `Content-Type: text/vnd.turbo-stream.html`. If these are returned after a POST form submission, then Turbo will do the rest to apply your changes (instead of the default Turbo behavior).

This is what a Turbo Stream looks like:

```blade
<turbo-stream action="append" target="comments">
    <template>
        <-- ... -->
    </template>
</turbo-stream>
```

You can read more about Turbo Streams in the [Turbo Documentation](https://turbo.hotwire.dev/handbook/streams).

<a name="wants-turbo-stream"></a>
### Turbo Stream Request Macro

A form submission is annotated by Turbo with an `Accept` header that indicates you may return a Turbo Stream response. You may check if the request accepts Turbo Streams using the `wantsTurboStream` macro in the Request class. You may also auto-generate the Turbo Stream response for a model using the `turboStream` macro in the Response factory (which you can use via the `response()` helper function or the Response Facade), like so:

```php
class PostCommentsController
{
  public function store(Post $post)
  { 
    $comment = $post->comments()->create([]);
    
    if (request()->wantsTurboStream()) {
      return response()->turboStream($comment);
    }
    
    return redirect()->route('...');
  }
}
```

<a name="turbo-stream-response"></a>
### Turbo Stream Responses

The `turbosStream` macro in the ResponseFactory will generate a Turbo Stream response for the changes made to your model (it was either created, updated, or deleted). That's where the partial [naming conventions](#conventions) take place. In the comment example above, we'll look for a partial located at `resources/views/comments/_comment.blade.php`.

This follows the convention of using the *plural resource name* for the folder and *singular resource name* for the partial itself, prefixed with an underscore. Your partial will receive a variable named after your class name (without the root namespace) in _camelCase_. So, in this case, it will receive a `$comment` variable that you can use.

<a name="override-turbo-stream-partials-and-data"></a>
### Override Model's Partial Name and Partial Data

You may override the partial name convention by implementing the `hotwirePartialName` in your Comment model. You may also have more control over the data passed to the partial by implementing the `hotwirePartialData` method, like so:

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

The macro will look for a partial for your model, render it inside a Turbo Stream tag, add the Turbo Stream header to the response, and return it so Turbo can apply to the page that triggered the request.

For recently created models (created during the request), the `target` of the Turbo Stream will be the resource name of the model, which, by default, will be a plural version of the model's basename, and the action will be "append", or whatever you pass as the second param of the `turboStream` response macro.

For updated models, the `target` of the Turbo Stream tag will be the DOM ID of the model itself (using the `@domid()` helper's conventions), and the default `action` will be `replace`.

For deleted models, the `target` will also be the DOM ID of the model, but the `action` will be `remove`. No template is needed for deleted Turbo Stream messages.

In this example, a `App\\Models\\Comment` model will look for its partial inside `resources/views/comments/_comment.blade.php`. To that partial, a reference of the model itself will be passed down having the model name as name for the variable name (a _camelCase_ version of the model name without the root namespaces). So, for a `App\\Models\\Comment` model, you will have a `$comment` variable available inside the partial.

<a name="override-turbo-stream-resource-and-dom-id"></a>
### Override Model's Resource Name and DOM ID

Both the resource used in the created Turbo Stream reponse (to append the model to the resource list) and the DOM ID of the model used in replace, update, and deleted Turbo Streams may be overwritten like so:

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
<turbo-stream target="comment_123" action="replace">
  <template>
    @include('comments._comment', ['comment' => $comment])
  </template>
</turbo-stream>
```

An example for a model that was deleted:

```blade
<turbo-stream target="comment_123" action="remove"></turbo-stream>
```

<a name="turbo-stream-view"></a>
### Custom Turbo Stream View

You may use the `response()->turboStreamView()` macro to return a custom Turbo Stream view instead of relying on the `response()->turboStream()` macro. Here's an example:

```php
return response()->turboStreamView(view('comments.turbo_created_stream', [
  'comment' => $comment,
]));
```

That view is a regular Blade view that you can add your own `<turbo-stream>` tags to. One example of such a view that appends the comment to the page and updates the comments counter in the page:

```blade
<turbo-stream target="@domid($comment->post, 'comments_count')" action="update">
    <template>({{ $comment->post->comments()->count() }})</template>
</turbo-stream>

<turbo-stream target="comments" action="append">
  <template>
    @include('comments._comment', ['comment' => $comment])
  </template>
</turbo-stream>
```

The `turboStreamView` Response macro will take your view, render it and apply the correct `Content-Type` for you.

<a name="override-turbo-stream-views"></a>
### Override Turbo Stream Views

You may want to override the Turbo Stream views generated by the package (either using `response()->turboStream()` or when using the broadcasting features - more on that soon).

To override the Turbo Stream views, you can create a `turbo` folder inside the model's resource views folder and name them after the model event, like so:

| Model Event | Expected View |
|---|---|
| `created` | `{resource}/turbo/created_stream.blade.php` |
| `updated` | `{resource}/turbo/updated_stream.blade.php` |
| `deleted` | `{resource}/turbo/deleted_stream.blade.php` |

The package will use these views instead of generating Turbo Stream views. The same conventions apply there for the variables passed to the view. Try to use only the resource itself and its relationships, or override the `hotwirePartialData` method. **It's important to note that these views will be used when generating Turbo Stream responses in background**, when you're using broadcasting capabilities. More on that on the following section.

<a name="turbo-streams-and-laravel-echo"></a>
### Turbo Streams and Laravel Echo

So far, we used Turbo Streams over HTTP to update multiple parts of the page after a Turbo form submission. In addition, you may want to broadcast model changes over WebSockets to other users on the same page. Although nice, **you don't have to use WebSockets in your app if you don't have the need for it. You can rely on only returning Turbo Stream responses from your controller.**

The same Turbo Streams you use to feed the current user after a form submission can be used to update other user's viewing the same page by broadcasting them over WebSockets. This will provide an instant feedback for the user making the request compared to waiting for the background job to broadcast your changes back to you.

If you want to augment your app with WebSockets continue reading.

First, you need to make sure Laravel Echo it set up properly. It should be something like this:

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

These settings assume you're using the [Laravel WebSockets](https://github.com/beyondcode/laravel-websockets) package. Check out the [resources/js/echo.js](resources/js/echo.js) for the suggested dotenv credentials you may need to configure. You may also use [Pusher](https://pusher.com/) instead of the Laravel WebSockets package, if you don't want to host it yourself.

<a name="turbo-stream-broadcasting-with-events"></a>
### Broadcasting Turbo Streams on Model Changes

With Laravel Echo properly configured, you may now broadcast changes model changes using WebSockets by attaching a specific event to the "created", "updated", or "deleted" events on the model, like so:

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

This will generate the Turbo Stream and broadcast the changes using [Laravel's Broadcasting](https://laravel.com/docs/master/broadcasting) component.

By default, Turbo Streams will be broadcast to the Model's channel. Channels may be named using a dotted notation of the model's FQCN plus its ID for the wildcard.

To follow our `App\\Models\\Comment` example, the changes would broadcast to a channel named: `App.Models.Comment.{id}` (the name of the wildcard is not enforced, you can use whatever you want, but we'll use the model's ID as its value). You may pick only the events you want to broadcast.

<a name="turbo-stream-broadcasting-destination"></a>
### Overriding Turbo Stream Broadcasting Channels

You may control where your model's broadcasts are sent to in a couple different ways.

If you want to broadcast the changes to a related model, you can use a `$broadcastsTo` public property on your model, like so:

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

This will generate the channel name using the related Post model instead of the Comment model. So it will broadcast to a `App.Models.Post.{id}` where `{id}` would be the Post ID (again, assuming your FQCN is `App\\Models\\Post`). You may also do that with a `broadcastsTo()` method:

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

You may return a model, an array or a collection of models, or an array or a collection of broadcasting _Channels_, giving you full control on where you want the broadcasting to be sent to. The same partial conventions apply here as well (for partial and resource namings and data provided to the partial, including the `resources/views/{resource}/turbo/` folder overrides).

<a name="turbo-stream-broadcasting-using-trait"></a>
### The Broadcasts Trait for Models

You may use the `Tonysm\TurboLaravel\Models\Broadcasts` trait provided by this package to broadcast all changes of your model (created, updated, and deleted model events), like so:

```php
use Tonysm\TurboLaravel\Models\Broadcasts;

class Comment extends Model
{
  use Broadcasts;
}
```

This will apply the same conventions mentioned for the model events, and it will automatically dispatch the broadcast in background using queued jobs.

<a name="turbo-streams-listening-to-echo-events"></a>
### Listening to Turbo Stream Broadcasts

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

You may want to read the [Laravel Broadcasting](https://laravel.com/docs/8.x/broadcasting) documentation.

<a name="brodcast-to-others"></a>
### Broadcasting Turbo Streams to Other Users Only

If you want to take the "mixed" approach I mentioned earlier, you can tell Turbo Laravel to only broadcast changes _to other_ users, and feed back the current user with Turbo Stream messages using the HTTP response they triggered. To tell Turbo Laravel to broadcast only to others, add the following in the `boot` method of your `AppServiceProvider`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Tonysm\TurboLaravel\TurboFacade;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        TurboFacade::broadcastToOthers();
    }
}
```

You may also pass a callback to it, so any Turbo Stream broadcast triggered inside the scope of that callback will be sent only _to other_ users, resuming back to the default behavior of broadcasting to all users after that. The callback version looks like this:

```php
TurboFacade::broadcastToOthers(function () {
    // ...
});
```

That's Turbo Stream over WebSockets using Laravel Echo and Queued Jobs.

<a name="validation-responses"></a>
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

Hotwire also has a [mobile side](https://turbo.hotwire.dev/handbook/native), and the package provides some goodies on this front too.

Turbo Visits made by a Turbo Native client will send a custom `User-Agent` header. So we added another Blade helper you may use to toggle fragments or assets (such as mobile specific stylesheets) on and off depending on whether your page is being rendered for a Native app or a Web app:

```blade
@turbonative
    <h1>Hello, Mobile Users!</h1>
@endturbonative
```

You may also check if the request was made from a Turbo Native visit using the TurboFacade, like so:

```php
if (\Tonysm\TurboLaravel\TurboFacade::isTurboNativeVisit()) {
    // Do something for mobile specific requests.
}
```

<a name="testing-helpers"></a>
### Testing Helpers

There is a [companion package](https://github.com/tonysm/turbo-laravel-test-helpers) that you may use as a dev dependency on your application to help with testing your apps using Turbo Laravel. First, install the package:

```bash
composer require tonysm/turbo-laravel-test-helpers --dev
```

And then you will be able to test your application like:

``` php
use Tonysm\TurboLaravelTestHelpers\Testing\InteractsWithTurbo;

class ExampleTest extends TestCase
{
    use InteractsWithTurbo;
    
    /** @test */
    public function turbo_stream_test()
    {
        $response = $this->turbo()->post('my-route');

        $response->assertTurboStream();

        $response->assertHasTurboStream($target = 'users', $action = 'append');

        $response->assertDoesntHaveTurboStream($target = 'empty_users', $action = 'remove');
    }

    /** @test */
    public function turbo_native_shows()
    {
        $response = $this->turboNative()->get('my-route');

        $response->assertSee('Only rendered in Turbo Native');
    }
}
```

Check out the [package repository](https://github.com/tonysm/turbo-laravel-test-helpers) if you want to know more about it.

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
