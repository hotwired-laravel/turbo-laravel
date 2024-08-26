<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Http;

use HotwiredLaravel\TurboLaravel\Testing\AssertableTurboStream;
use HotwiredLaravel\TurboLaravel\Testing\InteractsWithTurbo;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use Workbench\Database\Factories\ArticleFactory;

class TurboStreamResponseTest extends TestCase
{
    use InteractsWithTurbo;

    /** @test */
    public function turbo_stream_response()
    {
        $article = ArticleFactory::new()->create();

        $this->turbo()
            ->post(route('articles.comments.store', $article), [
                'content' => 'Hello World',
            ])
            ->assertTurboStream();
    }

    /** @test */
    public function not_turbo_response()
    {
        $article = ArticleFactory::new()->create();

        $this->post(route('articles.comments.store', $article), [
            'content' => 'Hello World',
        ])
            ->assertNotTurboStream();
    }

    /** @test */
    public function turbo_assert_count_of_turbo_streams()
    {
        $article = ArticleFactory::new()->create();

        $this->turbo()
            ->post(route('articles.comments.store', $article), ['content' => 'Hello World'])
            ->assertTurboStream(fn (AssertableTurboStream $turboStream) => (
                $turboStream->has(2)
            ));
    }

    /** @test */
    public function turbo_assert_has_turbo_stream()
    {
        $article = ArticleFactory::new()->create();

        $this->turbo()
            ->post(route('articles.comments.store', $article), ['content' => 'Hello World'])
            ->assertTurboStream(fn (AssertableTurboStream $turboStreams) => (
                $turboStreams->has(2)
                && $turboStreams->hasTurboStream(fn ($turboStream) => (
                    $turboStream->where('target', 'comments')
                        ->where('action', 'append')
                        ->see('Hello World')
                ))
                && $turboStreams->hasTurboStream(fn ($turboStream) => (
                    $turboStream->where('target', 'notifications')
                        ->where('action', 'append')
                        ->see('Comment created.')
                ))
            ));
    }

    /** @test */
    public function turbo_allows_custom_actions_with_no_view()
    {
        $this->assertEquals(
            <<<'HTML'
            <turbo-stream target="contact-edit-drawer" action="close-drawer">
            </turbo-stream>

            HTML,
            (string) turbo_stream()->action('close-drawer')->target('contact-edit-drawer'),
        );

        $this->assertEquals(
            <<<'HTML'
            <turbo-stream target="contact-edit-drawer" action="close-drawer">
            </turbo-stream>

            HTML,
            turbo_stream()->action('close-drawer')->target('contact-edit-drawer')->toResponse(request())->getContent(),
        );
    }
}
