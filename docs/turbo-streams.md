---
layout: _layouts.docs
title: Turbo Streams
description: The Turbo Streams Components and Helpers 
order: 7
---

# Turbo Streams

Out of everything Turbo provides, it's Turbo Streams that benefits the most from a tight backend integration.

Turbo Laravel offers helper functions, Blade Components, and [Model traits](/docs/{{version}}/broadcasting) to generate Turbo Streams. Turbo will add a new `Content-Type` to the HTTP Accept header (`Accept: text/vnd.turbo-stream.html, ...`) on Form submissions. This is a signal to the backend that we can return a Turbo Stream response for that form submission instead of an HTML document, if we want to.

Here's an example of a route handler detecting and returning a Turbo Stream response to a form submission:

```php
Route::post('posts/{post}/comments', function (Post $post) {
    $comment = $post->comments()->create(/** params */);

    if (request()->wantsTurboStream()) {
        return turbo_stream($comment);
    }

    return back();
});
```

The `request()->wantsTurboStream()` macro added to the request class will check if the request accepts Turbo Stream and return `true` or `false` accordingly.

The `turbo_stream()` helper function may be used to generate streams, but you may also use the `response()->turboStream()` macro as well. In the docs, we'll only use the helper function, but you may use either one of those.

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

Most of these things were "guessed" based on the [conventions](/docs/{{version}}/conventions) we talked about earlier. But you can override most things, like so:

```php
turbo_stream($comment)->target('post_comments');
```

This would render the following Turbo Stream:

```html
<turbo-stream action="append" target="post_comments">
    <template>
        <div id="comment_123">
            <p>Hello, World</p>
        </div>
    </template>
</turbo-stream>
```

Although it's handy to pass a model instance to the `turbo_stream()` function - which will be used to decide the default values of the Turbo Stream response based on the model's current state, sometimes you may want to build a Turbo Stream response manually:

```php
turbo_stream()
    ->target('comments')
    ->action('append')
    ->view('comments.partials.comment', ['comment' => $comment]);
```

There are also shorthand methods which may be used as well:

```php
turbo_stream()->append($comment);
turbo_stream()->prepend($comment);
turbo_stream()->before($comment);
turbo_stream()->after($comment);
turbo_stream()->replace($comment);
turbo_stream()->update($comment);
turbo_stream()->remove($comment);
turbo_stream()->refresh();
```

You may pass an instance of an Eloquent model to all these shorthand methods, except the `refresh` one, which will be used to figure things out like `target`, the `view`, and will also pass that model instance to the view.

For a model `App\Models\Comment`, the [convention] says that the view is located at `resources/views/comments/_comment.blade.php`. Based on the model's class basename, it will figure out the name of the variable that the view should depend on, which would be `$comment` in this case, so it would pass the model instance down to the view automatically. For that reason, when using the convention (which is optional), the model view must only depend on the model instance to be available (no globals or other locals with no defaults).

Alternativelly, you may also pass strings to the shorthand stream builders, which will be used as the target, and an optional content string, which will be rendered instead of a partial, for instance:

```php
turbo_stream()->append('statuses', __('Comment created!'));
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
    ->view('comments.partials.flash_message', [
        'message' => __('Comment created!'),
    ]);
```

Just like the other shorthand stream builders, you may also pass an option content string or `HtmlString` instance to the `before` and `after` shorthands. When doing that, you don't need to specify the view section.

```php
turbo_stream()->before($comment, __('Oh, hey!'));
```

You can read more about Turbo Streams in the [Turbo Handbook](https://turbo.hotwired.dev/handbook/streams).

As mentioned earlier, passing a model to the `turbo_stream()` helper (or the shorthand Turbo Stream builders) will pre-fill the pending response object with some defaults based on the model's state.

It will build a `remove` Turbo Stream if the model was just deleted (or if it was trashed - in case it's a Soft Deleted model), an `append` if the model was recently created (which you can override the action as the second parameter), a `replace` if the model was just updated (you can also change it to `update` using the second parameter.) Here's how overriding would look like:

```php
return turbo_stream($comment, 'append');
```

## Turbo Streams & Morph

Both the `update` and `replace` Turbo Stream actions can specify a `[method="morph"]` attribute, so the action will use DOM morphing instead of the default renderer.

To generate a Turbo Stream with the `[method="morph"]` attribute, chain the `morph()` method:

```php
turbo_stream()->replace(dom_id($post, 'comments'), view('comments.partials.comment', [
    'comment' => $comment,
]))->morph();
```

This would generate the following Turbo Stream HTML:

```html
<turbo-stream action="replace" target="comments_post_123" method="morph">
    <template>...</template>
</turbo-stream>
```

And here's the `update` action version:

```php
turbo_stream()->update(dom_id($post, 'comments'), view('comments.partials.comment', [
    'comment' => $comment,
]))->morph();
```

This would generate the following Turbo Stream HTML:

```html
<turbo-stream action="update" target="comments_post_123" method="morph">
    <template>...</template>
</turbo-stream>
```

## Target Multiple Elements

Turbo Stream elements can either have a `target` with a DOM ID or a `targets` attribute with a CSS selector to [match multiple elements](https://turbo.hotwired.dev/reference/streams#targeting-multiple-elements). You may use the `xAll` shorthand methods to set the `targets` attribute instead of `target`:

```php
turbo_stream()->appendAll('.comment', 'Some content');
turbo_stream()->prependAll('.comment', 'Some content');
turbo_stream()->updateAll('.comment', 'Some content');
turbo_stream()->replaceAll('.comment', 'Some content');
turbo_stream()->beforeAll('.comment', 'Some content');
turbo_stream()->afterAll('.comment', 'Some content');
turbo_stream()->removeAll('.comment');
```

With the exception of the `removeAll` method, the `xAll` methods accept astring of inline content, an instance of a View (which may be created using the `view()` function provided by Laravel), or an instance of the `HtmlSafe` class as the second parameter.

When creating Turbo Streams using the builders, you may also specify the CSS class using the `targets()` (plural) method instead of the `target()` (singular) version:

```php
turbo_stream()
    ->targets('.comment')
    ->action('append')
    ->view('comments.partials.comment', ['comment' => $comment]);
```

## Turbo Stream Macros

The `turbo_stream()` function returns an instance of `PendingTurboStreamResponse`, which is _macroable_. This means you can create your own DSL for your custom Turbo Streams. Let's say you always return flash messages from your controllers like so:

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
        ->update(dom_id($comment->post, 'comments_count'), view('posts.partials.comments_count', [
            'post' => $comment->post,
        ])),
]);
```

Although this is a valid option, it might feel like too much work for a controller. If that's the case, you may use [Custom Turbo Stream Views](#custom-turbo-stream-views).

## Custom Turbo Stream Views

Although combining Turbo Streams in a single response right there in the controller is a valid option, it may feel like too much work for a controller. If that's the case, you may want to extract the Turbo Streams to a Blade view and respond with that instead:

```php
return turbo_stream_view('comments.turbo.created_stream', [
    'comment' => $comment,
]);
```

Similar to the `turbo_stream()` helper function and the `Response::turboStream()` macro, you may prefer using the `Response::turboStreamView()` macro. It works the same way.

Here's an example of a more complex custom Turbo Stream view:

```blade
@include('layouts.turbo.flash_stream')

<turbo-stream target="@domid($comment->post, 'comments')" action="append">
    <template>
        @include('comments.partials.comment', ['comment' => $comment])
    </template>
</turbo-stream>
```

Remember, these are Blade views, so you have the full power of Blade at your hands. In this example, we're including a shared Turbo Stream partial which could append any flash messages we may have. That `layouts.turbo.flash_stream` could look like this:

```blade
@if (session()->has('status'))
<turbo-stream target="notice" action="append">
    <template>
        @include('layouts.partials.flash')
    </template>
</turbo-stream>
@endif
```

Similar to the `<x-turbo::frame>` Blade component, there's also a `<x-turbo::stream>` Blade component that can simplify things a bit. It has the same convention of figuring out the DOM ID when you're passing a model instance or an array as `target` attribute of the `<x-turbo::frame>` component. When using the component version, there's no need to specify the template wrapper for the Turbo Stream tag, as that will be added by the component itself. So, the same example would look something like this:

```blade
@include('layouts.turbo.flash_stream')

<x-turbo::stream :target="[$comment->post, 'comments']" action="append">
    @include('comments.partials.comment', ['comment' => $comment])
</x-turbo::stream>
```

I hope you can see how powerful this can be to reusing views.

## Custom Actions

You may also use the `<x-turbo::stream>` Blade component for your custom actions as well:

```blade
<x-turbo::stream action="console_log" value="Hello World" />
```

Custom actions are only supported from Blade views. You cannot return those from controllers using the Pending Streams Builder.

[Continue to Broadcasting...](/docs/{{version}}/broadcasting)
