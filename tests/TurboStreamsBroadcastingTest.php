<?php

namespace Tonysm\TurboLaravel\Tests\Testing;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\View;
use Tonysm\TurboLaravel\Broadcasting\PendingBroadcast;
use Tonysm\TurboLaravel\Facades\Turbo;
use Tonysm\TurboLaravel\Tests\TestCase;

class TurboStreamsBroadcastingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__ . '/Stubs/views');
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

        $broadcasting = Turbo::{$method}(
            channel: 'general',
            target: 'notifications',
            content: View::make('hello_view', [
                'name' => 'Tony',
            ]),
        )->cancel();

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
        $broadcasting = Turbo::broadcastRemoveTo(
            channel: 'general',
            target: 'todo_123',
        )->cancel();

        $expected = <<<HTML
        <turbo-stream target="todo_123" action="remove">
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim((string) $broadcasting->render()));
        $this->assertCount(1, $broadcasting->channels);
        $this->assertInstanceOf(Channel::class, $broadcasting->channels[0]);
        $this->assertEquals('general', $broadcasting->channels[0]->name);
    }

    /** @test */
    public function can_manually_broadcast_to_private_channels()
    {
        $broadcasting = Turbo::broadcastRemoveTo(
            target: 'todo_123',
        )->toPrivateChannel('user.123')->cancel();

        $this->assertInstanceOf(PrivateChannel::class, $broadcasting->channels[0]);
        $this->assertEquals('private-user.123', $broadcasting->channels[0]->name);
    }

    /** @test */
    public function can_manually_broadcast_to_presence_channels()
    {
        $broadcasting = Turbo::broadcastRemoveTo(
            target: 'todo_123',
        )->toPresenceChannel('user.123')->cancel();

        $this->assertInstanceOf(PresenceChannel::class, $broadcasting->channels[0]);
        $this->assertEquals('presence-user.123', $broadcasting->channels[0]->name);
    }

    /** @test */
    public function can_assert_nothing_was_broadcasted()
    {
        Turbo::fakeBroadcasting();

        Turbo::assertNothingWasBroadcasted();
    }

    /** @test */
    public function can_assert_broadcasted()
    {
        Turbo::fakeBroadcasting();

        Turbo::broadcastRemoveTo('todo_123');

        $called = false;

        Turbo::assertBroadcasted(function (PendingBroadcast $broadcast) use (&$called) {
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
        Turbo::fakeBroadcasting();

        Turbo::broadcastRemoveTo('todo_123');
        Turbo::broadcastRemoveTo('todo_123');

        $called = false;

        Turbo::assertBroadcastedTimes(function (PendingBroadcast $broadcast) use (&$called) {
            $called = true;

            return (
                $broadcast->target === 'todo_123'
                && $broadcast->action === 'remove'
            );
        }, 2);

        $this->assertTrue($called, 'The given filter callback was not called.');
    }
}
