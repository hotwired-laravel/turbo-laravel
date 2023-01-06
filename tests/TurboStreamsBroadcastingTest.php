<?php

namespace Tonysm\TurboLaravel\Tests\Testing;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Tonysm\TurboLaravel\Broadcasting\PendingBroadcast;
use Tonysm\TurboLaravel\Facades\TurboStream;
use Tonysm\TurboLaravel\Http\PendingTurboStreamResponse;
use Tonysm\TurboLaravel\Models\Broadcasts;
use Tonysm\TurboLaravel\Models\Naming\Name;
use Tonysm\TurboLaravel\Tests\Stubs\Models\TestModel;
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
        $method = sprintf('broadcast%s', ucfirst($action));

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

    /** @test */
    public function can_broadcast_to_multiple_public_channels()
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

    /** @test */
    public function can_manually_broadcast_to_private_channels()
    {
        $broadcasting = TurboStream::broadcastRemove(
            target: 'todo_123',
        )->toPrivateChannel('user.123');

        $this->assertInstanceOf(PrivateChannel::class, $broadcasting->channels[0]);
        $this->assertEquals('private-user.123', $broadcasting->channels[0]->name);
    }

    /** @test */
    public function can_manually_broadcast_to_multiple_private_channels()
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

    /** @test */
    public function can_manually_broadcast_to_presence_channels()
    {
        $broadcasting = TurboStream::broadcastRemove(
            target: 'todo_123',
        )->toPresenceChannel('user.123');

        $this->assertInstanceOf(PresenceChannel::class, $broadcasting->channels[0]);
        $this->assertEquals('presence-user.123', $broadcasting->channels[0]->name);
    }

    /** @test */
    public function can_manually_broadcast_to_multiple_presence_channels()
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

    /** @test */
    public function can_assert_nothing_was_broadcasted()
    {
        TurboStream::assertNothingWasBroadcasted();
    }

    /** @test */
    public function can_assert_broadcasted()
    {
        TurboStream::broadcastRemove('todo_123');

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
        TurboStream::broadcastRemove('todo_123');
        TurboStream::broadcastRemove('todo_123');

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
    public function broadcast_inline_content()
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

    /** @test */
    public function broadcast_inline_content_escaped()
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

    /** @test */
    public function broadcast_inline_content_as_html_string()
    {
        $broadcast = TurboStream::broadcastAppend(
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
        TurboStream::broadcastRemove('todo_123')->cancel();

        TurboStream::assertNothingWasBroadcasted();
    }

    /** @test */
    public function can_conditionally_cancel_broadcasting()
    {
        TurboStream::broadcastRemove('todo_123')->cancelIf(true);

        TurboStream::broadcastRemove('todo_123')->cancelIf(function () {
            return true;
        });

        TurboStream::assertNothingWasBroadcasted();

        TurboStream::broadcastRemove('todo_123')->cancelIf(false);

        TurboStream::broadcastRemove('todo_123')->cancelIf(function () {
            return false;
        });

        TurboStream::assertBroadcastedTimes(function ($broadcast) {
            return $broadcast->action === 'remove' && $broadcast->target === 'todo_123';
        }, 2);
    }

    /** @test */
    public function can_pass_model_without_broadcasts_trait_as_channel()
    {
        $model = TestModel::create(['name' => 'Testing']);

        TurboStream::broadcastRemove('todo_123', channel: $model);

        $called = false;

        TurboStream::assertBroadcasted(function ($broadcast) use ($model, &$called) {
            $called = true;

            return count($broadcast->channels) === 1
                && $broadcast->channels[0]->name === $model->broadcastChannel();
        });

        $this->assertTrue($called, 'Expected callback to be called, but it was not.');
    }

    /** @test */
    public function can_pass_model_with_broadcasts_trait_as_channel()
    {
        $model = BroadcastTestModel::create(['name' => 'Testing']);

        TurboStream::broadcastRemove('todo_123', channel: $model);

        $called = false;

        TurboStream::assertBroadcasted(function ($broadcast) use ($model, &$called) {
            $called = true;

            return count($broadcast->channels) === 1
                && $broadcast->channels[0]->name === $model->asTurboStreamBroadcastingChannel()[0]->name;
        });

        $this->assertTrue($called, 'Expected callback to be called, but it was not.');
    }

    /** @test */
    public function can_pass_recently_created_model_as_target()
    {
        $model = TestModel::create(['name' => 'Testing']);

        TurboStream::broadcastRemove($model);

        $called = false;

        TurboStream::assertBroadcasted(function ($broadcast) use ($model, &$called) {
            $called = true;

            return $broadcast->target === Name::forModel($model)->plural;
        });

        $this->assertTrue($called, 'Expected callback to be called, but it was not.');
    }

    /** @test */
    public function can_pass_existing_model_as_target()
    {
        $model = TestModel::find(TestModel::create(['name' => 'Testing'])->id);

        TurboStream::broadcastAppend('Testing', $model);

        $called = false;

        TurboStream::assertBroadcasted(function ($broadcast) use ($model, &$called) {
            $called = true;

            return $broadcast->target === dom_id($model);
        });

        $this->assertTrue($called, 'Expected callback to be called, but it was not.');
    }

    /** @test */
    public function broadcast_custom_action()
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

        TurboStream::assertBroadcasted(function ($broadcast) use (&$called) {
            $called = true;

            return $broadcast->action === 'console_log' && $broadcast->attributes == [
                'value' => 'Hello World',
            ];
        });

        $this->assertTrue($called, 'Expected callback to be called, but it was not.');
    }

    /** @test */
    public function pass_attributes_via_setter_method()
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

    /** @test */
    public function override_action_and_content_via_setter_methods()
    {
        $broadcast = TurboStream::broadcastAction('console_log')
            ->action('update_title')
            ->content('Hello World');

        $expected = <<<'HTML'
        <turbo-stream action="update_title">
            <template>Hello World</template>
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim($broadcast->render()));
    }

    /** @test */
    public function send_to_others()
    {
        $broadcast = TurboStream::broadcastAppend('Hello World');

        $this->assertFalse($broadcast->sendToOthers);

        $broadcast->toOthers();

        $this->assertTrue($broadcast->sendToOthers);
    }

    /** @test */
    public function broadcasts_using_the_response_builder_function()
    {
        $response = turbo_stream()
            ->append('notifications', 'Hello World')
            ->broadcastTo('general', fn ($broadcast) => $broadcast->toOthers());

        $this->assertInstanceOf(PendingTurboStreamResponse::class, $response);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) {
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('Hello World', $broadcast->inlineContent);
            $this->assertCount(1, $broadcast->channels);
            $this->assertInstanceOf(Channel::class, $broadcast->channels[0]);
            $this->assertEquals('general', $broadcast->channels[0]->name);
            $this->assertTrue($broadcast->sendToOthers);

            return true;
        });
    }

    /** @test */
    public function broadcast_to_private_channels_using_response_builder_function()
    {
        $response = turbo_stream()
            ->append('notifications', 'Hello World')
            ->broadcastToPrivateChannel('general', fn ($broadcast) => $broadcast->toOthers());

        $this->assertInstanceOf(PendingTurboStreamResponse::class, $response);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) {
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('Hello World', $broadcast->inlineContent);
            $this->assertCount(1, $broadcast->channels);
            $this->assertInstanceOf(PrivateChannel::class, $broadcast->channels[0]);
            $this->assertEquals('private-general', $broadcast->channels[0]->name);
            $this->assertTrue($broadcast->sendToOthers);

            return true;
        });
    }

    /** @test */
    public function broadcast_to_presence_channels_using_response_builder_function()
    {
        $response = turbo_stream()
            ->append('notifications', 'Hello World')
            ->broadcastToPresenceChannel('general', fn ($broadcast) => $broadcast->toOthers());

        $this->assertInstanceOf(PendingTurboStreamResponse::class, $response);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) {
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('Hello World', $broadcast->inlineContent);
            $this->assertCount(1, $broadcast->channels);
            $this->assertInstanceOf(PresenceChannel::class, $broadcast->channels[0]);
            $this->assertEquals('presence-general', $broadcast->channels[0]->name);
            $this->assertTrue($broadcast->sendToOthers);

            return true;
        });
    }

    /** @test */
    public function broadcast_to_model_channel_using_response_builder_function()
    {
        /** @var TestModel $model */
        $model = TestModel::create(['name' => 'Testing']);

        $response = turbo_stream()
            ->append('notifications', 'Hello World')
            ->broadcastTo($model, fn ($broadcast) => $broadcast->toOthers());

        $this->assertInstanceOf(PendingTurboStreamResponse::class, $response);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($model) {
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('Hello World', $broadcast->inlineContent);
            $this->assertCount(1, $broadcast->channels);
            $this->assertInstanceOf(Channel::class, $broadcast->channels[0]);
            $this->assertEquals($model->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertTrue($broadcast->sendToOthers);

            return true;
        });
    }

    /** @test */
    public function broadcast_to_model_as_private_channel_using_response_builder_function()
    {
        /** @var TestModel $model */
        $model = TestModel::create(['name' => 'Testing']);

        $response = turbo_stream()
            ->append('notifications', 'Hello World')
            ->broadcastToPrivateChannel($model, fn ($broadcast) => $broadcast->toOthers());

        $this->assertInstanceOf(PendingTurboStreamResponse::class, $response);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($model) {
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('Hello World', $broadcast->inlineContent);
            $this->assertCount(1, $broadcast->channels);
            $this->assertInstanceOf(PrivateChannel::class, $broadcast->channels[0]);
            $this->assertEquals('private-'.$model->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertTrue($broadcast->sendToOthers);

            return true;
        });
    }

    /** @test */
    public function broadcast_to_model_as_presence_channel_using_response_builder_function()
    {
        /** @var TestModel $model */
        $model = TestModel::create(['name' => 'Testing']);

        $response = turbo_stream()
            ->append('notifications', 'Hello World')
            ->broadcastToPresenceChannel($model, fn ($broadcast) => $broadcast->toOthers());

        $this->assertInstanceOf(PendingTurboStreamResponse::class, $response);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($model) {
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('Hello World', $broadcast->inlineContent);
            $this->assertCount(1, $broadcast->channels);
            $this->assertInstanceOf(PresenceChannel::class, $broadcast->channels[0]);
            $this->assertEquals('presence-'.$model->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertTrue($broadcast->sendToOthers);

            return true;
        });
    }

    /** @test */
    public function broadcast_model_changes_using_function()
    {
        /** @var TestModel $model */
        $model = TestModel::create(['name' => 'Testing']);

        $response = turbo_stream($model)
            ->broadcastTo($model, fn ($broadcast) => $broadcast->toOthers());

        $this->assertInstanceOf(PendingTurboStreamResponse::class, $response);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($model) {
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('test_models._test_model', $broadcast->partialView);
            $this->assertEquals(['testModel' => $model], $broadcast->partialData);
            $this->assertCount(1, $broadcast->channels);
            $this->assertInstanceOf(Channel::class, $broadcast->channels[0]);
            $this->assertEquals($model->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertTrue($broadcast->sendToOthers);

            return true;
        });
    }

    /** @test */
    public function broadcast_passing_model_with_broadcasts_trait_to_channel()
    {
        /** @var BroadcastTestModel $model */
        $model = BroadcastTestModel::find(BroadcastTestModel::create(['name' => 'Testing'])->id);

        $response = turbo_stream($model)
            ->broadcastTo($model, fn ($broadcast) => $broadcast->toOthers());

        $this->assertInstanceOf(PendingTurboStreamResponse::class, $response);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($model) {
            $this->assertEquals('replace', $broadcast->action);
            $this->assertEquals('broadcast_test_models._broadcast_test_model', $broadcast->partialView);
            $this->assertEquals(['broadcastTestModel' => $model], $broadcast->partialData);
            $this->assertCount(1, $broadcast->channels);
            $this->assertInstanceOf(PrivateChannel::class, $broadcast->channels[0]);
            $this->assertEquals('private-'.$model->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertTrue($broadcast->sendToOthers);

            return true;
        });
    }
}

class BroadcastTestModel extends TestModel
{
    use Broadcasts;
}
