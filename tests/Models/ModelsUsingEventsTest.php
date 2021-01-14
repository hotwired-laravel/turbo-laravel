<?php

namespace Tonysm\TurboLaravel\Tests\Models;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Tonysm\TurboLaravel\Events\TurboStreamModelCreated;
use Tonysm\TurboLaravel\Events\TurboStreamModelDeleted;
use Tonysm\TurboLaravel\Events\TurboStreamModelUpdated;
use Tonysm\TurboLaravel\Tests\TestCase;
use Tonysm\TurboLaravel\Tests\TestModel;

class ModelsUsingEventsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__ . '/stubs/views');
    }

    /** @test */
    public function models_can_use_the_events_directly_when_creating()
    {
        Event::fake([TurboStreamModelCreated::class]);

        $testModel = TestModelUsingEvent::create(['name' => 'Test Model Using Events']);

        $expectedTurboStream = <<<'blade'
<turbo-stream target="test_model_using_events" action="append">
    <template>
        <h1>Hello Turbo Stream TestModelUsingEvents</h1>
    </template>
</turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelCreated $event) use ($testModel, $expectedTurboStream) {
            $this->assertTrue($event->model->is($testModel));
            $this->assertEquals('append', $event->action);
            $this->assertEquals($expectedTurboStream, trim($event->render()));
            $this->assertEquals(
                sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($testModel)),
                    $testModel->getKey()
                ),
                $event->broadcastOn()[0]->name
            );

            return true;
        });
    }

    /** @test */
    public function models_can_use_the_events_directly_when_updated()
    {
        Event::fake([TurboStreamModelUpdated::class]);

        $testModel = TestModelUsingEvent::withoutEvents(function () {
            return TestModelUsingEvent::create(['name' => 'Test Model Using Events']);
        });

        $testModel->update(['name' => 'updated']);

        $expectedTurboStream = <<<blade
<turbo-stream target="test_model_using_event_{$testModel->getKey()}" action="replace">
    <template>
        <h1>Hello Turbo Stream TestModelUsingEvents</h1>
    </template>
</turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelUpdated $event) use ($testModel, $expectedTurboStream) {
            $this->assertTrue($event->model->is($testModel));
            $this->assertEquals('replace', $event->action);
            $this->assertEquals($expectedTurboStream, trim($event->render()));
            $this->assertEquals(
                sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($testModel)),
                    $testModel->getKey()
                ),
                $event->broadcastOn()[0]->name
            );

            return true;
        });
    }

    /** @test */
    public function models_can_use_the_events_directly_when_deleted()
    {
        Event::fake([TurboStreamModelDeleted::class]);

        $testModel = TestModelUsingEvent::withoutEvents(function () {
            return TestModelUsingEvent::create(['name' => 'Test Model Using Events']);
        });

        $testModel->delete();

        $expectedTurboStream = <<<blade
<turbo-stream target="test_model_using_event_{$testModel->getKey()}" action="remove"></turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelDeleted $event) use ($testModel, $expectedTurboStream) {
            $this->assertTrue($event->model->is($testModel));
            $this->assertEquals('remove', $event->action);
            $this->assertEquals($expectedTurboStream, trim($event->render()));
            $this->assertEquals(
                sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($testModel)),
                    $testModel->getKey()
                ),
                $event->broadcastOn()[0]->name
            );

            return true;
        });
    }
}

class TestModelUsingEvent extends TestModel
{
    protected $dispatchesEvents = [
        'created' => TurboStreamModelCreated::class,
        'updated' => TurboStreamModelUpdated::class,
        'deleted' => TurboStreamModelDeleted::class,
    ];
}
