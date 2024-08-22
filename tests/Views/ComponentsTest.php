<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Views;

use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use HotwiredLaravel\TurboLaravel\Views\Components\RefreshesWith;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\View\ViewException;
use Workbench\Database\Factories\ArticleFactory;

class ComponentsTest extends TestCase
{
    use InteractsWithViews;

    /** @test */
    public function frames()
    {
        // With all the attributes...
        $this->blade('<x-turbo::frame id="todos" :src="url(\'somewhere\')" loading="lazy" target="_top" class="block" data-controller="test" />', [])
            ->assertSee('<turbo-frame', false)
            ->assertSee('id="todos"', false)
            ->assertSee(sprintf(' src="%s"', url('somewhere')), false)
            ->assertSee('loading="lazy"', false)
            ->assertSee('target="_top"', false)
            ->assertSee('class="block"', false)
            ->assertSee('data-controller="test"', false)
            ->assertSee('</turbo-frame>', false);

        // Passing a model...
        $this->blade('<x-turbo::frame :id="$model" :src="url(\'somewhere\')" loading="lazy" target="_top" class="block" data-controller="test" />', [
            'model' => $article = ArticleFactory::new()->create(),
        ])
            ->assertSee('<turbo-frame', false)
            ->assertSee('id="article_'.$article->id.'"', false)
            ->assertSee(sprintf(' src="%s"', url('somewhere')), false)
            ->assertSee('loading="lazy"', false)
            ->assertSee('target="_top"', false)
            ->assertSee('class="block"', false)
            ->assertSee('data-controller="test"', false)
            ->assertSee('</turbo-frame>', false);

        // Passing a model and prefix...
        $this->blade('<x-turbo::frame :id="[$model, \'comments\']" :src="url(\'somewhere\')" loading="lazy" target="_top" class="block" data-controller="test" />', [
            'model' => $article = ArticleFactory::new()->create(),
        ])
            ->assertSee('<turbo-frame', false)
            ->assertSee('id="comments_article_'.$article->id.'"', false)
            ->assertSee(sprintf(' src="%s"', url('somewhere')), false)
            ->assertSee('loading="lazy"', false)
            ->assertSee('target="_top"', false)
            ->assertSee('class="block"', false)
            ->assertSee('data-controller="test"', false)
            ->assertSee('</turbo-frame>', false);

        // With only the required attributes...
        $this->blade('<x-turbo::frame id="todos" />', [])
            ->assertSee('<turbo-frame', false)
            ->assertSee('id="todos"', false)
            ->assertDontSee(sprintf(' src="%s"', url('somewhere')), false)
            ->assertDontSee('loading="lazy"', false)
            ->assertDontSee('target="_top"', false)
            ->assertSee('</turbo-frame>', false);
    }

    /** @test */
    public function streams()
    {
        $this->blade(<<<'BLADE'
            <x-turbo::stream target="todos" action="append">
                <p>Hello, World</p>
            </x-turbo::stream>
            BLADE)
            ->assertSee('<turbo-stream', false)
            ->assertSee('target="todos"', false)
            ->assertSee('action="append"', false)
            ->assertSee('<template><p>Hello, World</p></template>', false)
            ->assertSee('</turbo-stream>', false)
            ->assertDontSee('targets=');

        $this->blade(<<<'BLADE'
            <x-turbo::stream :target="[$model, 'comments']" action="append">
                <p>Hello, World</p>
            </x-turbo::stream>
            BLADE, ['model' => $article = ArticleFactory::new()->create()])
            ->assertSee('<turbo-stream', false)
            ->assertSee('target="comments_article_'.$article->id.'"', false)
            ->assertSee('action="append"', false)
            ->assertSee('<template><p>Hello, World</p></template>', false)
            ->assertSee('</turbo-stream>', false)
            ->assertDontSee('targets=');

        // Stream content is ignore when action is set to "remove"...
        $this->blade(<<<'BLADE'
            <x-turbo::stream :target="$model" action="remove">
                <p>Hello, World</p>
            </x-turbo::stream>
            BLADE, ['model' => $article = ArticleFactory::new()->create()])
            ->assertSee('<turbo-stream', false)
            ->assertSee('target="article_'.$article->id.'"', false)
            ->assertSee('action="remove"', false)
            ->assertDontSee('<template>', false)
            ->assertDontSee('</template>', false)
            ->assertDontSee('<p>Hello, World</p>', false)
            ->assertSee('</turbo-stream>', false)
            ->assertDontSee('targets=');

        $this->blade(<<<'BLADE'
            <x-turbo::stream targets=".todos" action="append" >
                <p>Hello, World</p>
            </x-turbo::stream>
            BLADE)
            ->assertSee('<turbo-stream', false)
            ->assertSee('targets=".todos"', false)
            ->assertSee('action="append"', false)
            ->assertSee('<template><p>Hello, World</p></template>', false)
            ->assertSee('</turbo-stream>', false)
            ->assertDontSee('target=');

        // Morphs...
        $this->blade(<<<'BLADE'
            <x-turbo::stream target="article_123" action="update" method="morph">
                <p>Hello, World</p>
            </x-turbo::stream>
            BLADE)
            ->assertSee('method="morph"', false);
    }

    /** @test */
    public function stream_from()
    {
        $this->blade('<x-turbo::stream-from :source="$model" />', [
            'model' => $article = ArticleFactory::new()->create(),
        ])->assertSee('<turbo-echo-stream-source channel="Workbench.App.Models.Article.'.$article->id.'" type="private" ></turbo-echo-stream-source>', false);

        $this->blade('<x-turbo::stream-from :source="$model" type="public" />', [
            'model' => $article,
        ])->assertSee('<turbo-echo-stream-source channel="Workbench.App.Models.Article.'.$article->id.'" type="public" ></turbo-echo-stream-source>', false);
    }

    /** @test */
    public function stream_target_targets_should_throw_exception()
    {
        $this->expectException(ViewException::class);

        $this->blade(<<<'BLADE'
        <x-turbo::stream target="todo" targets=".todos" action="append" >
            <p>Hello, World</p>
        </x-turbo::stream>
        BLADE);
    }

    /** @test */
    public function stream_null_target_targets_should_throw_exception()
    {
        $this->expectException(ViewException::class);

        $this->blade(<<<'BLADE'
        <x-turbo::stream action="append" >
            <p>Hello, World</p>
        </x-turbo::stream>
        BLADE);
    }

    /** @test */
    public function allows_custom_actions_with_extra_attributes()
    {
        $this->blade(<<<'BLADE'
            <x-turbo::stream action="console_log" hello="world">
            </x-turbo::stream>
            BLADE)
            ->assertSee('<turbo-stream', false)
            ->assertSee('action="console_log"', false)
            ->assertSee('hello="world"', false)
            ->assertSee('</turbo-stream>', false)
            ->assertDontSee('<template></template>', false)
            ->assertDontSee('targets=', false)
            ->assertDontSee('target=', false);
    }

    /** @test */
    public function refresh_strategy()
    {
        foreach (['replace', 'morph'] as $method) {
            foreach (['reset', 'preserve'] as $scroll) {
                $this->blade(<<<'BLADE'
                    <x-turbo::refreshes-with :method="$method" :scroll="$scroll" />
                BLADE, ['method' => $method, 'scroll' => $scroll])
                    ->assertSee('<meta name="turbo-refresh-method" content="'.$method.'">', false)
                    ->assertSee('<meta name="turbo-refresh-scroll" content="'.$scroll.'">', false);
            }
        }
    }

    /** @test */
    public function invalid_refresh_method()
    {
        $this->expectException(ViewException::class);
        $this->expectExceptionMessage('Invalid refresh method given "invalid". Allowed values are: replace or morph.');

        $this->blade(<<<'BLADE'
            <x-turbo::refreshes-with :method="$method" :scroll="$scroll" />
        BLADE, ['method' => 'invalid', 'scroll' => RefreshesWith::DEFAULT_SCROLL]);
    }

    /** @test */
    public function invalid_refresh_scroll()
    {
        $this->expectException(ViewException::class);
        $this->expectExceptionMessage('Invalid refresh scroll given "invalid". Allowed values are: reset or preserve.');

        $this->blade(<<<'BLADE'
            <x-turbo::refreshes-with :method="$method" :scroll="$scroll" />
        BLADE, ['method' => RefreshesWith::DEFAULT_METHOD, 'scroll' => 'invalid']);
    }

    /** @test */
    public function turbo_drive_components()
    {
        $this->blade(
            <<<'BLADE'
            <x-turbo::exempts-page-from-cache />
            <x-turbo::exempts-page-from-preview />
            <x-turbo::page-requires-reload />
            <x-turbo::page-view-transition />
            BLADE)
            ->assertSee('<meta name="turbo-cache-control" content="no-cache">', false)
            ->assertSee('<meta name="turbo-cache-control" content="no-preview">', false)
            ->assertSee('<meta name="turbo-visit-control" content="reload">', false)
            ->assertSee('<meta name="view-transition" content="same-origin" />', false);
    }
}
