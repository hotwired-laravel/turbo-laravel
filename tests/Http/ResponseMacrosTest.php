<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Http;

use HotwiredLaravel\TurboLaravel\Facades\Turbo as FacadesTurbo;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use HotwiredLaravel\TurboLaravel\Http\TurboStreamResponseFailedException;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use HotwiredLaravel\TurboLaravel\Turbo;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Workbench\App\Models\Article;
use Workbench\App\Models\Comment;
use Workbench\App\Models\User\Profile;
use Workbench\Database\Factories\ArticleFactory;
use Workbench\Database\Factories\CommentFactory;
use Workbench\Database\Factories\ProfileFactory;

use function HotwiredLaravel\TurboLaravel\dom_id;

class ResponseMacrosTest extends TestCase
{
    /** @test */
    public function streams_model_on_create()
    {
        $article = ArticleFactory::new()->create();

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'append',
            'target' => 'articles',
            'partial' => 'articles._article',
            'partialData' => ['article' => $article],
        ])->render();

        $resp = response()->turboStream($article)->toResponse(new Request);

        $this->assertEquals(trim($expected), trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_broadcastable_models_for_create()
    {
        $comment = Comment::withoutEvents(function () {
            return CommentFactory::new()->create();
        });

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'append',
            'target' => 'comments',
            'partial' => 'comments._comment',
            'partialData' => ['comment' => $comment],
        ])->render();

        $resp = response()->turboStream($comment)->toResponse(new Request);

        $this->assertEquals(trim($expected), trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_model_on_update()
    {
        $userProfile = Profile::withoutEvents(function () {
            return ProfileFactory::new()->create()->fresh();
        });

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'replace',
            'target' => dom_id($userProfile),
            'partial' => 'user_profiles._profile',
            'partialData' => ['userProfile' => $userProfile],
        ])->render();

        $resp = response()->turboStream($userProfile)->toResponse(new Request);

        $this->assertEquals(trim($expected), trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_broadcastable_models_for_update()
    {
        $comment = Comment::withoutEvents(function () {
            return CommentFactory::new()->create()->fresh();
        });

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'replace',
            'target' => dom_id($comment),
            'partial' => 'comments._comment',
            'partialData' => ['comment' => $comment],
        ])->render();

        $resp = response()->turboStream($comment)->toResponse(new Request);

        $this->assertEquals(trim($expected), trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_model_on_delete()
    {
        $article = Article::withoutEvents(function () {
            return tap(ArticleFactory::new()->create()->fresh())->delete();
        });

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'remove',
            'target' => dom_id($article),
        ])->render();

        $resp = response()->turboStream($article)->toResponse(new Request);

        $this->assertEquals(trim($expected), trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_model_on_soft_delete()
    {
        $userProfile = Profile::withoutEvents(function () {
            return tap(ProfileFactory::new()->create()->fresh())->delete();
        });

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'remove',
            'target' => dom_id($userProfile),
        ])->render();

        $resp = response()->turboStream($userProfile)->toResponse(new Request);

        $this->assertEquals(trim($expected), trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_broadcastable_models_for_deleted()
    {
        $comment = Comment::withoutEvents(function () {
            return tap(CommentFactory::new()->create()->fresh())->delete();
        });

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'remove',
            'target' => dom_id($comment),
        ])->render();

        $resp = response()->turboStream($comment)->toResponse(new Request);

        $this->assertEquals(trim($expected), trim($resp->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_custom_view()
    {
        $article = ArticleFactory::new()->create();

        $expected = trim(view('articles.turbo.created', [
            'article' => $article,
        ])->render());

        $resp = response()->turboStreamView('articles.turbo.created', [
            'article' => $article,
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
            ->target($target = 'articles')
            ->action($action = 'append')
            ->partial($partial = 'articles._article', $partialData = [
                'article' => ArticleFactory::new()->create(),
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
            ->target($target = 'articles')
            ->action($action = 'append')
            ->view($partial = 'articles._article', $partialData = [
                'article' => ArticleFactory::new()->create(),
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
            ->append($article = ArticleFactory::new()->create())
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'append',
            'target' => 'articles',
            'partial' => 'articles._article',
            'partialData' => ['article' => $article],
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

        $expected = <<<'HTML'
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

        $expected = <<<'HTML'
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
        $article = ArticleFactory::new()->create();

        $response = response()
            ->turboStream()
            ->append('article_cards')
            ->view('articles._article_card', ['article' => $article])
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'append',
            'target' => 'article_cards',
            'partial' => 'articles._article_card',
            'partialData' => ['article' => $article],
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function append_shorthand_passing_as_string_and_view_as_content()
    {
        $response = response()
            ->turboStream()
            ->append('some_dom_id', view('articles._hello', ['name' => 'Tester']))
            ->toResponse(new Request);

        $expected = <<<'HTML'
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

        $expected = <<<'HTML'
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
            ->appendAll('.test_models', view('articles._hello', ['name' => 'Tester']))
            ->toResponse(new Request);

        $expected = <<<'HTML'
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
            ->prepend($article = ArticleFactory::new()->create())
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'prepend',
            'target' => 'articles',
            'partial' => 'articles._article',
            'partialData' => ['article' => $article],
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

        $expected = <<<'HTML'
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
            ->prependAll('.test_models', view('articles._hello', ['name' => 'Tester']))
            ->toResponse(new Request);

        $expected = <<<'HTML'
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
            ->update($article = ArticleFactory::new()->create())
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'update',
            'target' => dom_id($article),
            'partial' => 'articles._article',
            'partialData' => ['article' => $article],
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

        $expected = <<<'HTML'
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
            ->updateAll('.test_models', view('articles._hello', ['name' => 'Tester']))
            ->toResponse(new Request);

        $expected = <<<'HTML'
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
            ->replace($article = ArticleFactory::new()->create())
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'replace',
            'target' => dom_id($article),
            'partial' => 'articles._article',
            'partialData' => ['article' => $article],
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

        $expected = <<<'HTML'
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
            ->replaceAll('.test_models', view('articles._hello', ['name' => 'Tester']))
            ->toResponse(new Request);

        $expected = <<<'HTML'
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
            ->remove($article = ArticleFactory::new()->create())
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'remove',
            'target' => dom_id($article),
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
            ->before($article = ArticleFactory::new()->create())
            ->view('articles._article', ['article' => $article])
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'before',
            'target' => dom_id($article),
            'partial' => 'articles._article',
            'partialData' => ['article' => $article],
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
            ->before('some_dom_id', view('articles._hello', ['name' => 'Tester']))
            ->toResponse(new Request);

        $expected = <<<'HTML'
        <turbo-stream target="some_dom_id" action="before">
            <template><div>Hello, Tester</div></template>
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function before_all_with_inline_content_string()
    {
        $response = response()
            ->turboStream()
            ->beforeAll('.test_models', 'Some inline content')
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'before',
            'targets' => '.test_models',
            'content' => 'Some inline content',
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function before_all_passing_html_safe_string()
    {
        $response = response()
            ->turboStream()
            ->beforeAll('.test_models', new HtmlString('<div>Some safe HTML content</div>'))
            ->toResponse(new Request);

        $expected = <<<'HTML'
        <turbo-stream targets=".test_models" action="before">
            <template><div>Some safe HTML content</div></template>
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function before_all_passing_view_as_content()
    {
        $response = response()
            ->turboStream()
            ->beforeAll('.test_models', view('articles._hello', ['name' => 'Tester']))
            ->toResponse(new Request);

        $expected = <<<'HTML'
        <turbo-stream targets=".test_models" action="before">
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
            ->after($article = ArticleFactory::new()->create())
            ->view('articles._article', ['article' => $article])
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'after',
            'target' => dom_id($article),
            'partial' => 'articles._article',
            'partialData' => ['article' => $article],
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
    public function after_all_with_inline_content_string()
    {
        $response = response()
            ->turboStream()
            ->afterAll('.test_models', 'Some inline content')
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'after',
            'targets' => '.test_models',
            'content' => 'Some inline content',
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function after_all_passing_html_safe_string()
    {
        $response = response()
            ->turboStream()
            ->afterAll('.test_models', new HtmlString('<div>Some safe HTML content</div>'))
            ->toResponse(new Request);

        $expected = <<<'HTML'
        <turbo-stream targets=".test_models" action="after">
            <template><div>Some safe HTML content</div></template>
        </turbo-stream>
        HTML;

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function after_all_passing_view_as_content()
    {
        $response = response()
            ->turboStream()
            ->afterAll('.test_models', view('articles._hello', ['name' => 'Tester']))
            ->toResponse(new Request);

        $expected = <<<'HTML'
        <turbo-stream targets=".test_models" action="after">
            <template><div>Hello, Tester</div></template>
        </turbo-stream>
        HTML;

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
        $article = ArticleFactory::new()->create();

        $response = response()->turboStream([
            response()->turboStream()->append($article)->target('append-target-id'),
            response()->turboStream()->prepend($article)->target('prepend-target-id'),
            response()->turboStream()->remove($article)->target('remove-target-id'),
        ])->toResponse(new Request());

        $expected = collect([
            view('turbo-laravel::turbo-stream', [
                'action' => 'append',
                'target' => 'append-target-id',
                'partial' => 'articles._article',
                'partialData' => ['article' => $article],
            ])->render(),
            view('turbo-laravel::turbo-stream', [
                'action' => 'prepend',
                'target' => 'prepend-target-id',
                'partial' => 'articles._article',
                'partialData' => ['article' => $article],
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

    /** @test */
    public function response_builder_doesnt_fail_when_partial_is_empty_string()
    {
        $response = response()
            ->turboStream()
            ->update('example_target', '')
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'update',
            'target' => 'example_target',
            'content' => '',
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
    }

    /** @test */
    public function refresh_shorthand()
    {
        $response = response()
            ->turboStream()
            ->refresh()
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'refresh',
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }

    /** @test */
    public function refresh_shorthand_with_request_id()
    {
        FacadesTurbo::setTurboTrackingRequestId('123');

        $response = response()
            ->turboStream()
            ->refresh()
            ->toResponse(new Request);

        $expected = view('turbo-laravel::turbo-stream', [
            'action' => 'refresh',
            'attrs' => [
                'request-id' => '123',
            ],
        ])->render();

        $this->assertEquals(trim($expected), trim($response->getContent()));
        $this->assertEquals(Turbo::TURBO_STREAM_FORMAT, $response->headers->get('Content-Type'));
    }
}
