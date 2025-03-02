<?php

namespace Tests;

use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use HotwiredLaravel\TurboLaravel\Turbo;
use Illuminate\Support\Facades\View;
use Workbench\App\Models\Article;

use function HotwiredLaravel\TurboLaravel\dom_class;
use function HotwiredLaravel\TurboLaravel\dom_id;
use function HotwiredLaravel\TurboLaravel\turbo_stream;
use function HotwiredLaravel\TurboLaravel\turbo_stream_view;

class FunctionsTest extends TestCase
{
    private Article $article;

    protected function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__.'/Stubs/views');

        $this->article = Article::create(['title' => 'Hello World']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function namespaced_turbo_stream_fn(): void
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

        $expected = trim(view('articles._article', [
            'article' => $this->article,
        ])->render());

        $this->assertEquals(
            trim(<<<HTML
            <turbo-stream target="articles" action="append">
                <template>{$expected}</template>
            </turbo-stream>
            HTML),
            trim(turbo_stream($this->article)),
        );

        $expected = trim(view('articles._article', [
            'article' => $this->article,
        ])->render());

        $this->assertEquals(
            trim(<<<HTML
            <turbo-stream target="article_{$this->article->id}" action="replace" method="morph">
                <template>{$expected}</template>
            </turbo-stream>
            HTML),
            trim(turbo_stream($this->article->fresh())->morph()),
        );

        // Unsets method
        $expected = trim(view('articles._article', [
            'article' => $this->article,
        ])->render());

        $this->assertEquals(
            trim(<<<HTML
            <turbo-stream target="article_{$this->article->id}" action="replace">
                <template>{$expected}</template>
            </turbo-stream>
            HTML),
            trim(turbo_stream($this->article->fresh())->morph()->method()),
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function global_turbo_stream_fn(): void
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

        $expected = trim(view('articles._article', [
            'article' => $this->article,
        ])->render());

        $this->assertEquals(
            trim(<<<HTML
            <turbo-stream target="articles" action="append">
                <template>{$expected}</template>
            </turbo-stream>
            HTML),
            trim(\turbo_stream($this->article)),
        );

        $expected = trim(view('articles._article', [
            'article' => $this->article,
        ])->render());

        $this->assertEquals(
            trim(<<<HTML
            <turbo-stream target="article_{$this->article->id}" action="replace" method="morph">
                <template>{$expected}</template>
            </turbo-stream>
            HTML),
            trim(\turbo_stream($this->article->fresh())->morph()),
        );

        // Unsets method
        $expected = trim(view('articles._article', [
            'article' => $this->article,
        ])->render());

        $this->assertEquals(
            trim(<<<HTML
            <turbo-stream target="article_{$this->article->id}" action="replace">
                <template>{$expected}</template>
            </turbo-stream>
            HTML),
            trim(\turbo_stream($this->article->fresh())->morph()->method()),
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function namespace_turbo_stream_htmlable(): void
    {
        $this->assertEquals(
            trim(<<<'HTML'
            <turbo-stream target="posts" action="append">
                <template>Hello World</template>
            </turbo-stream>

            <turbo-stream target="post_123" action="remove">
            </turbo-stream>
            HTML),
            trim(View::make('functions.turbo_stream_ns_fn_htmlable_multiple')->render())
        );

        $expected = trim(view('articles._article', [
            'article' => $this->article,
        ])->render());

        $this->assertEquals(
            trim(<<<HTML
            <turbo-stream target="articles" action="append">
                <template>{$expected}</template>
            </turbo-stream>
            HTML),
            trim(View::make('functions.turbo_stream_ns_fn_htmlable_model', [
                'model' => $this->article,
            ])->render())
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function global_turbo_stream_htmlable(): void
    {
        $this->assertEquals(
            trim(<<<'HTML'
            <turbo-stream target="posts" action="append">
                <template>Hello World</template>
            </turbo-stream>

            <turbo-stream target="post_123" action="remove">
            </turbo-stream>
            HTML),
            trim(View::make('functions.turbo_stream_global_fn_htmlable_multiple')->render())
        );

        $expected = trim(view('articles._article', [
            'article' => $this->article,
        ])->render());

        $this->assertEquals(
            trim(<<<HTML
            <turbo-stream target="articles" action="append">
                <template>{$expected}</template>
            </turbo-stream>
            HTML),
            trim(View::make('functions.turbo_stream_global_fn_htmlable_model', [
                'model' => $this->article,
            ])->render())
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function namespaced_dom_id_fn(): void
    {
        $this->assertEquals("article_{$this->article->id}", dom_id($this->article));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function global_dom_id_fn(): void
    {
        $this->assertEquals("article_{$this->article->id}", \dom_id($this->article));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function namespaced_dom_class_fn(): void
    {
        $this->assertEquals('article', dom_class($this->article));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function global_dom_class_fn(): void
    {
        $this->assertEquals('article', \dom_class($this->article));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function namespaced_turbo_stream_view_fn(): void
    {
        $response = turbo_stream_view('functions.turbo_stream_view', [
            'title' => 'Post Using Namespaced Function',
        ]);

        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
        $this->assertEquals(
            view('functions.turbo_stream_view', ['title' => 'Post Using Namespaced Function'])->render(),
            $response->content(),
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function global_turbo_stream_view_fn(): void
    {
        $response = \turbo_stream_view('functions.turbo_stream_view', [
            'title' => 'Post Global',
        ]);

        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
        $this->assertEquals(
            view('functions.turbo_stream_view', ['title' => 'Post Global'])->render(),
            $response->content(),
        );
    }
}
