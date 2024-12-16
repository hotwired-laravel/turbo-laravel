---
layout: _layouts.v1-docs
title: Turbo Streams
description: The Turbo Streams Components and Helpers 
order: 6
---

# Turbo Streams

Out of everything Turbo provides, it's Turbo Streams that benefits the most from a tight back-end integration.

Turbo Drive will get your pages behaving like an SPA and Turbo Frames will allow you to have a finer grained control of chunks of your page instead of replacing the entire page when a form is submitted or a link is clicked.

However, sometimes you want to update _multiple_ parts of your page at the same time. For instance, after a form submission to create a comment, you may want to append the comment to the comments' list and also update the comments' count in the page. You may achieve that with Turbo Streams.

Form submissions will get annotated by Turbo with a `Accept: text/vnd.turbo-stream.html` header (besides the other normal Content Types). This is a signal to the back-end that you can return a Turbo Stream response for that form submission if you want to.

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

The `request()->wantsTurboStream()` macro added to the request class will check if the request accepts Turbo Stream and return `true` or `false` accordingly.

The `response()->turboStream()` macro may be used to generate streams, but you may also use the `turbo_stream()` helper function. From now on, the docs will be using the helper function, but you may use either one of those. Using the function, this example would be:

```php
Route::post('posts/{post}/comments', function (Post $post) {
    $comment = $post->comments()->create(/** params */);

    if (request()->wantsTurboStream()) {
        return turbo_stream($comment);
    }

    return back();
});
```

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

Most of these things were "guessed" based on the [naming conventions](/docs/{{version}}/conventions) we talked about earlier. But you can override most things, like so:

```php
return turbo_stream($comment)->target('post_comments');
```

Although it's handy to pass the model instance to the `turbo_stream()` function - which will be used to decide the default values of the Turbo Stream response based on the model's current state, sometimes you may want to build a Turbo Stream response manually:

```php
return turbo_stream()
    ->target('comments')
    ->action('append')
    ->view('comments._comment', ['comment' => $comment]);
```

There are 7 _actions_ in Turbo Streams. They are:

* `append` & `prepend`: to insert the elements in the target element at the top or at the bottom, respectively
* `before` & `after`: to insert the elements next to the target element before or after, respectively
* `replace`: will replace the existing element entirely with the contents of the _template_ tag in the Turbo Stream
* `update`: will keep the target element and only replace the contents of it with the contents of the _template_ tag in the Turbo Stream
* `remove`: will remove the element. This one doesn't need a _template_ tag. It accepts either an instance of a Model or the DOM ID of the element to be removed as a string.

You will find shorthand methods for them all:

```php
turbo_stream()->append($comment);
turbo_stream()->prepend($comment);
turbo_stream()->before($comment);
turbo_stream()->after($comment);
turbo_stream()->replace($comment);
turbo_stream()->update($comment);
turbo_stream()->remove($comment);
```

For these shorthand stream builders, you may pass an instance of an Eloquent model, which will be used to figure out things like `target`, `action`, and the `view` partial as well as the view data passed to them.

Alternativelly, you may also pass strings to the shorthand stream builders, which will be used as the target, and an optional content string, which will be rendered instead of a partial, for instance:

```php
turbo_stream()->append('statuses', __('Comment was successfully created!'));
```

The optional content parameter expects either a string, a view instance, or an instance of Laravel's `Illuminate\Support\HtmlString`, so you could do something like:

```php
turbo_stream()->append('some_dom_id', view('greetings', [
    'name' => 'Tester',
]));
```

Or more explicitly by passing an instance of the `HtmlString` as content:

```php
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

turbo_stream()->append('statuses', new HtmlString(
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
turbo_stream()
    ->before($comment)
    ->view('comments._flash_message', [
        'message' => __('Comment was created!'),
    ]);
```

Just like the other shorthand stream builders, you may also pass an option content string or `HtmlString` instance to the `before` and `after` shorthands. When doing that, you don't need to specify the view section.

```php
turbo_stream()->before($comment, __('Oh, hey!'));
```

You can read more about Turbo Streams in the [Turbo Handbook](https://turbo.hotwired.dev/handbook/streams).

The shorthand methods also return a pending Turbo Stream builder which you can chain and override everything you want before it's rendered:

```php
return turbo_stream()
    ->append($comment)
    ->view('comments._comment_card', [
        'comment' => $comment,
    ]);
```

As mentioned earlier, passing a model to the `turbo_stream()` helper will pre-fill the pending response object with some defaults based on the model's state.

It will build a `remove` Turbo Stream if the model was deleted (or if it is trashed - in case it's a Soft Deleted model), an `append` if the model was recently created (which you can override the action as the second parameter), a `replace` if the model was just updated (you can also override the action as the second parameter.) Here's how overriding would look like:

```php
return turbo_stream($comment, 'append');
```

## Target Multiple Elements

You may also [target multiple elements](https://turbo.hotwired.dev/reference/streams#targeting-multiple-elements) using CSS classes with the `xAll` methods:

```php
turbo_stream()->appendAll('.comment', 'Some content');
turbo_stream()->prependAll('.comment', 'Some content');
turbo_stream()->updateAll('.comment', 'Some content');
turbo_stream()->removeAll('.comment');
turbo_stream()->beforeAll('.comment', 'Some content');
turbo_stream()->afterAll('.comment', 'Some content');
```

With the exception of the `removeAll` method, the `xAll` methods accept as the second parameter a string of inline content, an instance of a View (which may be created using the `view()` function provided by Laravel), or an instance of the `HtmlSafe` class.

When creating Turbo Streams using the builders, you may also specify the CSS class using the `targets()` (plural) method instead of the `target()` (singular) version:

```php
return turbo_stream()
    ->targets('.comment')
    ->action('append')
    ->view('comments._comment', ['comment' => $comment]);
```

## Turbo Stream Macros

The `turbo_stream()` function returns an instance of `PendingTurboStreamResponse`, which is _macroable_. This means you can create your custom DSL for streams. Let's say you always return flash messages from your controllers like so:

```php
class ChirpsController extends Controller
{
    public function destroy(Request $request, Chirp $chirp)
    {
        $this->authorize('delete', $chirp);

        $chirp->delete();

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->append('notifications', view('layouts.notification', [
                    'message' => __('Chirp deleted.'),
                ])),
            ]);
        }

        // ...
    }
}
```

Chances are you're gonna return flash messages from all your controllers, so you could create a custom macro like so:

```php
class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        PendingTurboStreamResponse::macro('flash', function (string $message) {
            return $this->append('notifications', view('layouts.notification', [
                'message' => $message,
            ]));
        });
    }
}
```

You could then rewrite that controller like so:

```php
class ChirpsController extends Controller
{
    public function destroy(Request $request, Chirp $chirp)
    {
        $this->authorize('delete', $chirp);

        $chirp->delete();

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->append('notifications', view('layouts.notification', [
                    'message' => __('Chirp deleted.'),
                ])),
                turbo_stream()->flash(__('Chirp deleted.')), // [tl! remove:-3,3 add]
            ]);
        }

        // ...
    }
}
```

## Turbo Streams Combo

You may combine multiple Turbo Streams in a single response like so:

```php
return turbo_stream([
    turbo_stream()
        ->append($comment)
        ->target(dom_id($comment->post, 'comments')),
    turbo_stream()
        ->update(dom_id($comment->post, 'comments_count'), view('posts._comments_count', ['post' => $comment->post])),
]);
```

Although this is a valid option, it might feel like too much work for a controller. If that's the case, use [Custom Turbo Stream Views](#custom-turbo-stream-views).

## Custom Turbo Stream Views

Although combining Turbo Streams in a single response right there in the controller is a valid option, it may feel like too much work for a controller. If that's the case, you may want to extract the Turbo Streams to a Blade view and respond with that instead:

```php
return response()->turboStreamView('comments.turbo.created_stream', [
    'comment' => $comment,
]);
```

Similar to the `Response::turboStream()` macro and the `turbo_stream()` helper function, you may prefer using the helper function `turbo_stream_view()`:

```php
return turbo_stream_view('comments.turbo.created_stream', [
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

Similar to the `<x-turbo-frame>` Blade component, there's also a `<x-turbo-stream>` Blade component that can simplify things a bit. It has the same convention of figureing out the DOM ID when you're passing a model instance or an array as the `<x-turbo-frame>` component applied to the `target` attribute. When using the component version, there's also no need to specify the template wrapper for the Turbo Stream tag, as that will be added by the component itself. So, the same example would look something like this:

```blade
@include('layouts.turbo.flash_stream')

<x-turbo-stream :target="[$comment->post, 'comments']" action="append">
    @include('comments._comment', ['comment' => $comment])
</x-turbo-stream>
```

I hope you can see how powerful this can be to reusing views.

## Custom Actions

When you're using the Blade component, you can use Turbo's custom actions:

```blade
<x-turbo-stream action="console_log" value="Hello World" />
```

As you can see, when using custom actions, the `<template></template>` is also optional. To implement custom actions in the front-end, you'd need something like this:

```js
import * as Turbo from '@hotwired/turbo';

Turbo.StreamActions.console_log = function () {
    console.log(this.getAttribute("value"))
}
```

Custom actions are only supported from Blade views. You cannot return those from controllers using the Pending Streams Builder, for instance.

[Continue to Broadcasting...](/docs/{{version}}/broadcasting)
