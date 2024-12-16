---
layout: _layouts.docs
title: Testing
description: Testing Helpers
order: 12
---

# Testing

Testing a Hotwired app is like testing a regular Laravel app. However, Turbo Laravel comes with a set of helpers that may be used to ease testing some aspects that are specific to Turbo:

1. **Turbo HTTP Request Helpers**. When you may want to mimic a Turbo visit, or a Hotwire Native visit, or a request coming from a Turbo Frame.
1. **Turbo Streams on HTTP Responses.** When you may want to test the Turbo Streams returned from HTTP requests.
1. **Turbo Stream Broadcasts.** When you're either using the broadcast methods on your models using the `Broadcasts` trait, or when you're using [Handmade Turbo Stream Broadcasts](https://turbo-laravel.com/docs/2.x/broadcasting#content-handmade-broadcasts).

Let's dig into those aspects and how you may test them.

## Turbo HTTP Request Helpers

To enhance your testing capabilities when using Turbo, Turbo Laravel adds a few macros to the `TestResponse` that Laravel uses under the hood. It also ships with a `InteractsWithTurbo` trait that adds Turbo-specific testing helper methods. The goal is to allow mimicking a request and inspecting the response in a very Laravel way.

### Acting as Turbo Visits

Turbo visits are marked with a `Accept: text/vnd.turbo-stream.html, ...` header, which you may want to respond diferently (maybe returning a Turbo Streams document instead of plain HTML). To be able to make request adding that header, you may add the `InteractsWithTurbo` trait to your current test class (or to the base `TestCase`). Then, you may use the `$this->turbo()` method before issuing a request:

```php
use HotwiredLaravel\TurboLaravel\Testing\InteractsWithTurbo;

class CreateCommentsTest extends TestCase
{
    use InteractsWithTurbo;

    /** @test */
    public function creates_comments()
    {
        $post = Post::factory()->create();

        $this->assertCount(0, $post->comments);

        $this->turbo()->post(route('posts.comments.store', $post), [
            'content' => 'Hello World',
        ])->assertOk();

        $this->assertCount(1, $post->refresh()->comments);
        $this->assertEquals('Hello World', $post->comments->first()->content);
    }
}
```

When using this method, calls to `request()->wantsTurboStream()` will return `true`.

## Acting as Turbo Frame Requests

You may want to handle requests a bit differently based on whether they came from a request triggered inside a Turbo Frame or not. To mimic a request coming from a Turbo Frame, you may use the `fromTurboFrame()` helper from the `InteractsWithTurbo` trait:

```php
use HotwiredLaravel\TurboLaravel\Testing\InteractsWithTurbo;

class CreateCommentsTest extends TestCase
{
    use InteractsWithTurbo;

    /** @test */
    public function create_comment()
    {
        $article = Article::factory()->create();

        $this->fromTurboFrame(dom_id($article, 'create_comment'))
            ->post(route('articles.comments.store', $article), [...])
            ->assertRedirect();
    }
}
```

### Acting as Hotwire Native

Additionally, when you're building a Hotwire Native mobile app, you may want to issue a request pretending to be sent from a Hotwire Native client. That's done by setting the `User-Agent` header to something that mentions the word `Hotwire Native`. The `InteractsWithTurbo` trait also has a `$this->hotwireNative()` method you may use that automatically sets the header correctly:

```php
use HotwiredLaravel\TurboLaravel\Testing\InteractsWithTurbo;

class CreateCommentsTest extends TestCase
{
    use InteractsWithTurbo;

    /** @test */
    public function creating_comments_from_native_recedes()
    {
        $post = Post::factory()->create();

        $this->assertCount(0, $post->comments);

        $this->hotwireNative()->post(route('posts.comments.store', $post), [
            'content' => 'Hello World',
        ])->assertOk();

        $this->assertCount(1, $post->refresh()->comments);
        $this->assertEquals('Hello World', $post->comments->first()->content);
    }
}
```

When using this method, calls to `request()->wasFromHotwireNative()` will return `true`. Additionally, the `@hotwirenative` and `@unlesshotwirenative` Blade directives will render as expected.

A few other macros were added to the `TestResponse` class to make it easier to assert based on the `recede`, `resume`, and `refresh` redirects using the specific assert methods:

| Method | Descrition |
|---|---|
| `assertRedirectRecede(array $with = [])` | Asserts that a redirect was returned to the `/recede_historical_location` route. |
| `assertRedirectResume(array $with = [])` | Asserts that a redirect was returned to the `/resume_historical_location` route. |
| `assertRedirectRefresh(array $with = [])` | Asserts that a redirect was returned to the `/refresh_historical_location` route. |

The `$with` argument will ensure that not only the route is correct, but also any flashed message will be included in the query string:

```php
use HotwiredLaravel\TurboLaravel\Testing\InteractsWithTurbo;

class CreateCommentsTest extends TestCase
{
    use InteractsWithTurbo;

    /** @test */
    public function creating_comments_from_native_recedes()
    {
        $post = Post::factory()->create();

        $this->assertCount(0, $post->comments);

        $this->hotwireNative()->post(route('posts.comments.store', $post), [
            'content' => 'Hello World',
        ])->assertRedirectRecede(['status' => __('Comment created.')]);

        $this->assertCount(1, $post->refresh()->comments);
        $this->assertEquals('Hello World', $post->comments->first()->content);
    }
}
```

## Asserting Turbo Stream HTTP Responses

You may test if you got a Turbo Stream response by using the `assertTurboStream()` response helper macro. Similarly, you may assert that your response was _not_ a Turbo Stream response by using the `assertNotTurboStream()` response helper macro:

```php
use HotwiredLaravel\TurboLaravel\Testing\InteractsWithTurbo;

class CreateTodosTest extends TestCase
{
    use InteractsWithTurbo;

    /** @test */
    public function creating_todo_from_turbo_request_returns_turbo_stream_response()
    {
        $this->turbo()->post(route('todos.store'), [
            'content' => 'Test the app',
        ])->assertTurboStream();
    }

    /** @test */
    public function creating_todo_from_regular_request_does_not_return_turbo_stream_response()
    {
        // Notice we're not chaining the `$this->turbo()` method here.
        $this->post(route('todos.store'), [
            'content' => 'Test the app',
        ])->assertNotTurboStream();
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
            return turbo_stream($todo);
        }

        return redirect()->route('todos.index');
    }
}
```

## Fluent Turbo Stream Assertions

The `assertTurboStream()` macro accepts a callback which allows you to assert specific details about your returned Turbo Streams. The callback takes an instance of the `AssertableTurboStream` class, which has some matching methods to help you building your specific assertion. In the following example, we're asserting that 2 Turbo Streams were returned, as well as their targets, actions, and even HTML content:

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

## Testing Turbo Stream Broadcasts

You may assert that Turbo Stream broadcasts were sent from any mechanism provided by Turbo Laravel by using the `TurboStream::fake()` abstraction. This allows you to capture any kind of Turbo Stream broadcasting that happens inside your application and assert on them:

```php
use App\Models\Todo;
use HotwiredLaravel\TurboLaravel\Facades\TurboStream;
use HotwiredLaravel\TurboLaravel\Broadcasting\PendingBroadcast;

class CreatesCommentsTest extends TestCase
{
    /** @test */
    public function content_is_required()
    {
        TurboStream::fake();

        $todo = Todo::factory()->create();

        $this->turbo()->post(route('todos.comments.store', $todo), [
            'content' => null,
        ])->assertInvalid(['content']);

        TurboStream::assertNothingWasBroadcasted();
    }

    /** @test */
    public function creates_comments()
    {
        TurboStream::fake();

        $todo = Todo::factory()->create();

        $this->turbo()->post(route('todos.comments.store', $todo), [
            'content' => 'Hey, this is really nice!',
        ])->assertTurboStream();

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($todo) {
            return $broadcast->target === 'comments'
                && $broadcast->action === 'append'
                && $broadcast->partialView === 'comments.partials.comment'
                && $broadcast->partialData['comment']->is($todo->comments->first())
                && count($broadcast->channels) === 1
                && $broadcast->channels[0]->name === sprintf('private-%s', $todo->broadcastChannel());
        });
    }
}
```

*Note: If you're using the automatic model changes broadcasting, make sure your `turbo-laravel.queue` config key is set to false, otherwise actions may not be dispatched during test because the model observer only fires them after the transaction is commited, which never happens in tests since they run inside a transaction.*

[Continue to Known Issues...](/docs/{{version}}/known-issues)
