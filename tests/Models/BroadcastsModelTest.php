<?php

namespace Tonysm\TurboLaravel\Tests\Models;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Tonysm\TurboLaravel\Events\TurboStreamModelCreated;
use Tonysm\TurboLaravel\Events\TurboStreamModelDeleted;
use Tonysm\TurboLaravel\Events\TurboStreamModelUpdated;
use Tonysm\TurboLaravel\Models\Broadcasts;
use Tonysm\TurboLaravel\Tests\TestCase;
use Tonysm\TurboLaravel\Tests\TestModel;

class BroadcastsModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__ . '/stubs/views');
    }

    /** @test */
    public function broadcasts_on_create()
    {
        Event::fake([TurboStreamModelCreated::class]);

        $model = BroadcastTestModel::create(['name' => 'My model']);

        $expectedPartialRender = <<<'blade'
<turbo-stream target="broadcast_test_models" action="append">
    <template>
        <h1>Hello from TestModel partial</h1>
    </template>
</turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelCreated $event) use ($model, $expectedPartialRender) {
            return $model->is($event->model)
                && $event->action === "append"
                && trim($event->render()) === $expectedPartialRender
                && $event->broadcastOn()->name === sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($model)),
                    $model->id
                );
        });
    }

    /** @test */
    public function broadcasts_on_update()
    {
        Event::fake([TurboStreamModelUpdated::class]);

        $model = BroadcastTestModel::find(
            BroadcastTestModel::create(['name' => 'My model'])->id
        );

        $this->assertFalse($model->wasRecentlyCreated);
        $model->update(['name' => 'Changed']);

        $expectedPartialRender = <<<'blade'
<turbo-stream target="broadcast_test_model_1" action="update">
    <template>
        <h1>Hello from TestModel partial</h1>
    </template>
</turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelUpdated $event) use ($model, $expectedPartialRender) {
            return $model->is($event->model)
                && $event->action === "update"
                && trim($event->render()) === $expectedPartialRender
                && $event->broadcastOn()->name === sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($model)),
                    $model->id
                );
        });
    }

    /** @test */
    public function broadcasts_on_delete()
    {
        Event::fake([TurboStreamModelDeleted::class]);

        $model = BroadcastTestModel::find(
            BroadcastTestModel::create(['name' => 'My model'])->id
        );

        $model->delete();

        $expectedPartialRender = <<<'blade'
<turbo-stream target="broadcast_test_model_1" action="remove"></turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelDeleted $event) use ($model, $expectedPartialRender) {
            return $model->is($event->model)
                && $event->action === "remove"
                && trim($event->render()) === $expectedPartialRender
                && $event->broadcastOn()->name === sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($model)),
                    $model->id
                );
        });
    }

    /** @test */
    public function broadcasts_using_override_action()
    {
    }

    /** @test */
    public function broadcasts_using_override_target_id()
    {
    }

    /** @test */
    public function broadcasts_to_related_model_using_override_property()
    {
    }

    /** @test */
    public function broadcasts_to_related_model_using_override_method()
    {
    }

    /** @test */
    public function broadcasts_using_another_channel()
    {
    }

    /** @test */
    public function broadcasts_using_override_partial_name()
    {
    }

    /** @test */
    public function broadcasts_using_override_partial_data()
    {
    }
}

class BroadcastTestModel extends TestModel
{
    use Broadcasts;
}
