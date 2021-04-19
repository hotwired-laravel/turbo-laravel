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
            '_test_model',
            ['testModel' => new TestModel(['id' => 1])]
        );

        $expected = View::make('turbo-laravel::turbo-stream', [
            'target' => 'test_target',
            'action' => 'replace',
            'partial' => '_test_model',
            'partialData' => [
                'testModel' => new TestModel(['id' => 1]),
            ],
        ]);

        $rendered = $event->render();

        $this->assertEquals(trim($expected), trim($rendered));
    }
}
