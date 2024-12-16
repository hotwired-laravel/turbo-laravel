---
layout: _layouts.v1-docs
title: Broadcasting
description: Broadcasting Turbo Streams
order: 7
---

# Broadcasting Turbo Streams Over WebSockets With Laravel Echo

So far, we have used Turbo Streams over HTTP to handle the case of updating multiple parts of the page for a single user after a form submission. In addition to that, you may want to broadcast model changes over WebSockets to all users that are viewing the same page. Although nice, **you don't have to use WebSockets if you don't have the need for it. You may still benefit from Turbo Streams over HTTP.**

We can broadcast to all users over WebSockets those exact same Turbo Stream tags we are returning to a user after a form submission. That makes use of Laravel Echo and Laravel's Broadcasting component.

You may still feed the user making the changes with Turbo Streams over HTTP and broadcast the changes to other users over WebSockets. This way, the user making the change will have an instant feedback compared to having to wait for a background worker to pick up the job and send it to them over WebSockets.

## Configuration

Broadcasting Turbo Streams relies heavily on Laravel's [Broadcasting component](https://laravel.com/docs/broadcasting). This means you need to configure Laravel Echo in the frontend and either use Pusher or any other open-source replacement you want to. If you're not using Pusher, we recommend [Soketi](https://docs.soketi.app/) since it's easy to setup.

## Listening to Broadcasts

You may listen to a Turbo Stream broadcasts on your pages by adding the custom HTML tag `<turbo-echo-stream-source>` that is published to your application's assets (see [here](https://github.com/tonysm/turbo-laravel/blob/main/stubs/resources/js/elements/turbo-echo-stream-tag.js)). You need to pass the channel you want to listen to broadcasts on using the `channel` attribute of this element, like so.

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

You may want to read the [Laravel Broadcasting](https://laravel.com/docs/broadcasting) documentation.

## Broadcasting Model Changes

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
        return turbo_stream($comment);
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

## Broadcasting Turbo Streams to Other Users Only

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

## Handmade Broadcasts

You may want to broadcast something that does not depend on a model. You may do so using the `Tonysm\TurboLaravel\Facades\TurboStream` Facade (if you're not into Facades, type-hinting the `Tonysm\TurboLaravel\Broadcasting\Factory` class should also work):

```php
TurboStream::broadcastAppend(
    content: __('Hello World'),
    target: 'notifications',
    channel: 'general',
);
```

Model broadcasts use this same abstraction under the hood, so you have similar methods available:

```php
TurboStream::broadcastAppend();
TurboStream::broadcastPrepend();
TurboStream::broadcastBefore();
TurboStream::broadcastAfter();
TurboStream::broadcastUpdate();
TurboStream::broadcastReplace();
TurboStream::broadcastRemove();
```

All of these methods, except the `broadcastRemove()` one, accept a `$content` parameter that may be a View instance, an instance of the `HtmlString` class, or a simple string:

```php
// Passing a view instance as content...
TurboStream::broadcastAppend(
    content: view('layouts.notification', ['message' => 'Hello World']),
    target: 'notifications',
    channel: 'general',
);

// Passing an instance of the HtmlString class (won't be escaped by Blade)...
TurboStream::broadcastAppend(
    content: new HtmlString('Hello World'),
    target: 'notifications',
    channel: 'general',
);

// Passing a simple string (will be escaped by Blade)...
TurboStream::broadcastAppend(
    content: 'Hello World',
    target: 'notifications',
    channel: 'general',
);
```

You may also dynamically change the Turbo Stream setting by chaining on the return of that method:

```php
TurboStream::broadcastAppend('Hello World')
    ->target('notifications')
    ->to('general');
```

As for the channel, you may pass a string that will be interpreted as a public channel name, an Eloquent model which will expect a private channel using that model's broadcasting channel convention, or instances of the `Illuminate\Broadcasting\Channel` class.

You may want to specify private or presence string channels, which you may do like so:

```php
TurboStream::broadcastAppend('Hello World')
    ->target('notifications')
    ->toPrivateChannel('user.123');

TurboStream::broadcastAppend('Hello World')
    ->target('notifications')
    ->toPresenceChannel('user.123');
```

Alternatively to broadcasting any of the 7 default broadcasting actions, you may want to broadcast custom Turbo Stream actions, which you can do by using the `broadcastAction()` method directly (which is the same method used by the other default ones):

```php
TurboStream::broadcastAction('scroll_to', target: 'todo_123');
```

## Handmade Broadcasting Using The `turbo_stream()` Response Builder

Alternatively to use the `TurboStream` Facade (or Factory type-hint), you may also broadcast directly from the `turbo_stream()` function response builder:

```php
turbo_stream()
    ->append('notifications', 'Hello World')
    ->broadcastTo('general');
```

This will tap on the `PendingTurboStreamResponse` and create a `PendingBroadcast` from the Turbo Stream you configured. It's important to note that this will return the same `PendingTurboStreamResponse`, not the `PendingBroadcast`. If you want to configure the `PendingBroadcast` that will be generated, you may pass a `Closure` as the second parameter:

```php
turbo_stream()
    ->append('notifications', 'Hello World')
    ->broadcastTo('general', fn ($broadcast) => $broadcast->toOthers());
```

You may pass a string, an Eloquent model, or an instance of the `Illuminate\Broadcasting\Channel` class as the channel:

```php
turbo_stream($comment)
    ->broadcastTo($comment->post, fn ($broadcast) => $broadcast->toOthers());
```

Similarly to using the Facade, you may also want to broadcast to private or presence string channels like so:

```php
// To private channels...
turbo_stream()
    ->append('notifications', 'Hello World')
    ->broadcastToPrivateChannel('user.123', fn ($broadcast) => $broadcast->toOthers())

// To presence channels...
turbo_stream()
    ->append('notifications', 'Hello World')
    ->broadcastToPresenceChannel('chat.123', fn ($broadcast) => $broadcast->toOthers());
```

[Continue to Livewire Integration...](/docs/{{version}}/livewire)
