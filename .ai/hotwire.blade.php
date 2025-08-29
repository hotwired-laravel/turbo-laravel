## Hotwire/Turbo Core Principles
- For standard application development, use Hotwire (Turbo + Stimulus)
- Send HTML over the wire instead of JSON. Keep complexity on the server side.
- Use Turbo Drive for smooth page transitions without full page reloads.
- Decompose pages with Turbo Frames for independent sections that update separately.
- Use Turbo Streams for real-time updates and dynamic content changes.
- Leverage Stimulus for progressive JavaScript enhancement when Turbo isn't sufficient.
- Keep Stimulus controllers focused and simple
- Prefer server-side template rendering and state management over client-side frameworks.
- Enable "morphing" for seamless page updates that preserve scroll position and focus.
- Use data attributes for JavaScript hooks
- For more complex JavaScript dependencies, use Importmap Laravel

## Turbo Setup & Base Helpers
@verbatim
- Turbo automatically handles page navigation, form submissions, and CSRF protection
- Enable morphing in your layout (preserves DOM state during page updates): `<x-turbo::refresh-method method="morph" />`
- Configure scroll behavior in your layout: `<x-turbo::refresh-scroll scroll="preserve" />`
- Enable both morphing and scroll preservation with a single component: `<x-turbo::refreshes-with method="morph" scroll="preserve" />`
- Generate unique DOM IDs from models: use function `dom_id($model, 'optional_prefix')` or Blade directive `@domid($model, 'optional_prefix')`
- Generate CSS classes from models: use function `dom_class($model, 'optional_prefix')` or Blade directive `@domclass($model, 'optional_prefix')`
@endverbatim

## Turbo Frames Best Practices
- Use frames to decompose pages into independent sections that can update without full page reloads:
@verbatim
    ```blade
    <x-turbo::frame :id="$post">
        <h3>{{ $post->title }}</h3>
        <p>{{ $post->content }}</p>
        <a href="{{ route('posts.edit', $post) }}">Edit</a>
    </x-turbo::frame>
    ```
@endverbatim
- Forms and links inside frames automatically target their containing frame (no configuration needed):
@verbatim
    ```blade
    <x-turbo::frame :id="$post">
        <form action="{{ route('posts.store') }}" method="POST">
            @csrf
            <input type="text" name="title" required>
            <button type="submit">Create Post</button>
        </form>
    </x-turbo::frame>
    ```
@endverbatim
- Override default frame targeting with `data-turbo-frame` attribute:
  - Use a frame's DOM ID to target a specific frame
  - Use `_top` to break out of frames and navigate the full page:
@verbatim
    ```blade
    <a href="{{ route('posts.show', $post) }}" data-turbo-frame="_top">View Full Post</a>
    ```
@endverbatim

## Turbo Streams for Dynamic Updates
- Return Turbo Stream responses from controllers to update specific page elements without full page reload:
@verbatim
<code-snippet name="Controller returning Turbo Streams" lang="php">
    public function store(Request $request)
    {
        $post = Post::create($request->validated());

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream()->append('posts', view('posts.partials.post', ['post' => $post])),
                turbo_stream()->update('create_post', view('posts.partials.form', ['post' => new Post()])),
            ]);
        }

        return back();
    }
</code-snippet>
@endverbatim
- Available Turbo Stream actions for manipulating DOM elements:
@verbatim
<code-snippet name="Turbo Stream actions" lang="php">
    // Append content
    turbo_stream()->append($comment, view('comments.partials.comment', [
        'comment' => $comment,
    ]));

    // Prepend content
    turbo_stream()->prepend($comment, view('comments.partials.comment', [
        'comment' => $comment,
    ]));

    // Insert before
    turbo_stream()->before($comment, view('comments.partials.comment', [
        'comment' => $comment,
    ]));

    // Insert after
    turbo_stream()->after($comment, view('comments.partials.comment', [
        'comment' => $comment,
    ]));

    // Replace content (swaps the target element)
    turbo_stream()->replace($comment, view('comments.partials.comment', [
        'comment' => $comment,
    ]));

    // Update content (keeps the target element and only updates its contents)
    turbo_stream()->update($comment, view('comments.partials.comment', [
        'comment' => $comment,
    ]));

    // Removes content
    turbo_stream()->remove($comment);
</code-snippet>
@endverbatim
- Broadcast Turbo Streams over WebSockets to push real-time updates to all connected users:
@verbatim
<code-snippet name="Broadcasting Turbo Streams" lang="php">
    // Add the trait to the model:
    use HotwiredLaravel\TurboLaravel\Models\Broadcasts;

    class Post extends Model
    {
        use Broadcasts;
    }

    // When you want to trigger the broadcasting from anywhere (including model events)...
    $post->broadcastAppend()->to('posts');
    $post->broadcastUpdate();
    $post->broadcastRemove();
</code-snippet>
@endverbatim

## Stimulus Integration
- Use Stimulus for client-side interactivity that requires JavaScript (when Turbo's server-side approach isn't sufficient):
@verbatim
<code-snippet name="Stimulus controller for form enhancements" lang="javascript">
    import { Controller } from "@hotwired/stimulus"

    export default class extends Controller {
        static targets = ["input", "counter"]

        connect() {
            this.updateCounter()
        }

        updateCounter() {
            const length = this.inputTarget.value.length
            this.counterTarget.textContent = `${length}/280`
        }
    }
</code-snippet>
@endverbatim
- Connect Stimulus controllers with proper data attributes:
@verbatim
    ```blade
    <div data-controller="character-counter">
        <textarea data-character-counter-target="input"
                  data-action="input->character-counter#updateCounter"></textarea>
        <span data-character-counter-target="counter">0/280</span>
    </div>
    ```
@endverbatim

## Form Handling & Validation
- Use Laravel's resource route naming conventions for automatic form re-rendering, if the matching route exists:
  - `*.store` action redirects to `*.create` route (shows form again with validation errors)
  - `*.update` action redirects to `*.edit` route (shows form again with validation errors)
  - `*.destroy` action redirects to `*.delete` route
- Validation errors are automatically displayed when using this convention with Turbo

## Performance & UX Enhancements
- Use `data-turbo-permanent` to preserve specific elements during Turbo navigation (prevents re-rendering):
@verbatim
    ```blade
    <div id="flash-messages" data-turbo-permanent>
        <!-- Flash messages that persist across navigation -->
    </div>
    ```
@endverbatim
- Preloading is automatically enabled on all links. You may disable it for specific links with the `data-turbo-preload` attribute (if you need to):
@verbatim
    ```blade
    <a href="{{ route('posts.show', $post) }}" data-turbo-preload="false">
        {{ $post->title }}
    </a>
    ```
@endverbatim

## Testing Hotwire/Turbo
@verbatim
<code-snippet name="Testing Turbo Stream responses" lang="php">
    public function test_creating_post_returns_turbo_stream()
    {
        $this->turbo()
            ->post(route('posts.store'), ['title' => 'Test Post'])
            ->assertTurboStream(fn (AssertableTurboStream $turboStreams) => (
                $turboStreams->has(2)
                && $turboStreams->hasTurboStream(fn ($turboStream) => (
                    $turboStream->where('target', 'flash_messages')
                                ->where('action', 'prepend')
                                ->see('Post was successfully created!')
                ))
                && $turboStreams->hasTurboStream(fn ($turboStream) => (
                    $turboStream->where('target', 'posts')
                                ->where('action', 'append')
                                ->see('Test Post')
                ))
            ));
    }
</code-snippet>
@endverbatim
@verbatim
<code-snippet name="Testing Turbo Frame responses" lang="php">
    public function test_frame_request_returns_partial_content()
    {
        $this->fromTurboFrame(dom_id($post))
            ->get(route('posts.update', $post))
            ->assertSee('<turbo-frame id="'.dom_id($post).'">', false)
            ->assertViewIs('posts.edit');
    }
</code-snippet>
@endverbatim
@verbatim
<code-snippet name="Testing broadcast streams" lang="php">
    use HotwiredLaravel\TurboLaravel\Facades\TurboStream;
    use HotwiredLaravel\TurboLaravel\Broadcasting\PendingBroadcast;

    public function test_post_creation_broadcasts_stream()
    {
        TurboStream::fake();

        $post = Post::create(['title' => 'Test Post']);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($post) {
            return $broadcast->target === 'posts'
                && $broadcast->action === 'append'
                && $broadcast->partialView === 'posts.partials.post'
                && $broadcast->partialData['post']->is($post)
                && count($broadcast->channels) === 1
                && $broadcast->channels[0]->name === sprintf('private-%s', $post->broadcastChannel());
        });
    }
</code-snippet>
@endverbatim
@verbatim
<code-snippet name="Testing Hotwire Native Resume, Recede, or Refresh" lang="php">
    use HotwiredLaravel\TurboLaravel\Facades\TurboStream;
    use HotwiredLaravel\TurboLaravel\Broadcasting\PendingBroadcast;

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
</code-snippet>
@endverbatim
