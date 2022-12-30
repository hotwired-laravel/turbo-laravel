<?php

namespace Tonysm\TurboLaravel\Tests\Testing;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Tonysm\TurboLaravel\Broadcasting\PendingBroadcast;
use Tonysm\TurboLaravel\Facades\TurboStream;
use Tonysm\TurboLaravel\Tests\TestCase;

class TurboStreamsBroadcastingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__ . '/Stubs/views');

        TurboStream::fake();
    }

    public function turboStreamDefaultInsertActions()
    {
        return [
            ['append'],
            ['prepend'],
            ['before'],
            ['after'],
            ['update'],
            ['replace'],
        ];
    }

    /**
     * @test
     * @dataProvider turboStreamDefaultInsertActions
     */
    public function can_manually_broadcast_append_streams(string $action)
    {
        $method = sprintf('broadcast%sTo', ucfirst($action));

        $broadcasting = TurboStream::{$method}(
            channel: 'general',
            target: 'notifications',
            content: View::make('hello_view', [
                'name' => 'Tony',
            ]),
        );

        $expected = <<<HTML
        <turbo-stream target="notifications" action="{$action}">
            <template><div>Hello, Tony</div></template>
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim((string) $broadcasting->render()));
        $this->assertCount(1, $broadcasting->channels);
        $this->assertInstanceOf(Channel::class, $broadcasting->channels[0]);
        $this->assertEquals('general', $broadcasting->channels[0]->name);
    }

    /** @test */
    public function manually_broadcast_remove_stream()
    {
        $broadcasting = TurboStream::broadcastRemoveTo(
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

    /** @test */
    public function can_broadcast_to_multiple_public_channels()
    {
        $broadcasting = TurboStream::broadcastRemoveTo(
            channel: ['general', 'todolist.123'],
            target: 'todo_123',
        );

        $this->assertCount(2, $broadcasting->channels);

        $this->assertInstanceOf(Channel::class, $broadcasting->channels[0]);
        $this->assertEquals('general', $broadcasting->channels[0]->name);

        $this->assertInstanceOf(Channel::class, $broadcasting->channels[1]);
        $this->assertEquals('todolist.123', $broadcasting->channels[1]->name);
    }

    /** @test */
    public function can_manually_broadcast_to_private_channels()
    {
        $broadcasting = TurboStream::broadcastRemoveTo(
            target: 'todo_123',
        )->toPrivateChannel('user.123');

        $this->assertInstanceOf(PrivateChannel::class, $broadcasting->channels[0]);
        $this->assertEquals('private-user.123', $broadcasting->channels[0]->name);
    }

    /** @test */
    public function can_manually_broadcast_to_multiple_private_channels()
    {
        $broadcasting = TurboStream::broadcastRemoveTo(
            target: 'todo_123',
        )->toPrivateChannel(['user.123', 'todolist.123']);

        $this->assertCount(2, $broadcasting->channels);

        $this->assertInstanceOf(PrivateChannel::class, $broadcasting->channels[0]);
        $this->assertEquals('private-user.123', $broadcasting->channels[0]->name);

        $this->assertInstanceOf(PrivateChannel::class, $broadcasting->channels[1]);
        $this->assertEquals('private-todolist.123', $broadcasting->channels[1]->name);
    }

    /** @test */
    public function can_manually_broadcast_to_presence_channels()
    {
        $broadcasting = TurboStream::broadcastRemoveTo(
            target: 'todo_123',
        )->toPresenceChannel('user.123');

        $this->assertInstanceOf(PresenceChannel::class, $broadcasting->channels[0]);
        $this->assertEquals('presence-user.123', $broadcasting->channels[0]->name);
    }

    /** @test */
    public function can_manually_broadcast_to_multiple_presence_channels()
    {
        $broadcasting = TurboStream::broadcastRemoveTo(
            target: 'todo_123',
        )->toPresenceChannel(['user.123', 'todolist.123']);

        $this->assertCount(2, $broadcasting->channels);

        $this->assertInstanceOf(PresenceChannel::class, $broadcasting->channels[0]);
        $this->assertEquals('presence-user.123', $broadcasting->channels[0]->name);

        $this->assertInstanceOf(PresenceChannel::class, $broadcasting->channels[1]);
        $this->assertEquals('presence-todolist.123', $broadcasting->channels[1]->name);
    }

    /** @test */
    public function can_assert_nothing_was_broadcasted()
    {
        TurboStream::assertNothingWasBroadcasted();
    }

    /** @test */
    public function can_assert_broadcasted()
    {
        TurboStream::broadcastRemoveTo('todo_123');

        $called = false;

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use (&$called) {
            $called = true;

            return (
                $broadcast->target === 'todo_123'
                && $broadcast->action === 'remove'
            );
        });

        $this->assertTrue($called, 'The given filter callback was not called.');
    }

    /** @test */
    public function can_assert_broadcasted_times()
    {
        TurboStream::broadcastRemoveTo('todo_123');
        TurboStream::broadcastRemoveTo('todo_123');

        $called = false;

        TurboStream::assertBroadcastedTimes(function (PendingBroadcast $broadcast) use (&$called) {
            $called = true;

            return (
                $broadcast->target === 'todo_123'
                && $broadcast->action === 'remove'
            );
        }, 2);

        $this->assertTrue($called, 'The given filter callback was not called.');
    }

    /** @test */
    public function broadcast_inline_content_escaped()
    {
        $broadcast = TurboStream::broadcastAppendTo(
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

    /** @test */
    public function broadcast_inline_content_as_html_string()
    {
        $broadcast = TurboStream::broadcastAppendTo(
            channel: 'general',
            target: 'notifications',
            content: new HtmlString("<h1>Hello World</h1>"),
        );

        $expected = <<<'HTML'
        <turbo-stream target="notifications" action="append">
            <template><h1>Hello World</h1></template>
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim($broadcast->render()));
    }

    /** @test */
    public function can_cancel_broadcasting()
    {
        TurboStream::broadcastRemoveTo('todo_123')->cancel();

        TurboStream::assertNothingWasBroadcasted();
    }
}
