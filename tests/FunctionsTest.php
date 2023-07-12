<?php

namespace Tests;

use function HotwiredLaravel\TurboLaravel\dom_class;
use function HotwiredLaravel\TurboLaravel\dom_id;
use HotwiredLaravel\TurboLaravel\Tests\Stubs\Models\TestModel;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use HotwiredLaravel\TurboLaravel\Turbo;
use function HotwiredLaravel\TurboLaravel\turbo_stream;
use function HotwiredLaravel\TurboLaravel\turbo_stream_view;
use Illuminate\Support\Facades\View;

class FunctionsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__.'/Stubs/views');
    }

    /** @test */
    public function namespaced_turbo_stream_fn()
    {
        $this->assertEquals(
            trim(<<<'HTML'
            <turbo-stream target="posts" action="append">
                <template>Hello World</template>
            </turbo-stream>
            HTML),
            trim(turbo_stream()->append('posts', 'Hello World')),
        );

        $this->assertEquals(
            trim(<<<'HTML'
            <turbo-stream target="posts" action="append">
                <template>Hello World</template>
            </turbo-stream>

            <turbo-stream target="post_123" action="remove">
            </turbo-stream>
            HTML),
            trim(turbo_stream([
                turbo_stream()->append('posts', 'Hello World'),
                turbo_stream()->remove('post_123'),
            ])),
        );

        $testModel = TestModel::create(['name' => 'Hello']);
        $expected = trim(view('test_models._test_model', [
            'testModel' => $testModel,
        ])->render());

        $this->assertEquals(
            trim(<<<HTML
            <turbo-stream target="test_models" action="append">
                <template>{$expected}</template>
            </turbo-stream>
            HTML),
            trim(turbo_stream($testModel)),
        );
    }

    /** @test */
    public function global_turbo_stream_fn()
    {
        $this->assertEquals(
            trim(<<<'HTML'
            <turbo-stream target="posts" action="append">
                <template>Hello World</template>
            </turbo-stream>
            HTML),
            trim(\turbo_stream()->append('posts', 'Hello World')),
        );

        $this->assertEquals(
            trim(<<<'HTML'
            <turbo-stream target="posts" action="append">
                <template>Hello World</template>
            </turbo-stream>

            <turbo-stream target="post_123" action="remove">
            </turbo-stream>
            HTML),
            trim(\turbo_stream([
                \turbo_stream()->append('posts', 'Hello World'),
                \turbo_stream()->remove('post_123'),
            ])),
        );

        $testModel = TestModel::create(['name' => 'Hello']);
        $expected = trim(view('test_models._test_model', [
            'testModel' => $testModel,
        ])->render());

        $this->assertEquals(
            trim(<<<HTML
            <turbo-stream target="test_models" action="append">
                <template>{$expected}</template>
            </turbo-stream>
            HTML),
            trim(\turbo_stream($testModel)),
        );
    }

    /** @test */
    public function namespace_turbo_stream_htmlable()
    {
        $testModel = TestModel::create(['name' => 'Hello']);
        $expected = trim(view('test_models._test_model', [
            'testModel' => $testModel,
        ])->render());

        $this->assertEquals(
            trim(<<<'HTML'
            <turbo-stream target="posts" action="append">
                <template>Hello World</template>
            </turbo-stream>

            <turbo-stream target="post_123" action="remove">
            </turbo-stream>
            HTML),
            trim(View::make('turbo_stream_global_htmlable_multiple')->render())
        );

        $this->assertEquals(
            trim(<<<HTML
            <turbo-stream target="test_models" action="append">
                <template>{$expected}</template>
            </turbo-stream>
            HTML),
            trim(View::make('turbo_stream_global_htmlable_model', [
                'testModel' => $testModel,
            ])->render())
        );
    }

    /** @test */
    public function global_turbo_stream_htmlable()
    {
        $testModel = TestModel::create(['name' => 'Hello']);
        $expected = trim(view('test_models._test_model', [
            'testModel' => $testModel,
        ])->render());

        $this->assertEquals(
            trim(<<<'HTML'
            <turbo-stream target="posts" action="append">
                <template>Hello World</template>
            </turbo-stream>

            <turbo-stream target="post_123" action="remove">
            </turbo-stream>
            HTML),
            trim(View::make('turbo_stream_global_htmlable_multiple')->render())
        );

        $this->assertEquals(
            trim(<<<HTML
            <turbo-stream target="test_models" action="append">
                <template>{$expected}</template>
            </turbo-stream>
            HTML),
            trim(View::make('turbo_stream_global_htmlable_model', [
                'testModel' => $testModel,
            ])->render())
        );
    }

    /** @test */
    public function namespaced_dom_id_fn()
    {
        $testModel = TestModel::create(['name' => 'Hello']);

        $this->assertEquals("test_model_{$testModel->id}", dom_id($testModel));
    }

    /** @test */
    public function global_dom_id_fn()
    {
        $testModel = TestModel::create(['name' => 'Hello']);

        $this->assertEquals("test_model_{$testModel->id}", \dom_id($testModel));
    }

    /** @test */
    public function namespaced_dom_class_fn()
    {
        $testModel = TestModel::create(['name' => 'Hello']);

        $this->assertEquals('test_model', dom_class($testModel));
    }

    /** @test */
    public function global_dom_class_fn()
    {
        $testModel = TestModel::create(['name' => 'Hello']);

        $this->assertEquals('test_model', \dom_class($testModel));
    }

    /** @test */
    public function namespaced_turbo_stream_view_fn()
    {
        $response = turbo_stream_view('turbo_stream_view_namespaced', [
            'title' => 'Post Namespaced',
        ]);

        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
        $this->assertEquals(
            view('turbo_stream_view_namespaced', ['title' => 'Post Namespaced'])->render(),
            $response->content(),
        );
    }

    /** @test */
    public function global_turbo_stream_view_fn()
    {
        $response = \turbo_stream_view('turbo_stream_view_global', [
            'title' => 'Post Global',
        ]);

        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
        $this->assertEquals(
            view('turbo_stream_view_global', ['title' => 'Post Global'])->render(),
            $response->content(),
        );
    }
}
