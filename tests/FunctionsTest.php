<?php

namespace Tests;

use Illuminate\Support\Facades\View;
use function Tonysm\TurboLaravel\dom_class;
use function Tonysm\TurboLaravel\dom_id;

use Tonysm\TurboLaravel\Tests\Stubs\Models\TestModel;
use Tonysm\TurboLaravel\Tests\TestCase;
use function Tonysm\TurboLaravel\turbo_stream;

class FunctionsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__ . '/Stubs/views');
    }

    /** @test */
    public function namespaced_turbo_stream_fn()
    {
        $this->assertEquals(
            trim(<<<HTML
            <turbo-stream target="posts" action="append">
                <template>Hello World</template>
            </turbo-stream>
            HTML),
            trim(turbo_stream()->append('posts', 'Hello World')),
        );

        $this->assertEquals(
            trim(<<<HTML
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
            trim(<<<HTML
            <turbo-stream target="posts" action="append">
                <template>Hello World</template>
            </turbo-stream>
            HTML),
            trim(\turbo_stream()->append('posts', 'Hello World')),
        );

        $this->assertEquals(
            trim(<<<HTML
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
            trim(<<<HTML
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
            trim(<<<HTML
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

        $this->assertEquals("test_model", dom_class($testModel));
    }

    /** @test */
    public function global_dom_class_fn()
    {
        $testModel = TestModel::create(['name' => 'Hello']);

        $this->assertEquals("test_model", \dom_class($testModel));
    }
}
