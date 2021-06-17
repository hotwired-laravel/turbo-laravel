<?php

namespace Tonysm\TurboLaravel\Tests\Models;

use Illuminate\Broadcasting\Channel;
use Illuminate\Support\Facades\Bus;
use Tonysm\TurboLaravel\Jobs\BroadcastAction;
use Tonysm\TurboLaravel\Models\Broadcasts;
use Tonysm\TurboLaravel\Tests\TestCase;
use Tonysm\TurboLaravel\Tests\TestModel;

class BroadcastsModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['turbo-laravel.queue' => false]);
    }

    /** @test */
    public function manually_broadcast_append()
    {
        Bus::fake([BroadcastAction::class]);

        $model = BroadcastTestModel::create(['name' => 'Testing']);

        Bus::assertNotDispatched(BroadcastAction::class);

        $model->broadcastAppend();

        Bus::assertDispatched(function (BroadcastAction $job) use ($model) {
            $this->assertCount(1, $job->channels);
            $this->assertEquals(sprintf('private-%s', $model->broadcastChannel()), $job->channels[0]->name);
            $this->assertEquals('broadcast_test_models', $job->target);
            $this->assertEquals('append', $job->action);
            $this->assertEquals('broadcast_test_models._broadcast_test_model', $job->partial);
            $this->assertEquals(['broadcastTestModel' => $model], $job->partialData);

            return true;
        });
    }

    /** @test */
    public function manually_append_with_overrides()
    {
        Bus::fake([BroadcastAction::class]);

        $model = BroadcastTestModel::create(['name' => 'Testing']);

        Bus::assertNotDispatched(BroadcastAction::class);

        $model->broadcastAppend()
            ->to($channel = new Channel('hello'))
            ->target('some_other_target')
            ->partial('another_partial', ['lorem' => 'ipsum']);

        Bus::assertDispatched(function (BroadcastAction $job) use ($channel) {
            $this->assertCount(1, $job->channels);
            $this->assertSame($channel, $job->channels[0]);
            $this->assertEquals('some_other_target', $job->target);
            $this->assertEquals('append', $job->action);
            $this->assertEquals('another_partial', $job->partial);
            $this->assertEquals(['lorem' => 'ipsum'], $job->partialData);

            return true;
        });
    }

    /** @test */
    public function manually_before_with_overrides()
    {
        Bus::fake([BroadcastAction::class]);

        $model = BroadcastTestModel::create(['name' => 'Testing']);

        Bus::assertNotDispatched(BroadcastAction::class);

        $model->broadcastBefore('example_dom_id_target')
            ->to($channel = new Channel('hello'))
            ->partial('another_partial', ['lorem' => 'ipsum']);

        Bus::assertDispatched(function (BroadcastAction $job) use ($channel) {
            $this->assertCount(1, $job->channels);
            $this->assertSame($channel, $job->channels[0]);
            $this->assertEquals('example_dom_id_target', $job->target);
            $this->assertEquals('before', $job->action);
            $this->assertEquals('another_partial', $job->partial);
            $this->assertEquals(['lorem' => 'ipsum'], $job->partialData);

            return true;
        });
    }

    /** @test */
    public function manually_after_with_overrides()
    {
        Bus::fake([BroadcastAction::class]);

        $model = BroadcastTestModel::create(['name' => 'Testing']);

        Bus::assertNotDispatched(BroadcastAction::class);

        $model->broadcastAfter('example_dom_id_target')
            ->to($channel = new Channel('hello'))
            ->partial('another_partial', ['lorem' => 'ipsum']);

        Bus::assertDispatched(function (BroadcastAction $job) use ($channel) {
            $this->assertCount(1, $job->channels);
            $this->assertSame($channel, $job->channels[0]);
            $this->assertEquals('example_dom_id_target', $job->target);
            $this->assertEquals('after', $job->action);
            $this->assertEquals('another_partial', $job->partial);
            $this->assertEquals(['lorem' => 'ipsum'], $job->partialData);

            return true;
        });
    }

    /** @test */
    public function manually_before_to_with_overrides()
    {
        Bus::fake([BroadcastAction::class]);

        $model = BroadcastTestModel::create(['name' => 'Testing']);

        Bus::assertNotDispatched(BroadcastAction::class);

        $model->broadcastBeforeTo($channel = new Channel('hello'), 'example_dom_id_target')
            ->partial('another_partial', ['lorem' => 'ipsum']);

        Bus::assertDispatched(function (BroadcastAction $job) use ($channel) {
            $this->assertCount(1, $job->channels);
            $this->assertSame($channel, $job->channels[0]);
            $this->assertEquals('example_dom_id_target', $job->target);
            $this->assertEquals('before', $job->action);
            $this->assertEquals('another_partial', $job->partial);
            $this->assertEquals(['lorem' => 'ipsum'], $job->partialData);

            return true;
        });
    }

    /** @test */
    public function manually_after_to_with_overrides()
    {
        Bus::fake([BroadcastAction::class]);

        $model = BroadcastTestModel::create(['name' => 'Testing']);

        Bus::assertNotDispatched(BroadcastAction::class);

        $model->broadcastAfterTo($channel = new Channel('hello'), 'example_dom_id_target')
            ->partial('another_partial', ['lorem' => 'ipsum']);

        Bus::assertDispatched(function (BroadcastAction $job) use ($channel) {
            $this->assertCount(1, $job->channels);
            $this->assertSame($channel, $job->channels[0]);
            $this->assertEquals('example_dom_id_target', $job->target);
            $this->assertEquals('after', $job->action);
            $this->assertEquals('another_partial', $job->partial);
            $this->assertEquals(['lorem' => 'ipsum'], $job->partialData);

            return true;
        });
    }

    /** @test */
    public function manually_broadcast_replace()
    {
        Bus::fake([BroadcastAction::class]);

        $model = BroadcastTestModel::create(['name' => 'Testing']);

        Bus::assertNotDispatched(BroadcastAction::class);

        $model->broadcastReplace();

        Bus::assertDispatched(function (BroadcastAction $job) use ($model) {
            $this->assertCount(1, $job->channels);
            $this->assertEquals(sprintf('private-%s', $model->broadcastChannel()), $job->channels[0]->name);
            $this->assertEquals("broadcast_test_model_{$model->id}", $job->target);
            $this->assertEquals('replace', $job->action);
            $this->assertEquals('broadcast_test_models._broadcast_test_model', $job->partial);
            $this->assertEquals(['broadcastTestModel' => $model], $job->partialData);

            return true;
        });
    }

    /** @test */
    public function manually_broadcast_remove()
    {
        Bus::fake([BroadcastAction::class]);

        $model = BroadcastTestModel::create(['name' => 'Testing']);

        Bus::assertNotDispatched(BroadcastAction::class);

        $model->broadcastRemove();

        Bus::assertDispatched(function (BroadcastAction $job) use ($model) {
            $this->assertCount(1, $job->channels);
            $this->assertEquals(sprintf('private-%s', $model->broadcastChannel()), $job->channels[0]->name);
            $this->assertEquals("broadcast_test_model_{$model->id}", $job->target);
            $this->assertEquals('remove', $job->action);
            $this->assertNull($job->partial);
            $this->assertEquals([], $job->partialData);

            return true;
        });
    }

    /** @test */
    public function can_configure_to_auto_broadcast()
    {
        Bus::fake([BroadcastAction::class]);

        $model = BroadcastTestModel::create(['name' => 'Testing']);

        Bus::assertNotDispatched(BroadcastAction::class);

        $model->broadcastReplace();

        Bus::assertDispatched(function (BroadcastAction $job) use ($model) {
            $this->assertCount(1, $job->channels);
            $this->assertEquals(sprintf('private-%s', $model->broadcastChannel()), $job->channels[0]->name);
            $this->assertEquals("broadcast_test_model_{$model->id}", $job->target);
            $this->assertEquals('replace', $job->action);
            $this->assertEquals('broadcast_test_models._broadcast_test_model', $job->partial);
            $this->assertEquals(['broadcastTestModel' => $model], $job->partialData);

            return true;
        });
    }

    /** @test */
    public function can_configure_auto_broadcast_with_broadcasts_to_property()
    {
        Bus::fake([BroadcastAction::class]);

        $model = AutoBroadcastTestModel::create(['name' => 'Testing']);

        Bus::assertDispatched(function (BroadcastAction $job) use ($model) {
            $this->assertCount(1, $job->channels);
            $this->assertEquals(sprintf('private-%s', $model->broadcastChannel()), $job->channels[0]->name);
            $this->assertEquals('auto_broadcast_test_models', $job->target);
            $this->assertEquals('append', $job->action);
            $this->assertEquals('auto_broadcast_test_models._auto_broadcast_test_model', $job->partial);
            $this->assertEquals(['autoBroadcastTestModel' => $model], $job->partialData);

            return true;
        });
    }

    /** @test */
    public function auto_broadcasts_with_custom_inserts()
    {
        Bus::fake([BroadcastAction::class]);

        $modelWithCustomInsert = AutoBroadcastWithCustomInsertsTestModel::create(['name' => 'Testing']);

        Bus::assertDispatched(function (BroadcastAction $job) use ($modelWithCustomInsert) {
            $this->assertCount(1, $job->channels);
            $this->assertEquals(sprintf('private-%s', $modelWithCustomInsert->broadcastChannel()), $job->channels[0]->name);
            $this->assertEquals('auto_broadcast_with_custom_inserts_test_models', $job->target);
            $this->assertEquals('prepend', $job->action);
            $this->assertEquals('auto_broadcast_with_custom_inserts_test_models._auto_broadcast_with_custom_inserts_test_model', $job->partial);
            $this->assertEquals(['autoBroadcastWithCustomInsertsTestModel' => $modelWithCustomInsert], $job->partialData);

            return true;
        });
    }

    /** @test */
    public function can_configure_auto_wire_to_parent_model_using_property()
    {
        Bus::fake([BroadcastAction::class]);

        $parent = RelatedModelParent::create(['name' => 'Parent']);
        $child = RelatedModelChild::create(['name' => 'Child', 'parent_id' => $parent->id]);

        Bus::assertDispatched(function (BroadcastAction $job) use ($parent, $child) {
            $this->assertCount(1, $job->channels);
            $this->assertEquals(sprintf('private-%s', $parent->broadcastChannel()), $job->channels[0]->name);
            $this->assertEquals('related_model_children', $job->target);
            $this->assertEquals('append', $job->action);
            $this->assertEquals('related_model_children._related_model_child', $job->partial);
            $this->assertEquals(['relatedModelChild' => $child], $job->partialData);

            return true;
        });
    }

    /** @test */
    public function can_configure_auto_broadcast_to_parent_model_using_a_method()
    {
        Bus::fake([BroadcastAction::class]);

        $parent = RelatedModelParent::create(['name' => 'Parent']);
        $child = RelatedModelChildMethod::create(['name' => 'Child', 'parent_id' => $parent->id]);

        Bus::assertDispatched(function (BroadcastAction $job) use ($parent, $child) {
            $this->assertCount(1, $job->channels);
            $this->assertEquals(sprintf('private-%s', $parent->broadcastChannel()), $job->channels[0]->name);
            $this->assertEquals('related_model_child_methods', $job->target);
            $this->assertEquals('append', $job->action);
            $this->assertEquals('related_model_child_methods._related_model_child_method', $job->partial);
            $this->assertEquals(['relatedModelChildMethod' => $child], $job->partialData);

            return true;
        });
    }

    /** @test */
    public function can_configure_auto_broadcast_to_channel()
    {
        Bus::fake([BroadcastAction::class]);

        $model = BroadcastTestModelUsingChannel::create(['name' => 'Testing']);

        Bus::assertDispatched(function (BroadcastAction $job) use ($model) {
            $this->assertCount(1, $job->channels);
            $this->assertSame($model::$TEST_CHANNEL, $job->channels[0]);
            $this->assertEquals('broadcast_test_model_using_channels', $job->target);
            $this->assertEquals('append', $job->action);
            $this->assertEquals('broadcast_test_model_using_channels._broadcast_test_model_using_channel', $job->partial);
            $this->assertEquals(['broadcastTestModelUsingChannel' => $model], $job->partialData);

            return true;
        });
    }

    /** @test */
    public function combines_both_properties()
    {
        Bus::fake([BroadcastAction::class]);

        $parent = RelatedModelParent::create(['name' => 'Parent']);
        $child = CombinedPropertiesTestModel::create(['name' => 'Combined', 'parent_id' => $parent->id]);

        Bus::assertDispatched(function (BroadcastAction $job) use ($parent, $child) {
            $this->assertCount(1, $job->channels);
            $this->assertSame(sprintf('private-%s', $parent->broadcastChannel()), $job->channels[0]->name);
            $this->assertEquals('combined_properties_test_models', $job->target);
            $this->assertEquals('prepend', $job->action);
            $this->assertEquals('combined_properties_test_models._combined_properties_test_model', $job->partial);
            $this->assertEquals(['combinedPropertiesTestModel' => $child], $job->partialData);

            return true;
        });
    }
}

class BroadcastTestModel extends TestModel
{
    use Broadcasts;
}

class AutoBroadcastTestModel extends TestModel
{
    use Broadcasts;

    protected $broadcasts = true;
}

class AutoBroadcastWithCustomInsertsTestModel extends TestModel
{
    use Broadcasts;

    protected $broadcasts = [
        'insertsBy' => 'prepend',
    ];
}

class RelatedModelParent extends TestModel
{
}

class RelatedModelChild extends TestModel
{
    use Broadcasts;

    protected $broadcastsTo = 'parent';

    public function parent()
    {
        return $this->belongsTo(RelatedModelParent::class);
    }
}

class RelatedModelChildMethod extends TestModel
{
    use Broadcasts;

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

class BroadcastTestModelUsingChannel extends TestModel
{
    use Broadcasts;

    public static $TEST_CHANNEL;

    public function broadcastsTo()
    {
        return static::$TEST_CHANNEL ??= new Channel('testing');
    }
}

class CombinedPropertiesTestModel extends TestModel
{
    use Broadcasts;

    protected $broadcasts = ['insertsBy' => 'prepend'];
    protected $broadcastsTo = 'parent';

    public function parent()
    {
        return $this->belongsTo(RelatedModelParent::class);
    }
}
