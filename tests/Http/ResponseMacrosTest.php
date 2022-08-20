<?php

namespace Tonysm\TurboLaravel\Tests\Http;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;

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
    }

    /** @test */
    public function streams_model_on_create()
    {
        $testModel = TestModel::create(['name' => 'test']);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'append',
            'target' => 'test_models',
            'partial' => 'test_models._test_model',
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

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'append',
            'target' => 'broadcast_test_models',
            'partial' => 'broadcast_test_models._broadcast_test_model',
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

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'replace',
            'target' => dom_id($testModel),
            'partial' => 'test_models._test_model',
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

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'replace',
            'target' => dom_id($testModel),
            'partial' => 'broadcast_test_models._broadcast_test_model',
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

        $expected = view('turbo-laravel::turbo-stream', [
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

        $expected = view('turbo-laravel::turbo-stream', [
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

        $expected = view('turbo-laravel::turbo-stream', [
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

        $resp = response()->turboStreamView(View::file(__DIR__ . '/../Stubs/views/test_models/_test_model.blade.php', [
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

        $resp = response()->turboStreamView('test_models._test_model', [
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

        $expected = view('turbo-laravel::turbo-stream', [
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

        $expected = view('turbo-laravel::turbo-stream', [
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

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'append',
            'target' => 'test_models',
            'partial' => 'test_models._test_model',
            'partialData' => ['testModel' => $testModel],
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function append_shorthand_passing_string()
    {
        $response = response()
            ->turboStream()
            ->append('some_dom_id', 'Hello World')
            ->toResponse(new Request());

        $expected = <<<HTML
        <turbo-stream target="some_dom_id" action="append">
            <template>Hello World</template>
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function append_shorthand_passing_html_string()
    {
        $response = response()
            ->turboStream()
            ->append('some_dom_id', new HtmlString('<div>Hello, Tester</div>'))
            ->toResponse(new Request);

        $expected = <<<HTML
        <turbo-stream target="some_dom_id" action="append">
            <template><div>Hello, Tester</div></template>
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function append_shorthand_passing_string_with_view_partial()
    {
        $testModel = TestModel::create(['name' => 'Test model']);

        $response = response()
            ->turboStream()
            ->append('test_models_target')
            ->view('test_models._test_model', ['testModel' => $testModel])
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'append',
            'target' => 'test_models_target',
            'partial' => 'test_models._test_model',
            'partialData' => ['testModel' => $testModel],
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function append_shorthand_passing_as_string_and_view_as_content()
    {
        $response = response()
            ->turboStream()
            ->append('some_dom_id', view('hello_view', ['name' => 'Tester']))
            ->toResponse(new Request);

        $expected = <<<HTML
        <turbo-stream target="some_dom_id" action="append">
            <template><div>Hello, Tester</div></template>
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function append_all_with_inline_content_string()
    {
        $response = response()
            ->turboStream()
            ->appendAll('.test_models', 'Some inline content')
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'append',
            'targets' => '.test_models',
            'content' => 'Some inline content',
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function append_all_passing_html_safe_string()
    {
        $response = response()
            ->turboStream()
            ->appendAll('.test_models', new HtmlString('<div>Some safe HTML content</div>'))
            ->toResponse(new Request);

        $expected = <<<HTML
        <turbo-stream targets=".test_models" action="append">
            <template><div>Some safe HTML content</div></template>
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function append_all_passing_view_as_content()
    {
        $response = response()
            ->turboStream()
            ->appendAll('.test_models', view('hello_view', ['name' => 'Tester']))
            ->toResponse(new Request);

        $expected = <<<HTML
        <turbo-stream targets=".test_models" action="append">
            <template><div>Hello, Tester</div></template>
        </turbo-stream>
        HTML;

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

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'prepend',
            'target' => 'test_models',
            'partial' => 'test_models._test_model',
            'partialData' => ['testModel' => $testModel],
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function prepend_shorthand_as_string()
    {
        $response = response()
            ->turboStream()
            ->prepend('test_models', 'Hello World')
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'prepend',
            'target' => 'test_models',
            'content' => 'Hello World',
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function prepend_all_with_inline_content_string()
    {
        $response = response()
            ->turboStream()
            ->prependAll('.test_models', 'Some inline content')
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'prepend',
            'targets' => '.test_models',
            'content' => 'Some inline content',
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function prepend_all_passing_html_safe_string()
    {
        $response = response()
            ->turboStream()
            ->prependAll('.test_models', new HtmlString('<div>Some safe HTML content</div>'))
            ->toResponse(new Request);

        $expected = <<<HTML
        <turbo-stream targets=".test_models" action="prepend">
            <template><div>Some safe HTML content</div></template>
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function prepend_all_passing_view_as_content()
    {
        $response = response()
            ->turboStream()
            ->prependAll('.test_models', view('hello_view', ['name' => 'Tester']))
            ->toResponse(new Request);

        $expected = <<<HTML
        <turbo-stream targets=".test_models" action="prepend">
            <template><div>Hello, Tester</div></template>
        </turbo-stream>
        HTML;

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

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'update',
            'target' => dom_id($testModel),
            'partial' => 'test_models._test_model',
            'partialData' => ['testModel' => $testModel],
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function update_shorthand_as_string()
    {
        $response = response()
            ->turboStream()
            ->update('test_models_target', 'Hello World')
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'update',
            'target' => 'test_models_target',
            'content' => 'Hello World',
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function update_all_with_inline_content_string()
    {
        $response = response()
            ->turboStream()
            ->updateAll('.test_models', 'Some inline content')
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'update',
            'targets' => '.test_models',
            'content' => 'Some inline content',
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function update_all_passing_html_safe_string()
    {
        $response = response()
            ->turboStream()
            ->updateAll('.test_models', new HtmlString('<div>Some safe HTML content</div>'))
            ->toResponse(new Request);

        $expected = <<<HTML
        <turbo-stream targets=".test_models" action="update">
            <template><div>Some safe HTML content</div></template>
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function update_all_passing_view_as_content()
    {
        $response = response()
            ->turboStream()
            ->updateAll('.test_models', view('hello_view', ['name' => 'Tester']))
            ->toResponse(new Request);

        $expected = <<<HTML
        <turbo-stream targets=".test_models" action="update">
            <template><div>Hello, Tester</div></template>
        </turbo-stream>
        HTML;

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

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'replace',
            'target' => dom_id($testModel),
            'partial' => 'test_models._test_model',
            'partialData' => ['testModel' => $testModel],
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function replace_shorthand_as_string()
    {
        $response = response()
            ->turboStream()
            ->replace('test_models_target', 'Hello World')
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'replace',
            'target' => 'test_models_target',
            'content' => 'Hello World',
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function replace_all_with_inline_content_string()
    {
        $response = response()
            ->turboStream()
            ->replaceAll('.test_models', 'Some inline content')
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'replace',
            'targets' => '.test_models',
            'content' => 'Some inline content',
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function replace_all_passing_html_safe_string()
    {
        $response = response()
            ->turboStream()
            ->replaceAll('.test_models', new HtmlString('<div>Some safe HTML content</div>'))
            ->toResponse(new Request);

        $expected = <<<HTML
        <turbo-stream targets=".test_models" action="replace">
            <template><div>Some safe HTML content</div></template>
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function replace_all_passing_view_as_content()
    {
        $response = response()
            ->turboStream()
            ->replaceAll('.test_models', view('hello_view', ['name' => 'Tester']))
            ->toResponse(new Request);

        $expected = <<<HTML
        <turbo-stream targets=".test_models" action="replace">
            <template><div>Hello, Tester</div></template>
        </turbo-stream>
        HTML;

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

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'remove',
            'target' => dom_id($testModel),
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function remove_shorthand_as_string()
    {
        $response = response()
            ->turboStream()
            ->remove('test_models_target')
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'remove',
            'target' => 'test_models_target',
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function remove_shorthand_accepts_string()
    {
        $response = response()
            ->turboStream()
            ->remove('target_dom_id')
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'remove',
            'target' => 'target_dom_id',
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function remove_all()
    {
        $response = response()
            ->turboStream()
            ->removeAll('.test_models')
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'remove',
            'targets' => '.test_models',
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function before_shorthand()
    {
        $response = response()
            ->turboStream()
            ->before($testModel = TestModel::create(['name' => 'Test model']))
            ->view('test_models._test_model', ['testModel' => $testModel])
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'before',
            'target' => dom_id($testModel),
            'partial' => 'test_models._test_model',
            'partialData' => ['testModel' => $testModel],
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function before_shorthand_as_string()
    {
        $response = response()
            ->turboStream()
            ->before('some_dom_id', 'Hello World')
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'before',
            'target' => 'some_dom_id',
            'content' => 'Hello World',
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function before_shorthand_passing_as_string_target_and_view_as_content()
    {
        $response = response()
            ->turboStream()
            ->before('some_dom_id', view('hello_view', ['name' => 'Tester']))
            ->toResponse(new Request);

        $expected = <<<HTML
        <turbo-stream target="some_dom_id" action="before">
            <template><div>Hello, Tester</div></template>
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function after_shorthand()
    {
        $response = response()
            ->turboStream()
            ->after($testModel = TestModel::create(['name' => 'Test model']))
            ->view('test_models._test_model', ['testModel' => $testModel])
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'after',
            'target' => dom_id($testModel),
            'partial' => 'test_models._test_model',
            'partialData' => ['testModel' => $testModel],
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function after_shorthand_as_string()
    {
        $response = response()
            ->turboStream()
            ->after('some_dom_id', 'Hello World')
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'after',
            'target' => 'some_dom_id',
            'content' => 'Hello World',
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function targets()
    {
        $response = response()
            ->turboStream()
            ->action('remove')
            ->targets('.some_dom_class')
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'remove',
            'targets' => '.some_dom_class',
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function builds_multiple_turbo_stream_responses()
    {
        $model = TestModel::create(['name' => 'Test model']);

        $response = response()->turboStream([
            response()->turboStream()->append($model)->target('append-target-id'),
            response()->turboStream()->prepend($model)->target('prepend-target-id'),
            response()->turboStream()->remove($model)->target('remove-target-id'),
        ])->toResponse(new Request());

        $expected = collect([
            view('turbo-laravel::turbo-stream', [
                'action' => 'append',
                'target' => 'append-target-id',
                'partial' => 'test_models._test_model',
                'partialData' => ['testModel' => $model],
            ])->render(),
            view('turbo-laravel::turbo-stream', [
                'action' => 'prepend',
                'target' => 'prepend-target-id',
                'partial' => 'test_models._test_model',
                'partialData' => ['testModel' => $model],
            ])->render(),
            view('turbo-laravel::turbo-stream', [
                'action' => 'remove',
                'target' => 'remove-target-id',
            ])->render(),
        ])->implode(PHP_EOL);

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
}

class TestModelSoftDelete extends TestModel
{
    use SoftDeletes;
}

class BroadcastTestModel extends \Tonysm\TurboLaravel\Tests\TestModel
{
    use Broadcasts;
}

class TestModelWithTurboPartial extends TestModel
{
}
