<?php

namespace Tonysm\TurboLaravel\Tests\Models;

use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
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

        View::addLocation(__DIR__ . '/../Stubs/views');
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
            $this->assertTrue($model->is($event->model));
            $this->assertEquals('append', $event->action);
            $this->assertEquals($expectedPartialRender, trim($event->render()));
            $this->assertEquals(
                sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($model)),
                    $model->getKey()
                ),
                $event->broadcastOn()[0]->name
            );

            return true;
        });
    }

    /** @test */
    public function broadcasts_on_update()
    {
        Event::fake([TurboStreamModelUpdated::class]);

        $model = BroadcastTestModel::find(
            BroadcastTestModel::create(['name' => 'My model'])->getKey()
        );

        $this->assertFalse($model->wasRecentlyCreated);
        $model->update(['name' => 'Changed']);

        $expectedPartialRender = <<<'blade'
<turbo-stream target="broadcast_test_model_1" action="replace">
    <template>
        <h1>Hello from TestModel partial</h1>
    </template>
</turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelUpdated $event) use ($model, $expectedPartialRender) {
            $this->assertTrue($model->is($event->model));
            $this->assertEquals('replace', $event->action);
            $this->assertEquals($expectedPartialRender, trim($event->render()));
            $this->assertEquals(
                sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($model)),
                    $model->getKey()
                ),
                $event->broadcastOn()[0]->name
            );

            return true;
        });
    }

    /** @test */
    public function broadcasts_on_delete()
    {
        Event::fake([TurboStreamModelDeleted::class]);

        $model = BroadcastTestModel::find(
            BroadcastTestModel::create(['name' => 'My model'])->getKey()
        );

        $model->delete();

        App::terminate();

        $expectedPartialRender = <<<'blade'
<turbo-stream target="broadcast_test_model_1" action="remove"></turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelDeleted $event) use ($model, $expectedPartialRender) {
            return $model->is($event->model)
                && $event->action === "remove"
                && trim($event->render()) === $expectedPartialRender
                && $event->broadcastOn()[0]->name === sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($model)),
                    $model->getKey()
                );
        });
    }

    /** @test */
    public function broadcasting_soft_deleted_models_works_as_regular_delete_by_default()
    {
        Event::fake([TurboStreamModelDeleted::class]);

        $model = BroadcastTestModelSoftDelete::withoutEvents(function () {
            return BroadcastTestModelSoftDelete::find(
                BroadcastTestModelSoftDelete::create(['name' => 'My model'])->getKey()
            );
        });

        $model->delete();

        App::terminate();

        $expectedPartialRender = <<<'blade'
<turbo-stream target="broadcast_test_model_soft_delete_1" action="remove"></turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelDeleted $event) use ($model, $expectedPartialRender) {
            return $model->is($event->model)
                && $event->action === "remove"
                && trim($event->render()) === $expectedPartialRender
                && $event->broadcastOn()[0]->name === sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($model)),
                    $model->getKey()
                );
        });
    }

    /** @test */
    public function broadcasts_using_override_partial_name()
    {
        Event::fake([TurboStreamModelCreated::class]);

        $model = BroadcastTestModelDifferentPartial::create(['name' => 'My model']);

        $expectedPartialRender = <<<'blade'
<turbo-stream target="broadcast_test_model_different_partials" action="append">
    <template>
        <h1>Hello from a different partial</h1>
    </template>
</turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelCreated $event) use ($model, $expectedPartialRender) {
            return $model->is($event->model)
                && $event->action === "append"
                && trim($event->render()) === $expectedPartialRender
                && $event->broadcastOn()[0]->name === sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($model)),
                    $model->getKey()
                );
        });
    }

    /** @test */
    public function broadcasts_using_override_action()
    {
        Event::fake([TurboStreamModelCreated::class, TurboStreamModelUpdated::class]);

        $model = tap(tap(BroadcastTestModelDifferentAction::create(['name' => 'My model'])->fresh())->update([
            'name' => 'Changed',
        ]))->delete();

        $expectedPartialRenderForCreate = <<<'blade'
<turbo-stream target="broadcast_test_model_different_actions" action="prepend">
    <template>
        <h1>Hello from a different partial</h1>
    </template>
</turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelCreated $event) use ($model, $expectedPartialRenderForCreate) {
            return $model->is($event->model)
                && $event->action === "prepend"
                && trim($event->render()) === $expectedPartialRenderForCreate
                && $event->broadcastOn()[0]->name === sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($model)),
                    $model->getKey()
                );
        });

        $expectedPartialRenderForUpdate = <<<blade
<turbo-stream target="broadcast_test_model_different_action_{$model->getKey()}" action="replace">
    <template>
        <h1>Hello from a different partial</h1>
    </template>
</turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelUpdated $event) use ($model, $expectedPartialRenderForUpdate) {
            return $model->is($event->model)
                && $event->action === "replace"
                && trim($event->render()) === $expectedPartialRenderForUpdate
                && $event->broadcastOn()[0]->name === sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($model)),
                    $model->getKey()
                );
        });
    }

    /** @test */
    public function broadcasts_using_override_target_id_on_update()
    {
        Event::fake([TurboStreamModelCreated::class, TurboStreamModelUpdated::class]);

        $model = tap(BroadcastTestModelDifferentTargetId::create(['name' => 'My model'])->fresh())->update([
            'name' => 'Changed',
        ]);

        $expectedPartialRenderForCreate = <<<blade
<turbo-stream target="changed-resource-name" action="append">
    <template>
        <h1>Hello from a different partial</h1>
    </template>
</turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelCreated $event) use ($model, $expectedPartialRenderForCreate) {
            $this->assertTrue($model->is($event->model));
            $this->assertEquals('append', $event->action);
            $this->assertEquals($expectedPartialRenderForCreate, trim($event->render()));
            $this->assertEquals(
                sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($model)),
                    $model->getKey()
                ),
                $event->broadcastOn()[0]->name
            );

            return true;
        });

        $expectedPartialRenderForUpdate = <<<blade
<turbo-stream target="hello-{$model->getKey()}" action="replace">
    <template>
        <h1>Hello from a different partial</h1>
    </template>
</turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelUpdated $event) use ($model, $expectedPartialRenderForUpdate) {
            $this->assertTrue($model->is($event->model));
            $this->assertEquals('replace', $event->action);
            $this->assertEquals($expectedPartialRenderForUpdate, trim($event->render()));
            $this->assertEquals(
                sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($model)),
                    $model->getKey()
                ),
                $event->broadcastOn()[0]->name
            );

            return true;
        });
    }

    /** @test */
    public function broadcasts_to_related_model_using_override_property()
    {
        Event::fake([TurboStreamModelCreated::class]);

        $parent = RelatedModelParent::create(['name' => 'Parent']);
        $child = RelatedModelChildArr::create(['name' => 'Child', 'parent_id' => $parent->getKey()]);

        $expectedPartialRenderForCreate = <<<blade
<turbo-stream target="related_model_child_arrs" action="append">
    <template>
        <h1>Hello from a different partial</h1>
    </template>
</turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelCreated $event) use ($parent, $child, $expectedPartialRenderForCreate) {
            return $child->is($event->model)
                && $event->action === "append"
                && trim($event->render()) === $expectedPartialRenderForCreate
                && $event->broadcastOn()[0]->name === sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($parent)),
                    $parent->getKey()
                );
        });
    }

    /** @test */
    public function broadcasts_to_related_model_using_override_property_array()
    {
        Event::fake([TurboStreamModelCreated::class]);

        $parent = RelatedModelParent::create(['name' => 'Parent']);
        $child = RelatedModelChild::create(['name' => 'Child', 'parent_id' => $parent->getKey()]);

        $expectedPartialRenderForCreate = <<<blade
<turbo-stream target="related_model_children" action="append">
    <template>
        <h1>Hello from a different partial</h1>
    </template>
</turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelCreated $event) use ($parent, $child, $expectedPartialRenderForCreate) {
            return $child->is($event->model)
                && $event->action === "append"
                && trim($event->render()) === $expectedPartialRenderForCreate
                && $event->broadcastOn()[0]->name === sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($parent)),
                    $parent->getKey()
                );
        });
    }

    /** @test */
    public function broadcasts_to_related_model_using_override_method()
    {
        Event::fake([TurboStreamModelCreated::class]);

        $parent = RelatedModelParent::create(['name' => 'Parent']);
        $child = RelatedModelChildMethod::create(['name' => 'Child', 'parent_id' => $parent->getKey()]);

        $expectedPartialRenderForCreate = <<<blade
<turbo-stream target="related_model_child_methods" action="append">
    <template>
        <h1>Hello from a different partial</h1>
    </template>
</turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelCreated $event) use ($parent, $child, $expectedPartialRenderForCreate) {
            return $child->is($event->model)
                && $event->action === "append"
                && trim($event->render()) === $expectedPartialRenderForCreate
                && $event->broadcastOn()[0]->name === sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($parent)),
                    $parent->getKey()
                );
        });
    }

    /** @test */
    public function broadcasts_to_related_model_using_override_method_array()
    {
        Event::fake([TurboStreamModelCreated::class]);

        $parent = RelatedModelParent::create(['name' => 'Parent']);
        $child = RelatedModelChildMethodArray::create(['name' => 'Child', 'parent_id' => $parent->getKey()]);

        $expectedPartialRenderForCreate = <<<blade
<turbo-stream target="related_model_child_method_arrays" action="append">
    <template>
        <h1>Hello from a different partial</h1>
    </template>
</turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelCreated $event) use ($parent, $child, $expectedPartialRenderForCreate) {
            return $child->is($event->model)
                && $event->action === "append"
                && trim($event->render()) === $expectedPartialRenderForCreate
                && $event->broadcastOn()[0]->name === sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($parent)),
                    $parent->getKey()
                );
        });
    }

    /** @test */
    public function broadcasts_using_another_channel()
    {
        Event::fake([TurboStreamModelCreated::class]);

        $model = BroadcastTestModelUsingChannel::create(['name' => 'Switch Channel']);

        $expectedPartialRenderForCreate = <<<blade
<turbo-stream target="broadcast_test_model_using_channels" action="append">
    <template>
        <h1>Hello from a different partial</h1>
    </template>
</turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelCreated $event) use ($model, $expectedPartialRenderForCreate) {
            return $model->is($event->model)
                && $event->action === "append"
                && trim($event->render()) === $expectedPartialRenderForCreate
                && $event->broadcastOn()[0]->name === 'lorem.ipsum';
        });
    }

    /** @test */
    public function broadcasts_using_override_partial_data()
    {
        Event::fake([TurboStreamModelCreated::class]);

        $model = BroadcastTestModelDifferentPartialData::create(['name' => 'Switch Channel']);

        $expectedPartialRenderForCreate = <<<blade
<turbo-stream target="broadcast_test_model_different_partial_datas" action="append">
    <template>
        <h2>Hello, John Doe</h2>
    </template>
</turbo-stream>
blade;

        Event::assertDispatched(function (TurboStreamModelCreated $event) use ($model, $expectedPartialRenderForCreate) {
            $this->assertTrue($model->is($event->model));
            $this->assertEquals('append', $event->action);
            $this->assertEquals($expectedPartialRenderForCreate, trim($event->render()));
            $this->assertEquals(
                sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($model)),
                    $model->getKey()
                ),
                $event->broadcastOn()[0]->name
            );

            return true;
        });
    }

    /** @test */
    public function prefers_custom_turbo_stream_views_when_creating_models()
    {
        Event::fake([TurboStreamModelCreated::class]);

        $model = BroadcastsUsingCustomTurboStreamView::create(['name' => 'test']);

        Event::assertDispatched(function (TurboStreamModelCreated $event) use ($model) {
            $this->assertEquals(
                sprintf('created custom partial for %s', $model->name),
                trim($event->render())
            );

            return true;
        });
    }

    /** @test */
    public function prefers_custom_turbo_stream_views_when_updating_models()
    {
        Event::fake([TurboStreamModelUpdated::class]);

        $model = BroadcastsUsingCustomTurboStreamView::withoutEvents(function () {
            return BroadcastsUsingCustomTurboStreamView::create(['name' => 'test']);
        });

        $model->update(['name' => 'changed']);

        Event::assertDispatched(function (TurboStreamModelUpdated $event) use ($model) {
            $this->assertEquals(
                sprintf('updated custom partial for %s', $model->name),
                trim($event->render())
            );

            return true;
        });
    }

    /** @test */
    public function prefers_custom_turbo_stream_views_when_deleting_models()
    {
        Event::fake([TurboStreamModelDeleted::class]);

        $model = BroadcastsUsingCustomTurboStreamView::withoutEvents(function () {
            return BroadcastsUsingCustomTurboStreamView::create(['name' => 'test']);
        });

        $model->delete();

        App::terminate();

        Event::assertDispatched(function (TurboStreamModelDeleted $event) use ($model) {
            $this->assertEquals(
                sprintf('deleted custom partial for %s', $model->name),
                trim($event->render())
            );

            return true;
        });
    }

    /** @test */
    public function broadcasts_to_related_model_when_deleting()
    {
        Event::fake([TurboStreamModelDeleted::class]);

        $parent = null;
        $child = null;

        Model::withoutEvents(function () use (&$parent, &$child) {
            $parent = RelatedModelParent::create(['name' => 'test']);
            $child = RelatedModelChildUsingBroadcasts::create(['name' => 'child', 'parent_id' => $parent->getKey()]);
        });

        $child->delete();

        App::terminate();

        Event::assertDispatched(function (TurboStreamModelDeleted $event) use ($parent, $child) {
            $this->assertTrue($event->model->is($child));
            $this->assertEquals(
                sprintf(
                    'private-%s.%s',
                    str_replace('\\', '.', get_class($parent)),
                    $parent->getKey()
                ),
                $event->broadcastOn()[0]->name
            );

            return true;
        });
    }
}

class BroadcastTestModel extends TestModel
{
    use Broadcasts;
}

class BroadcastTestModelSoftDelete extends BroadcastTestModel
{
    use SoftDeletes;
}

class BroadcastTestModelDifferentPartial extends BroadcastTestModel
{
    public function hotwirePartialName()
    {
        return "_override_partial_name";
    }
}

class BroadcastTestModelDifferentAction extends BroadcastTestModelDifferentPartial
{
    public $turboStreamCreatedAction = "prepend";
    public $turboStreamUpdatedAction = "replace";
}

class BroadcastTestModelDifferentTargetId extends BroadcastTestModelDifferentPartial
{
    public function hotwireTargetDomId()
    {
        return "hello-{$this->getKey()}";
    }

    public function hotwireTargetResourcesName()
    {
        return "changed-resource-name";
    }
}

class RelatedModelParent extends TestModel
{
}

class RelatedModelChild extends BroadcastTestModelDifferentPartial
{
    public $broadcastsTo = 'parent';

    public function parent()
    {
        return $this->belongsTo(RelatedModelParent::class);
    }
}

class RelatedModelChildUsingBroadcasts extends BroadcastTestModelDifferentPartial
{
    public $broadcastsTo = 'parent';

    public function parent()
    {
        return $this->belongsTo(RelatedModelParent::class);
    }
}

class RelatedModelChildArr extends BroadcastTestModelDifferentPartial
{
    public $broadcastsTo = [
        'parent',
    ];

    public function parent()
    {
        return $this->belongsTo(RelatedModelParent::class);
    }
}

class RelatedModelChildMethod extends BroadcastTestModelDifferentPartial
{
    public function broadcastsTo()
    {
        return $this->parent;
    }

    public function parent()
    {
        return $this->belongsTo(RelatedModelParent::class);
    }
}

class RelatedModelChildMethodArray extends BroadcastTestModelDifferentPartial
{
    public function broadcastsTo()
    {
        return [
            $this->parent,
        ];
    }

    public function parent()
    {
        return $this->belongsTo(RelatedModelParent::class);
    }
}

class BroadcastTestModelUsingChannel extends BroadcastTestModelDifferentPartial
{
    public function broadcastsTo()
    {
        return new Channel('lorem.ipsum');
    }
}

class BroadcastTestModelDifferentPartialData extends BroadcastTestModel
{
    public function hotwirePartialName()
    {
        return "_override_partial_data";
    }

    public function hotwirePartialData()
    {
        return [
            'name' => 'John Doe',
        ];
    }
}

class BroadcastsUsingCustomTurboStreamView extends TestModel
{
    use Broadcasts;
}
