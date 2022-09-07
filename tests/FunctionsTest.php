<?php

namespace Tests;

use Illuminate\Support\Facades\View;
use Tonysm\TurboLaravel\Tests\TestCase;
use Tonysm\TurboLaravel\Tests\Stubs\Models\TestModel;

use function Tonysm\TurboLaravel\turbo_stream;

class FunctionsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__ . '/Stubs/views');
    }

    /** @test */
    public function turbo_streams()
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
}
