<?php

namespace Tonysm\TurboLaravel\Tests\Http;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use function Tonysm\TurboLaravel\dom_id;

use Tonysm\TurboLaravel\Http\PendingTurboStreamResponse;
use Tonysm\TurboLaravel\Http\TurboStreamResponseFailedException;
use Tonysm\TurboLaravel\Models\Broadcasts;
use Tonysm\TurboLaravel\Tests\TestCase;

use Tonysm\TurboLaravel\Turbo;

class ResponseMacrosTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__ . '/../Stubs/views');
        View::addLocation(__DIR__ . '/../../resources/views/');
    }

    /** @test */
    public function streams_model_on_create()
    {
        $testModel = TestModel::create(['name' => 'test']);

        $expected = view('turbo-stream', [
            'action' => 'append',
            'target' => 'test_models',
            'partial' => '_test_model',
            'partialData' => ['testModel' => $testModel],
        ])->render();

        $resp = response()->turboStream($testModel)->toResponse(new Request);

        $this->assertEquals(trim($expected), trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_broadcastable_models_for_create()
    {
        $testModel = BroadcastTestModel::withoutEvents(function () {
            return BroadcastTestModel::create(['name' => 'test']);
        });

        $expected = view('turbo-stream', [
            'action' => 'append',
            'target' => 'broadcast_test_models',
            'partial' => '_broadcast_test_model',
            'partialData' => ['broadcastTestModel' => $testModel],
        ])->render();

        $resp = response()->turboStream($testModel)->toResponse(new Request);

        $this->assertEquals(trim($expected), trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_model_on_update()
    {
        $testModel = TestModel::create(['name' => 'test'])->fresh();

        $expected = view('turbo-stream', [
            'action' => 'replace',
            'target' => dom_id($testModel),
            'partial' => '_test_model',
            'partialData' => ['testModel' => $testModel],
        ])->render();

        $resp = response()->turboStream($testModel)->toResponse(new Request);

        $this->assertEquals(trim($expected), trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_broadcastable_models_for_update()
    {
        $testModel = BroadcastTestModel::withoutEvents(function () {
            return BroadcastTestModel::create(['name' => 'test'])->fresh();
        });

        $expected = view('turbo-stream', [
            'action' => 'replace',
            'target' => dom_id($testModel),
            'partial' => $testModel->hotwirePartialName(),
            'partialData' => ['broadcastTestModel' => $testModel],
        ])->render();

        $resp = response()->turboStream($testModel)->toResponse(new Request);

        $this->assertEquals(trim($expected), trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_model_on_delete()
    {
        $testModel = tap(TestModel::create(['name' => 'test']))->delete();

        $expected = view('turbo-stream', [
            'action' => 'remove',
            'target' => dom_id($testModel),
        ])->render();

        $resp = response()->turboStream($testModel)->toResponse(new Request);

        $this->assertEquals(trim($expected), trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_model_on_soft_delete()
    {
        $testModelSoftDelete = tap(TestModelSoftDelete::create(['name' => 'test']))->delete();

        $expected = view('turbo-stream', [
            'action' => 'remove',
            'target' => dom_id($testModelSoftDelete),
        ])->render();

        $resp = response()->turboStream($testModelSoftDelete)->toResponse(new Request);

        $this->assertEquals(trim($expected), trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_broadcastable_models_for_deleted()
    {
        $testModel = BroadcastTestModel::withoutEvents(function () {
            return tap(BroadcastTestModel::create(['name' => 'test']))->delete();
        });

        $expected = view('turbo-stream', [
            'action' => 'remove',
            'target' => dom_id($testModel),
        ])->render();

        $resp = response()->turboStream($testModel)->toResponse(new Request);

        $this->assertEquals(trim($expected), trim($resp->getContent()));
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
    public function can_manually_build_turbo_stream_response()
    {
        $builder = response()->turboStream();

        $this->assertInstanceOf(PendingTurboStreamResponse::class, $builder);
        $this->assertInstanceOf(Responsable::class, $builder);
    }

    /** @test */
    public function can_configure_manually_turbo_stream_rendering()
    {
        $response = response()
            ->turboStream()
            ->target($target = 'example_target')
            ->action($action = 'replace')
            ->partial($partial = 'test_model_with_turbo_partials.turbo.created_stream', $partialData = [
                'exampleModel' => TestModel::create(['name' => 'Test model']),
            ])
            ->toResponse(new Request);

        $expected = view('turbo-stream', [
            'action' => $action,
            'target' => $target,
            'partial' => $partial,
            'partialData' => $partialData,
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function can_use_view_instead_of_partial()
    {
        $response = response()
            ->turboStream()
            ->target($target = 'example_target')
            ->action($action = 'replace')
            ->view($partial = 'test_model_with_turbo_partials.turbo.created_stream', $partialData = [
                'exampleModel' => TestModel::create(['name' => 'Test model']),
            ])
            ->toResponse(new Request);

        $expected = view('turbo-stream', [
            'action' => $action,
            'target' => $target,
            'partial' => $partial,
            'partialData' => $partialData,
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function append_shorthand_for_response_builder()
    {
        $response = response()
            ->turboStream()
            ->append($testModel = TestModel::create(['name' => 'Test model']))
            ->toResponse(new Request);

        $expected = view('turbo-stream', [
            'action' => 'append',
            'target' => 'test_models',
            'partial' => '_test_model',
            'partialData' => ['testModel' => $testModel],
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function prepend_shorthand_for_response_builder()
    {
        $response = response()
            ->turboStream()
            ->prepend($testModel = TestModel::create(['name' => 'Test model']))
            ->toResponse(new Request);

        $expected = view('turbo-stream', [
            'action' => 'prepend',
            'target' => 'test_models',
            'partial' => '_test_model',
            'partialData' => ['testModel' => $testModel],
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function update_shorthand_for_response_builder()
    {
        $response = response()
            ->turboStream()
            ->update($testModel = TestModel::create(['name' => 'Test model']))
            ->toResponse(new Request);

        $expected = view('turbo-stream', [
            'action' => 'update',
            'target' => dom_id($testModel),
            'partial' => '_test_model',
            'partialData' => ['testModel' => $testModel],
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function replace_shorthand_for_response_builder()
    {
        $response = response()
            ->turboStream()
            ->replace($testModel = TestModel::create(['name' => 'Test model']))
            ->toResponse(new Request);

        $expected = view('turbo-stream', [
            'action' => 'replace',
            'target' => dom_id($testModel),
            'partial' => '_test_model',
            'partialData' => ['testModel' => $testModel],
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function remove_shorthand_for_response_builder()
    {
        $response = response()
            ->turboStream()
            ->remove($testModel = TestModel::create(['name' => 'Test model']))
            ->toResponse(new Request);

        $expected = view('turbo-stream', [
            'action' => 'remove',
            'target' => dom_id($testModel),
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function response_builder_fails_when_partial_is_missing_and_not_a_remove_action()
    {
        $this->expectException(TurboStreamResponseFailedException::class);

        response()
            ->turboStream()
            ->target('example_target')
            ->action('replace')
            ->toResponse(new Request);
    }
}

class TestModel extends \Tonysm\TurboLaravel\Tests\TestModel
{
    public function hotwirePartialName()
    {
        return "_test_model";
    }
}

class TestModelSoftDelete extends TestModel
{
    use SoftDeletes;

    public function hotwirePartialName()
    {
        return "_test_model_soft_delete";
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
