<?php

namespace Tonysm\TurboLaravel\Tests\Events;

use Tonysm\TurboLaravel\Events\TurboStreamBroadcast;
use Tonysm\TurboLaravel\Tests\Stubs\Models\TestModel;
use Tonysm\TurboLaravel\Tests\TestCase;
use View;

class TurboStreamBroadcastTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__ . '/../Stubs/views');
    }

    /** @test */
    public function renders_turbo_stream()
    {
        $event = new TurboStreamBroadcast(
            [],
            'test_target',
            'replace',
            'test_models._test_model',
            ['testModel' => new TestModel(['id' => 1])]
        );

        $expected = View::make('turbo-laravel::turbo-stream', [
            'target' => 'test_target',
            'action' => 'replace',
            'partial' => 'test_models._test_model',
            'partialData' => [
                'testModel' => new TestModel(['id' => 1]),
            ],
        ]);

        $rendered = $event->render();

        $this->assertEquals(trim($expected), trim($rendered));
    }

    /** @test */
    public function renders_turbo_stream_targets()
    {
        $event = new TurboStreamBroadcast(
            [],
            null,
            'replace',
            'test_models._test_model',
            ['testModel' => new TestModel(['id' => 1])],
            'targets'
        );

        $expected = View::make('turbo-laravel::turbo-stream', [
            'targets' => 'targets',
            'action' => 'replace',
            'partial' => 'test_models._test_model',
            'partialData' => [
                'testModel' => new TestModel(['id' => 1]),
            ],
        ]);

        $rendered = $event->render();

        $this->assertEquals(trim($expected), trim($rendered));
    }
}
