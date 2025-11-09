## Hotwire/Turbo Core Principles
- For standard application development, use Hotwire (Turbo + Stimulus)
- For most interactions, use regular links and form submits (Turbo Drive will make them fast and dynamic)
- Decompose pages with Turbo Frames for independent sections that update separately
- Use Turbo Streams for real-time updates and dynamic content changes
- Leverage Stimulus for progressive JavaScript enhancement when Turbo isn't sufficient (if Stimulus is available)
- Prefer server-side template rendering and state management over client-side frameworks and state
- Use data attributes for JavaScript hooks and CSS styling for as much as possible

## Base Helpers
@verbatim
- Turbo automatically handles page navigation, form submissions, and CSRF protection
- You may configure morphing and scroll preservation for a page (or layout) with: `<x-turbo::refreshes-with method="morph" scroll="preserve" />`
- Generate unique DOM IDs from models: use the `dom_id($model, 'optional_prefix')` global function or Blade directive `@domid($model, 'optional_prefix')`
- Generate CSS classes from models: use the `dom_class($model, 'optional_prefix')` global function or Blade directive `@domclass($model, 'optional_prefix')`
@endverbatim

## Turbo Frames Best Practices
- Use frames to decompose pages into independent sections that can update without full page reloads
- Forms and links inside frames automatically target their containing frame (no configuration needed)
- You may override the default frame target of a link or form with `[data-turbo-frame]` attribute:
  - Use a frame's DOM ID to target a specific frame
  - Use the value `_top` to break out of frames and navigate the full page
- The `[:id]` prop accepts models and automatically generates DOM IDs for them
- The `[:src]` prop accepts a URL to lazy-load from content. Optionally, you may pair it with a `[loading=lazy]` so it only loads when the element is visible in the viewport

Example:
@verbatim
    ```blade
    <x-turbo::frame :id="$post">
        <h3>{{ $post->title }}</h3>
        <p>{{ $post->content }}</p>
        <a href="{{ route('posts.edit', $post) }}" data-turbo-frame="_top">Edit</a>
        <form action="{{ route('posts.store') }}" method="POST">
            @csrf
            <input type="text" name="title" required>
            <button type="submit">Create Post</button>
        </form>
    </x-turbo::frame>
    ```
@endverbatim

## Turbo Streams for Dynamic Updates

- You may return Turbo Streams from controllers after form submissions to update specific page elements (always check if the request accepts Turbo Streams for resilience)
@verbatim
<code-snippet name="Controller returning Turbo Streams" lang="php">
    public function store(Request $request)
    {
        $post = Post::create($request->validated());

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream()->append('posts', view('posts.partials.post', ['post' => $post])),
                turbo_stream()->update('create_post', view('posts.partials.form', ['post' => new Post()])),
                // turbo_stream()->prepend('some_dom_id', view('posts.partials.post', ['post' => $post])),
                // turbo_stream()->before('some_dom_id', view('...'))
                // turbo_stream()->after('some_dom_id', view('...'))
                // turbo_stream()->replace('some_dom_id', view('...'))
                // turbo_stream()->remove('some_dom_id')
            ]);
        }

        return back();
    }
</code-snippet>
@endverbatim
- Turbo Streams can also be broadcasted using Laravel Echo for real-time updates to all users connected to a channel:
@verbatim
<code-snippet name="Listening to Broadcasts" lang="blade">
    <x-turbo::stream-from :source="$post" />
</code-snippet>

<code-snippet name="Broadcasting Turbo Streams" lang="php">
    // Ensure the channel is defined in `routes/channels.php`:
    Broadcast::channel(Post::class, function (User $user, Post $post) {
        return $user->belongsToProject($post->project);
    });

    // Add the trait to the model:
    use HotwiredLaravel\TurboLaravel\Models\Broadcasts;

    class Post extends Model
    {
        use Broadcasts;
    }

    // When you want to trigger the broadcasting from anywhere (including model events)...
    $post->broadcastUpdate();
    $post->broadcastRemove();
    $post->broadcastAppend()->to('posts');
</code-snippet>
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
