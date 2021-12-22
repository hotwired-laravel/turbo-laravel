<?php

namespace Tonysm\TurboLaravel\Tests\Views;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Tonysm\TurboLaravel\Tests\Stubs\Models\TestModel;
use Tonysm\TurboLaravel\Tests\TestCase;

class ComponentsTest extends TestCase
{
    use InteractsWithViews;

    /** @test */
    public function frames()
    {
        // With all the attributes...
        $this->blade('<x-turbo-frame id="todos" :src="url(\'somewhere\')" loading="lazy" target="_top" class="block" data-controller="test" />', [])
            ->assertSee('<turbo-frame', false)
            ->assertSee('id="todos"', false)
            ->assertSee(sprintf(' src="%s"', url('somewhere')), false)
            ->assertSee('loading="lazy"', false)
            ->assertSee('target="_top"', false)
            ->assertSee('class="block"', false)
            ->assertSee('data-controller="test"', false)
            ->assertSee('</turbo-frame>', false);

        // Passing a model...
        $this->blade('<x-turbo-frame :id="$model" :src="url(\'somewhere\')" loading="lazy" target="_top" class="block" data-controller="test" />', [
            'model' => new TestModel(['id' => 123]),
        ])
            ->assertSee('<turbo-frame', false)
            ->assertSee('id="test_model_123"', false)
            ->assertSee(sprintf(' src="%s"', url('somewhere')), false)
            ->assertSee('loading="lazy"', false)
            ->assertSee('target="_top"', false)
            ->assertSee('class="block"', false)
            ->assertSee('data-controller="test"', false)
            ->assertSee('</turbo-frame>', false);

        // Passing a model and prefix...
        $this->blade('<x-turbo-frame :id="[$model, \'comments\']" :src="url(\'somewhere\')" loading="lazy" target="_top" class="block" data-controller="test" />', [
            'model' => new TestModel(['id' => 123]),
        ])
            ->assertSee('<turbo-frame', false)
            ->assertSee('id="comments_test_model_123"', false)
            ->assertSee(sprintf(' src="%s"', url('somewhere')), false)
            ->assertSee('loading="lazy"', false)
            ->assertSee('target="_top"', false)
            ->assertSee('class="block"', false)
            ->assertSee('data-controller="test"', false)
            ->assertSee('</turbo-frame>', false);

        // With only the required attributes...
        $this->blade('<x-turbo-frame id="todos" />', [])
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
        $this->blade(<<<BLADE
            <x-turbo-stream target="todos" action="append">
                <p>Hello, World</p>
            </x-turbo-stream>
            BLADE)
            ->assertSee('<turbo-stream', false)
            ->assertSee('target="todos"', false)
            ->assertSee('action="append"', false)
            ->assertSee('<template><p>Hello, World</p></template>', false)
            ->assertSee('</turbo-stream>', false);

        $this->blade(<<<'BLADE'
            <x-turbo-stream :target="[$model, 'comments']" action="append">
                <p>Hello, World</p>
            </x-turbo-stream>
            BLADE, ['model' => new TestModel(['id' => 123])])
            ->assertSee('<turbo-stream', false)
            ->assertSee('target="comments_test_model_123"', false)
            ->assertSee('action="append"', false)
            ->assertSee('<template><p>Hello, World</p></template>', false)
            ->assertSee('</turbo-stream>', false);

        // Stream content is ignore when action is set to "remove"...
        $this->blade(<<<'BLADE'
            <x-turbo-stream :target="$model" action="remove">
                <p>Hello, World</p>
            </x-turbo-stream>
            BLADE, ['model' => new TestModel(['id' => 123])])
            ->assertSee('<turbo-stream', false)
            ->assertSee('target="test_model_123"', false)
            ->assertSee('action="remove"', false)
            ->assertDontSee('<template>', false)
            ->assertDontSee('</template>', false)
            ->assertDontSee('<p>Hello, World</p>', false)
            ->assertSee('</turbo-stream>', false);
    }

    /** @test */
    public function stream_from()
    {
        $this->blade('<x-turbo-stream-from :source="$model" />', [
                'model' => new TestModel(['id' => 123]),
            ])
            ->assertSee('<turbo-echo-stream-source channel="Tonysm.TurboLaravel.Tests.Stubs.Models.TestModel.123" type="private" ></turbo-echo-stream-source>', false);

        $this->blade('<x-turbo-stream-from :source="$model" type="public" />', [
                'model' => new TestModel(['id' => 123]),
            ])
            ->assertSee('<turbo-echo-stream-source channel="Tonysm.TurboLaravel.Tests.Stubs.Models.TestModel.123" type="public" ></turbo-echo-stream-source>', false);
    }
}
