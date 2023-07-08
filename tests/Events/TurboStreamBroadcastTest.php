<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Events;

use HotwiredLaravel\TurboLaravel\Events\TurboStreamBroadcast;
use HotwiredLaravel\TurboLaravel\Tests\Stubs\Models\TestModel;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use View;

class TurboStreamBroadcastTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__.'/../Stubs/views');
    }

    /** @test */
    public function renders_turbo_stream()
    {
        $event = new TurboStreamBroadcast(
            [],
            'replace',
            'test_target',
            null,
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
            'replace',
            null,
            '.targets',
            'test_models._test_model',
            ['testModel' => new TestModel(['id' => 1])],
        );

        $expected = View::make('turbo-laravel::turbo-stream', [
            'action' => 'replace',
            'targets' => '.targets',
            'partial' => 'test_models._test_model',
            'partialData' => [
                'testModel' => new TestModel(['id' => 1]),
            ],
        ]);

        $rendered = $event->render();

        $this->assertEquals(trim($expected), trim($rendered));
    }
}
