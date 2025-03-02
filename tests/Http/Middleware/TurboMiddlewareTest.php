<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Http\Middleware;

use HotwiredLaravel\TurboLaravel\Facades\Turbo as TurboFacade;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use HotwiredLaravel\TurboLaravel\Turbo;
use Workbench\App\Models\Article;
use Workbench\Database\Factories\ArticleFactory;
use Workbench\Database\Factories\CommentFactory;

class TurboMiddlewareTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function doesnt_change_redirect_response_when_not_turbo_visit(): void
    {
        $this->from('/articles')
            ->post('/articles', [])
            ->assertRedirect('/articles')
            ->assertStatus(302);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function handles_invalid_forms_with_an_internal_redirect(): void
    {
        $this->from('/articles')
            ->post('/articles', headers: [
                'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
            ])
            ->assertSee('New Article')
            ->assertSee('The title field is required.')
            ->assertStatus(422);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function handles_invalid_forms_with_an_internal_redirect_when_using_form_requests(): void
    {
        $article = Article::create(['title' => 'Hello World']);

        $this
            ->from("/articles/{$article->id}")
            ->post(route('articles.comments.store', $article), headers: [
                'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
            ])
            ->assertSee('New Comment')
            ->assertSee('The content field is required.')
            ->assertStatus(422);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_detect_hotwire_native_visits(): void
    {
        ArticleFactory::new()->times(3)->create();

        $this->assertFalse(
            TurboFacade::isTurboNativeVisit(),
            'Expected to not have started saying it is a Hotwire Native visit, but it said it is.'
        );

        $this->assertFalse(
            TurboFacade::isHotwireNativeVisit(),
            'Expected to not have started saying it is a Hotwire Native visit, but it said it is.'
        );

        $this->get('/articles', [
            'User-Agent' => 'Turbo Native Android',
        ])->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'content', 'created_at', 'updated_at'],
            ],
        ]);

        $this->get('/articles', [
            'User-Agent' => 'Hotwire Native Android',
        ])->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'content', 'created_at', 'updated_at'],
            ],
        ]);

        $this->assertTrue(
            TurboFacade::isTurboNativeVisit(),
            'Expected to have detected a Hotwire Native visit, but it did not.'
        );

        $this->assertTrue(
            TurboFacade::isHotwireNativeVisit(),
            'Expected to have detected a Hotwire Native visit, but it did not.'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function uses_the_redirect_to_when_guessed_route_doesnt_exist(): void
    {
        $comment = CommentFactory::new()->create();

        // There's no route named `comments.edit`, so it redirects "back".

        $this->from(route('articles.show', $comment->article))
            ->put(route('comments.update', $comment), headers: [
                'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
            ])
            ->assertSee($comment->article->title, escape: false)
            ->assertUnprocessable();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_prevent_redirect_route(): void
    {
        config()->set('turbo-laravel.redirect_guessing_exceptions', [
            '/articles*',
        ]);

        $this->from(route('articles.index'))
            ->post(route('articles.store'), headers: [
                'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
            ])
            ->assertRedirectToRoute('articles.index');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function sends_an_internal_redirect_to_resource_create_routes_on_failed_validation_follows_laravel_conventions_and_returns_422_status_code(): void
    {
        $this
            ->from(route('articles.index'))
            ->post(route('articles.store'), headers: [
                'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
            ])
            ->assertSee('New Article')
            ->assertSee('The title field is required.')
            ->assertUnprocessable();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function redirects_back_to_resource_edit_routes_on_failed_validation_follows_laravel_conventions(): void
    {
        $article = ArticleFactory::new()->create();

        $this->from(route('articles.index'))
            ->put(route('articles.update', $article), headers: [
                'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
            ])
            ->assertSee('Edit Article')
            ->assertSee('The title field is required.')
            ->assertUnprocessable();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function redirects_include_query_params(): void
    {
        $article = ArticleFactory::new()->create();

        $this->from(route('articles.index'))
            ->put(route('articles.update', ['article' => $article, 'frame' => 'lorem']), headers: [
                'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
            ])
            ->assertSee('Edit Article')
            ->assertSee('The title field is required.')
            ->assertSee('Showing frame: lorem.')
            ->assertUnprocessable();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function only_guess_route_on_resource_routes(): void
    {
        $this->from(route('login'))
            ->post(route('login.store'), headers: [
                'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
            ])
            ->assertRedirectToRoute('login')
            ->assertStatus(303);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function passes_the_request_cookies_to_the_internal_request(): void
    {
        $article = ArticleFactory::new()->create();

        $this
            ->withCookie('my-cookie', 'test-value')
            ->delete(route('articles.destroy', $article), headers: [
                'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
            ])
            ->assertSee('Delete Article')
            ->assertSee('My cookie: test-value.')
            ->assertSee('Response cookie: response-cookie-value.')
            ->assertUnprocessable();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function sets_turbo_tracking_request_id(): void
    {
        $this->get('request-id')
            ->assertJson(['turbo_request_id' => null]);

        $this->withHeader('X-Turbo-Request-Id', '123')
            ->get('request-id')
            ->assertJson(['turbo_request_id' => '123']);
    }
}
