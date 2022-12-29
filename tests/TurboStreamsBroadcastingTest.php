<?php

namespace Tonysm\TurboLaravel\Tests\Testing;

use Illuminate\Broadcasting\Channel;
use Illuminate\Support\Facades\View;
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
}
