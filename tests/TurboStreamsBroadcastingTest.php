<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Testing;

use HotwiredLaravel\TurboLaravel\Broadcasting\PendingBroadcast;
use HotwiredLaravel\TurboLaravel\Facades\TurboStream;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use HotwiredLaravel\TurboLaravel\Models\Naming\Name;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Workbench\App\Models\Comment;
use Workbench\Database\Factories\ArticleFactory;
use Workbench\Database\Factories\CommentFactory;
use Workbench\Database\Factories\MessageFactory;

class TurboStreamsBroadcastingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        TurboStream::fake();
    }

    /**
     * @testWith ["append"]
     *           ["prepend"]
     *           ["before"]
     *           ["after"]
     *           ["update"]
     *           ["replace"]
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function can_manually_broadcast_append_streams(string $action): void
    {
        $method = sprintf('broadcast%s', ucfirst($action));

        $broadcasting = TurboStream::{$method}(
            channel: 'general',
            target: 'notifications',
            content: View::make('partials._notification', [
                'message' => 'Hello World',
            ]),
        );

        $expected = <<<HTML
        <turbo-stream target="notifications" action="{$action}">
            <template><div
            class="px-4 py-2 shadow-lg opacity-90 bg-gray-900 text-white rounded-full mx-auto animate-appear-then-fade-out"
            data-controller="remover"
            data-action="animationend->remover#remove"
            data-turbo-temporary
        >Hello World</div></template>
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim((string) $broadcasting->render()));
        $this->assertCount(1, $broadcasting->channels);
        $this->assertInstanceOf(Channel::class, $broadcasting->channels[0]);
        $this->assertEquals('general', $broadcasting->channels[0]->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function manually_broadcast_remove_stream(): void
    {
        $broadcasting = TurboStream::broadcastRemove(
            channel: 'general',
            target: 'todo_123',
        );

        $expected = <<<'HTML'
        <turbo-stream target="todo_123" action="remove">
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim((string) $broadcasting->render()));
        $this->assertCount(1, $broadcasting->channels);
        $this->assertInstanceOf(Channel::class, $broadcasting->channels[0]);
        $this->assertEquals('general', $broadcasting->channels[0]->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_broadcast_to_multiple_public_channels(): void
    {
        $broadcasting = TurboStream::broadcastRemove(
            channel: ['general', 'todolist.123'],
            target: 'todo_123',
        );

        $this->assertCount(2, $broadcasting->channels);

        $this->assertInstanceOf(Channel::class, $broadcasting->channels[0]);
        $this->assertEquals('general', $broadcasting->channels[0]->name);

        $this->assertInstanceOf(Channel::class, $broadcasting->channels[1]);
        $this->assertEquals('todolist.123', $broadcasting->channels[1]->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_manually_broadcast_to_private_channels(): void
    {
        $broadcasting = TurboStream::broadcastRemove(
            target: 'todo_123',
        )->toPrivateChannel('user.123');

        $this->assertInstanceOf(PrivateChannel::class, $broadcasting->channels[0]);
        $this->assertEquals('private-user.123', $broadcasting->channels[0]->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_manually_broadcast_to_multiple_private_channels(): void
    {
        $broadcasting = TurboStream::broadcastRemove(
            target: 'todo_123',
        )->toPrivateChannel(['user.123', 'todolist.123']);

        $this->assertCount(2, $broadcasting->channels);

        $this->assertInstanceOf(PrivateChannel::class, $broadcasting->channels[0]);
        $this->assertEquals('private-user.123', $broadcasting->channels[0]->name);

        $this->assertInstanceOf(PrivateChannel::class, $broadcasting->channels[1]);
        $this->assertEquals('private-todolist.123', $broadcasting->channels[1]->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_manually_broadcast_to_presence_channels(): void
    {
        $broadcasting = TurboStream::broadcastRemove(
            target: 'todo_123',
        )->toPresenceChannel('user.123');

        $this->assertInstanceOf(PresenceChannel::class, $broadcasting->channels[0]);
        $this->assertEquals('presence-user.123', $broadcasting->channels[0]->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_manually_broadcast_to_multiple_presence_channels(): void
    {
        $broadcasting = TurboStream::broadcastRemove(
            target: 'todo_123',
        )->toPresenceChannel(['user.123', 'todolist.123']);

        $this->assertCount(2, $broadcasting->channels);

        $this->assertInstanceOf(PresenceChannel::class, $broadcasting->channels[0]);
        $this->assertEquals('presence-user.123', $broadcasting->channels[0]->name);

        $this->assertInstanceOf(PresenceChannel::class, $broadcasting->channels[1]);
        $this->assertEquals('presence-todolist.123', $broadcasting->channels[1]->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_assert_nothing_was_broadcasted(): void
    {
        TurboStream::assertNothingWasBroadcasted();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_assert_broadcasted(): void
    {
        TurboStream::broadcastRemove('todo_123');

        $called = false;

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use (&$called): bool {
            $called = true;

            return
                $broadcast->target === 'todo_123'
                && $broadcast->action === 'remove';
        });

        $this->assertTrue($called, 'The given filter callback was not called.');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_assert_broadcasted_times(): void
    {
        TurboStream::broadcastRemove('todo_123');
        TurboStream::broadcastRemove('todo_123');

        $called = 0;

        TurboStream::assertBroadcastedTimes(function (PendingBroadcast $broadcast) use (&$called): bool {
            $called++;

            return $broadcast->target === 'todo_123' && $broadcast->action === 'remove';
        }, 2);

        $this->assertEquals(2, $called, 'The given filter callback was not called.');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function broadcast_inline_content(): void
    {
        $broadcast = TurboStream::broadcastUpdate(
            channel: 'general',
            target: 'notifications',
            content: 'Hello World',
        );

        $expected = <<<'HTML'
        <turbo-stream target="notifications" action="update">
            <template>Hello World</template>
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim($broadcast->render()));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function broadcast_inline_content_escaped(): void
    {
        $broadcast = TurboStream::broadcastAppend(
            channel: 'general',
            target: 'notifications',
            content: "Hello <script>alert('World')</script>",
        );

        $expected = <<<'HTML'
        <turbo-stream target="notifications" action="append">
            <template>Hello &lt;script&gt;alert(&#039;World&#039;)&lt;/script&gt;</template>
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim($broadcast->render()));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function broadcast_inline_content_as_html_string(): void
    {
        $broadcast = TurboStream::broadcastAppend(
            channel: 'general',
            target: 'notifications',
            content: new HtmlString('<h1>Hello World</h1>'),
        );

        $expected = <<<'HTML'
        <turbo-stream target="notifications" action="append">
            <template><h1>Hello World</h1></template>
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim($broadcast->render()));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_cancel_broadcasting(): void
    {
        TurboStream::broadcastRemove('todo_123')->cancel();

        TurboStream::assertNothingWasBroadcasted();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_conditionally_cancel_broadcasting(): void
    {
        TurboStream::broadcastRemove('todo_123')->cancelIf(true);

        TurboStream::broadcastRemove('todo_123')->cancelIf(fn (): true => true);

        TurboStream::assertNothingWasBroadcasted();

        TurboStream::broadcastRemove('todo_123')->cancelIf(false);

        TurboStream::broadcastRemove('todo_123')->cancelIf(fn (): false => false);

        TurboStream::assertBroadcastedTimes(fn ($broadcast): bool => $broadcast->action === 'remove' && $broadcast->target === 'todo_123', 2);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_pass_model_without_broadcasts_trait_as_channel(): void
    {
        $message = MessageFactory::new()->create();

        TurboStream::broadcastRemove('todo_123', channel: $message);

        $called = false;

        TurboStream::assertBroadcasted(function ($broadcast) use ($message, &$called): bool {
            $called = true;

            return count($broadcast->channels) === 1
                && $broadcast->channels[0]->name === $message->broadcastChannel();
        });

        $this->assertTrue($called, 'Expected callback to be called, but it was not.');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_pass_model_with_broadcasts_trait_as_channel(): void
    {
        $article = ArticleFactory::new()->create();

        TurboStream::broadcastRemove('todo_123', channel: $article);

        $called = false;

        TurboStream::assertBroadcasted(function ($broadcast) use ($article, &$called): bool {
            $called = true;

            return count($broadcast->channels) === 1
                && $broadcast->channels[0]->name === $article->asTurboStreamBroadcastingChannel()[0]->name;
        });

        $this->assertTrue($called, 'Expected callback to be called, but it was not.');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_pass_recently_created_model_as_target(): void
    {
        $article = ArticleFactory::new()->create();

        TurboStream::broadcastRemove($article);

        $called = false;

        TurboStream::assertBroadcasted(function ($broadcast) use ($article, &$called): bool {
            $called = true;

            return $broadcast->target === Name::forModel($article)->plural;
        });

        $this->assertTrue($called, 'Expected callback to be called, but it was not.');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_pass_existing_model_as_target(): void
    {
        $article = ArticleFactory::new()->create()->fresh();

        TurboStream::broadcastAppend('Testing', $article);

        $called = false;

        TurboStream::assertBroadcasted(function ($broadcast) use ($article, &$called): bool {
            $called = true;

            return $broadcast->target === dom_id($article);
        });

        $this->assertTrue($called, 'Expected callback to be called, but it was not.');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function broadcast_custom_action(): void
    {
        $broadcast = TurboStream::broadcastAction('console_log', attributes: [
            'value' => 'Hello World',
        ]);

        $expected = <<<'HTML'
        <turbo-stream action="console_log" value="Hello World">
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim($broadcast->render()));

        // Calls the __destruct() magic method...
        unset($broadcast);

        $called = false;

        TurboStream::assertBroadcasted(function ($broadcast) use (&$called): bool {
            $called = true;

            return $broadcast->action === 'console_log' && $broadcast->attributes == [
                'value' => 'Hello World',
            ];
        });

        $this->assertTrue($called, 'Expected callback to be called, but it was not.');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function pass_attributes_via_setter_method(): void
    {
        $broadcast = TurboStream::broadcastAction('console_log')->attributes([
            'value' => 'Testing',
        ]);

        $expected = <<<'HTML'
        <turbo-stream action="console_log" value="Testing">
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim($broadcast->render()));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function override_action_and_content_via_setter_methods(): void
    {
        $broadcast = TurboStream::broadcastAction('console_log')
            ->action('update_title')
            ->content('Hello World');

        $expected = <<<'HTML'
        <turbo-stream action="update_title">
            <template>Hello World</template>
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim((string) $broadcast->render()));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function send_to_others(): void
    {
        $broadcast = TurboStream::broadcastAppend('Hello World');

        $this->assertFalse($broadcast->sendToOthers);

        $broadcast->toOthers();

        $this->assertTrue($broadcast->sendToOthers);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function broadcasts_using_the_response_builder_function(): void
    {
        $response = turbo_stream()
            ->append('notifications', 'Hello World')
            ->broadcastTo('general', fn ($broadcast) => $broadcast->toOthers());

        $this->assertInstanceOf(PendingTurboStreamResponse::class, $response);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast): true {
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('Hello World', $broadcast->inlineContent);
            $this->assertCount(1, $broadcast->channels);
            $this->assertInstanceOf(Channel::class, $broadcast->channels[0]);
            $this->assertEquals('general', $broadcast->channels[0]->name);
            $this->assertTrue($broadcast->sendToOthers);

            return true;
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function broadcast_to_private_channels_using_response_builder_function(): void
    {
        $response = turbo_stream()
            ->append('notifications', 'Hello World')
            ->broadcastToPrivateChannel('general', fn ($broadcast) => $broadcast->toOthers());

        $this->assertInstanceOf(PendingTurboStreamResponse::class, $response);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast): true {
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('Hello World', $broadcast->inlineContent);
            $this->assertCount(1, $broadcast->channels);
            $this->assertInstanceOf(PrivateChannel::class, $broadcast->channels[0]);
            $this->assertEquals('private-general', $broadcast->channels[0]->name);
            $this->assertTrue($broadcast->sendToOthers);

            return true;
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function broadcast_to_presence_channels_using_response_builder_function(): void
    {
        $response = turbo_stream()
            ->append('notifications', 'Hello World')
            ->broadcastToPresenceChannel('general', fn ($broadcast) => $broadcast->toOthers());

        $this->assertInstanceOf(PendingTurboStreamResponse::class, $response);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast): true {
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('Hello World', $broadcast->inlineContent);
            $this->assertCount(1, $broadcast->channels);
            $this->assertInstanceOf(PresenceChannel::class, $broadcast->channels[0]);
            $this->assertEquals('presence-general', $broadcast->channels[0]->name);
            $this->assertTrue($broadcast->sendToOthers);

            return true;
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function broadcast_to_model_channel_using_response_builder_function(): void
    {
        $article = ArticleFactory::new()->create()->fresh();

        $response = turbo_stream()
            ->append('notifications', 'Hello World')
            ->broadcastTo($article, fn ($broadcast) => $broadcast->toOthers());

        $this->assertInstanceOf(PendingTurboStreamResponse::class, $response);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($article): true {
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('Hello World', $broadcast->inlineContent);
            $this->assertCount(1, $broadcast->channels);
            $this->assertInstanceOf(Channel::class, $broadcast->channels[0]);
            $this->assertEquals('private-'.$article->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertTrue($broadcast->sendToOthers);

            return true;
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function broadcast_to_model_as_private_channel_using_response_builder_function(): void
    {
        $article = ArticleFactory::new()->create()->fresh();

        $response = turbo_stream()
            ->append('notifications', 'Hello World')
            ->broadcastToPrivateChannel($article, fn ($broadcast) => $broadcast->toOthers());

        $this->assertInstanceOf(PendingTurboStreamResponse::class, $response);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($article): true {
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('Hello World', $broadcast->inlineContent);
            $this->assertCount(1, $broadcast->channels);
            $this->assertInstanceOf(PrivateChannel::class, $broadcast->channels[0]);
            $this->assertEquals('private-'.$article->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertTrue($broadcast->sendToOthers);

            return true;
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function broadcast_to_model_as_presence_channel_using_response_builder_function(): void
    {
        $message = MessageFactory::new()->create()->fresh();

        $response = turbo_stream()
            ->append('notifications', 'Hello World')
            ->broadcastToPresenceChannel($message, fn ($broadcast) => $broadcast->toOthers());

        $this->assertInstanceOf(PendingTurboStreamResponse::class, $response);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($message): true {
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('Hello World', $broadcast->inlineContent);
            $this->assertCount(1, $broadcast->channels);
            $this->assertInstanceOf(PresenceChannel::class, $broadcast->channels[0]);
            $this->assertEquals('presence-'.$message->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertTrue($broadcast->sendToOthers);

            return true;
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function broadcast_model_changes_using_function(): void
    {
        $message = MessageFactory::new()->create();

        $response = turbo_stream($message)
            ->broadcastTo($message, fn ($broadcast) => $broadcast->toOthers());

        $this->assertInstanceOf(PendingTurboStreamResponse::class, $response);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($message): true {
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('messages._message', $broadcast->partialView);
            $this->assertEquals(['message' => $message], $broadcast->partialData);
            $this->assertCount(1, $broadcast->channels);
            $this->assertInstanceOf(Channel::class, $broadcast->channels[0]);
            $this->assertEquals($message->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertTrue($broadcast->sendToOthers);

            return true;
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function broadcast_passing_model_with_broadcasts_trait_to_channel(): void
    {
        $comment = Comment::withoutEvents(fn () => CommentFactory::new()->create()->fresh());

        $response = turbo_stream($comment)
            ->broadcastTo($comment, fn ($broadcast) => $broadcast->toOthers());

        $this->assertInstanceOf(PendingTurboStreamResponse::class, $response);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($comment): true {
            $this->assertEquals('replace', $broadcast->action);
            $this->assertEquals('comments._comment', $broadcast->partialView);
            $this->assertEquals(['comment' => $comment], $broadcast->partialData);
            $this->assertCount(1, $broadcast->channels);
            $this->assertInstanceOf(PrivateChannel::class, $broadcast->channels[0]);
            $this->assertEquals('private-'.$comment->article->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertTrue($broadcast->sendToOthers);

            return true;
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_disable_turbo_stream_broadcasts(): void
    {
        TurboStream::withoutBroadcasts(fn () => (
            TurboStream::broadcastRemove('todo_123')
        ));

        TurboStream::assertNothingWasBroadcasted();

        TurboStream::broadcastRemove('todo_123');

        TurboStream::assertBroadcasted(fn (PendingBroadcast $broadcast): bool => $broadcast->target === 'todo_123'
        && $broadcast->action === 'remove');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function globally_disabling_turbo_stream_broadcasts_also_disable_models(): void
    {
        $article = ArticleFactory::new()->create()->fresh();

        TurboStream::withoutBroadcasts(fn () => (
            $article->broadcastAppend()
        ));

        TurboStream::assertNothingWasBroadcasted();

        $article->broadcastAppend();

        TurboStream::assertBroadcasted(fn (PendingBroadcast $broadcast): bool => $broadcast->target === 'articles' && $broadcast->action === 'append');
    }
}
