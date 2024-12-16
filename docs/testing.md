---
layout: _layouts.v1-docs
title: Testing
description: Testing Helpers
order: 12
---

# Testing

There are two aspects of your application using Turbo Laravel that are specific this approach itself:

1. **Turbo Stream HTTP responses.** As you return Turbo Stream responses from your route handlers/controllers to be applied by Turbo itself; and
1. **Turbo Stream broadcasts.** Which is the side-effect of certain model changes, or when you call `$model->broadcastAppend()` on your models, or when you're using Handmade Turbo Stream broadcasts.

We're going to cover both of these scenarios here.

## Making Turbo & Turbo Native HTTP requests

To enhance your testing capabilities here, Turbo Laravel adds a couple of macros to the TestResponse that Laravel uses under the hood. The goal is that testing Turbo Stream responses is as convenient as testing regular HTTP responses.

To mimic Turbo requests, which means sending a request setting the correct Content-Type in the `Accept:` HTTP header, you need to use the `InteractsWithTurbo` trait to your testcase. Now you can mimic a Turbo HTTP request by using the `$this->turbo()` method before you make the HTTP call itself. You can also mimic Turbo Native specific requests by using the `$this->turboNative()` also before you make the HTTP call. The first method will add the correct Turbo Stream content type to the `Accept:` header, and the second method will add Turbo Native `User-Agent:` value.

These methods are handy when you are conditionally returning Turbo Stream responses based on the `request()->wantsTurboStream()` helper, for instance. Or when using the `@turbonative` or `@unlessturbonative` Blade directives.

## Testing Turbo Stream HTTP Responses

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

## Fluent Turbo Stream Testing

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

## Testing Turbo Stream Broadcasts

All broadcasts use the `TurboStream` Facade. You may want to fake it so you can that the broadcasts are being correctly sent:

```php
use App\Models\Todo;
use Tonysm\TurboLaravel\Facades\TurboStream;
use Tonysm\TurboLaravel\Broadcasting\PendingBroadcast;

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
                && $broadcast->partialView === 'comments._comment'
                && $broadcast->partialData['comment']->is($todo->comments->first())
                && count($broadcast->channels) === 1
                && $broadcast->channels[0]->name === sprintf('private-%s', $todo->broadcastChannel());
        });
    }
}
```

*Note: If you're using the automatic model changes broadcasting, make sure your `turbo-laravel.queue` config key is set to false, otherwise actions may not be dispatched during test because the model observer only fires them after the transaction is commited, which never happens in tests since they run inside a transaction.*

[Continue to Known Issues...](/docs/{{version}}/known-issues)
