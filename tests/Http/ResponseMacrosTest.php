<?php

namespace Tonysm\TurboLaravel\Tests\Http;

use Illuminate\Support\Facades\View;
use Tonysm\TurboLaravel\Models\Broadcasts;
use Tonysm\TurboLaravel\Tests\TestCase;
use Tonysm\TurboLaravel\Turbo;

class ResponseMacrosTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__ . '/../Stubs/views');
    }

    /** @test */
    public function streams_model_on_create()
    {
        $testModel = TestModel::create(['name' => 'test']);

        $expected = <<<html
<turbo-stream target="test_models" action="append">
    <template>
        <div id="test_model_{$testModel->getKey()}">hello</div>
    </template>
</turbo-stream>
html;

        $resp = response()->turboStream($testModel);

        $this->assertEquals($expected, trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_broadcastable_models_for_create()
    {
        $testModel = BroadcastTestModel::withoutEvents(function () {
            return BroadcastTestModel::create(['name' => 'test']);
        });

        $expected = <<<html
<turbo-stream target="broadcast_test_models" action="append">
    <template>
        <div id="broadcast_test_model_{$testModel->getKey()}">hello</div>
    </template>
</turbo-stream>
html;

        $resp = response()->turboStream($testModel);

        $this->assertEquals($expected, trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_model_on_update()
    {
        $testModel = TestModel::create(['name' => 'test'])->fresh();

        $expected = <<<html
<turbo-stream target="test_model_{$testModel->getKey()}" action="replace">
    <template>
        <div id="test_model_{$testModel->getKey()}">hello</div>
    </template>
</turbo-stream>
html;

        $resp = response()->turboStream($testModel);

        $this->assertEquals($expected, trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_broadcastable_models_for_update()
    {
        $testModel = BroadcastTestModel::withoutEvents(function () {
            return BroadcastTestModel::create(['name' => 'test'])->fresh();
        });

        $expected = <<<html
<turbo-stream target="broadcast_test_model_{$testModel->getKey()}" action="replace">
    <template>
        <div id="broadcast_test_model_{$testModel->getKey()}">hello</div>
    </template>
</turbo-stream>
html;

        $resp = response()->turboStream($testModel);

        $this->assertEquals($expected, trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_model_on_delete()
    {
        $testModel = tap(TestModel::create(['name' => 'test']))->delete();

        $expected = <<<html
<turbo-stream target="test_model_{$testModel->getKey()}" action="remove"></turbo-stream>
html;

        $resp = response()->turboStream($testModel);

        $this->assertEquals($expected, trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_broadcastable_models_for_deleted()
    {
        $testModel = BroadcastTestModel::withoutEvents(function () {
            return tap(BroadcastTestModel::create(['name' => 'test']))->delete();
        });

        $expected = <<<html
<turbo-stream target="broadcast_test_model_{$testModel->getKey()}" action="remove"></turbo-stream>
html;

        $resp = response()->turboStream($testModel);

        $this->assertEquals($expected, trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_custom_view()
    {
        $testModel = TestModel::create(['name' => 'test']);

        $expected = <<<html
<div id="test_model_{$testModel->getKey()}">hello</div>
html;

        $resp = response()->turboStreamView(View::file(__DIR__ . '/../Stubs/views/_test_model.blade.php', [
            'testModel' => $testModel,
        ]));

        $this->assertEquals($expected, trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_custom_view_with_alternative_syntax_passing_view_string_and_data()
    {
        $testModel = TestModel::create(['name' => 'test']);

        $expected = <<<html
<div id="test_model_{$testModel->getKey()}">hello</div>
html;

        $resp = response()->turboStreamView('_test_model', [
            'testModel' => $testModel,
        ]);

        $this->assertEquals($expected, trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function uses_turbo_stream_specific_views_when_they_exist()
    {
        $testModel = TestModelWithTurboPartial::create(['name' => 'test']);

        $expected = <<<'blade'
<turbo-stream target="full_control_over_targets" action="append">
    <template>
        <h1>Hello</h1>
    </template>
</turbo-stream>
blade;

        $resp = response()->turboStream($testModel);

        $this->assertEquals($expected, trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }
}

class TestModel extends \Tonysm\TurboLaravel\Tests\TestModel
{
    public function hotwirePartialName()
    {
        return "_test_model";
    }
}

class BroadcastTestModel extends \Tonysm\TurboLaravel\Tests\TestModel
{
    use Broadcasts;

    public function hotwirePartialName()
    {
        return "_broadcast_test_model";
    }
}

class TestModelWithTurboPartial extends TestModel
{
}
