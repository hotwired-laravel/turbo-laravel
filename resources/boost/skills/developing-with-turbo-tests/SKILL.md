---
name: developing-with-turbo-tests
description: >-
  Tests Turbo Laravel features in PHPUnit or Pest. Activates when using the InteractsWithTurbo trait; simulating requests with
  $this->turbo(), $this->fromTurboFrame(), or $this->hotwireNative(); asserting responses with assertTurboStream(),
  assertNotTurboStream(), assertRedirectRecede(), assertRedirectResume(), or assertRedirectRefresh(); faking broadcasts
  with TurboStream::fake(), assertBroadcasted(), assertNothingWasBroadcasted(), or assertBroadcastedTimes(); writing
  feature tests for Turbo Stream responses; or when the user mentions testing Turbo, testing broadcasts, or Turbo test
  assertions.
---

# Testing Turbo Laravel

Turbo Laravel provides the `InteractsWithTurbo` trait and several test response macros for asserting Turbo-specific behavior in feature tests.

## Setup

Add the `InteractsWithTurbo` trait to your test class (or base `TestCase`):

@verbatim

<code-snippet name="Setup" lang="php">
use HotwiredLaravel\TurboLaravel\Testing\InteractsWithTurbo;

class PostTest extends TestCase
{
    use InteractsWithTurbo;
}
</code-snippet>

@endverbatim

**Note:** The `turbo-laravel.queue` config is automatically set to `false` during testing so broadcasts are processed synchronously.

## Simulating Turbo Requests

### Turbo Stream Visits

Use `$this->turbo()` to simulate a request that accepts Turbo Stream responses (sets the appropriate `Accept` header):

@verbatim

<code-snippet name="Turbo visits" lang="php">
$this->turbo()->post(route('posts.store'), ['title' => 'Test'])
    ->assertTurboStream();

$this->turbo()->put(route('posts.update', $post), ['title' => 'Updated'])
    ->assertTurboStream();

$this->turbo()->delete(route('posts.destroy', $post))
    ->assertTurboStream();
</code-snippet>

@endverbatim

### Turbo Frame Requests

Use `$this->fromTurboFrame()` to simulate a request from a specific Turbo Frame (sets the `Turbo-Frame` header):

@verbatim

<code-snippet name="Frame requests" lang="php">
$this->fromTurboFrame(dom_id($post))
    ->get(route('posts.edit', $post))
    ->assertSee('<turbo-frame id="' . dom_id($post) . '">', false);

$this->fromTurboFrame(dom_id($post, 'create_comment'))
    ->post(route('posts.comments.store', $post), ['content' => 'Hello'])
    ->assertOk();
</code-snippet>

@endverbatim

### Hotwire Native Requests

Use `$this->hotwireNative()` to simulate a request from a Hotwire Native mobile client:

@verbatim

<code-snippet name="Hotwire Native" lang="php">
$this->hotwireNative()->post(route('comments.store'), ['content' => 'Hello'])
    ->assertRedirectRecede(['status' => __('Comment created.')]);
</code-snippet>

@endverbatim

## Asserting Turbo Stream Responses

### `assertTurboStream()`

Assert the response is a Turbo Stream. Optionally pass a callback to inspect individual streams:

@verbatim

<code-snippet name="assertTurboStream" lang="php">
// Simple assertion that the response is a Turbo Stream
$this->turbo()->post(route('posts.store'), ['title' => 'Test'])
    ->assertTurboStream();

// With callback for detailed assertions
$this->turbo()->post(route('posts.store'), ['title' => 'Test'])
    ->assertTurboStream(fn ($streams) => $streams
        ->has(2) // Assert exactly 2 stream elements
        ->hasTurboStream(fn ($s) => $s
            ->where('target', 'posts')
            ->where('action', 'append')
            ->see('Test')
        )
        ->hasTurboStream(fn ($s) => $s
            ->where('target', 'post_count')
            ->where('action', 'update')
        )
    );
</code-snippet>

@endverbatim

### `assertNotTurboStream()`

Assert the response is NOT a Turbo Stream:

@verbatim

<code-snippet name="assertNotTurboStream" lang="php">
$this->get(route('posts.index'))
    ->assertNotTurboStream();
</code-snippet>

@endverbatim

## Asserting Hotwire Native Redirects

For Hotwire Native clients, assert specific redirect path behaviors with optional flash data:

@verbatim

<code-snippet name="Native redirects" lang="php">
// Assert a "recede" redirect (go back in the native navigation stack)
$this->hotwireNative()->post(route('comments.store'), ['content' => 'Hello'])
    ->assertRedirectRecede(['status' => __('Comment created.')]);

// Assert a "resume" redirect (stay on the current screen)
$this->hotwireNative()->put(route('settings.update'), ['name' => 'New'])
    ->assertRedirectResume(['status' => __('Settings updated.')]);

// Assert a "refresh" redirect (reload the current screen)
$this->hotwireNative()->post(route('posts.store'), ['title' => 'Test'])
    ->assertRedirectRefresh(['status' => __('Post created.')]);
</code-snippet>

@endverbatim

## Testing Broadcasts

### Faking Broadcasts

Use `TurboStream::fake()` to capture broadcasts without actually sending them:

@verbatim

<code-snippet name="Fake broadcasts" lang="php">
use HotwiredLaravel\TurboLaravel\Facades\TurboStream;

public function test_creating_post_broadcasts()
{
    TurboStream::fake();

    $post = Post::create(['title' => 'Test']);

    TurboStream::assertBroadcasted(fn ($broadcast) =>
        $broadcast->target === 'posts' && $broadcast->action === 'append'
    );
}
</code-snippet>

@endverbatim

### `assertNothingWasBroadcasted()`

Assert no broadcasts were sent:

@verbatim

<code-snippet name="Nothing broadcasted" lang="php">
TurboStream::fake();

// ... perform actions that should NOT broadcast ...

TurboStream::assertNothingWasBroadcasted();
</code-snippet>

@endverbatim

### `assertBroadcastedTimes()`

Assert a broadcast matching a condition was sent a specific number of times:

@verbatim

<code-snippet name="Broadcast times" lang="php">
TurboStream::fake();

Post::create(['title' => 'First']);
Post::create(['title' => 'Second']);

TurboStream::assertBroadcastedTimes(
    fn ($broadcast) => $broadcast->action === 'append',
    times: 2,
);
</code-snippet>

@endverbatim
