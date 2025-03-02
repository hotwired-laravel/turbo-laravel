<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Http;

use HotwiredLaravel\TurboLaravel\Testing\AssertableTurboStream;
use HotwiredLaravel\TurboLaravel\Testing\InteractsWithTurbo;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use Workbench\Database\Factories\ArticleFactory;

class TurboStreamResponseTest extends TestCase
{
    use InteractsWithTurbo;

    #[\PHPUnit\Framework\Attributes\Test]
    public function turbo_stream_response(): void
    {
        $article = ArticleFactory::new()->create();

        $this->turbo()
            ->post(route('articles.comments.store', $article), [
                'content' => 'Hello World',
            ])
            ->assertTurboStream();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function not_turbo_response(): void
    {
        $article = ArticleFactory::new()->create();

        $this->post(route('articles.comments.store', $article), [
            'content' => 'Hello World',
        ])
            ->assertNotTurboStream();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function turbo_assert_count_of_turbo_streams(): void
    {
        $article = ArticleFactory::new()->create();

        $this->turbo()
            ->post(route('articles.comments.store', $article), ['content' => 'Hello World'])
            ->assertTurboStream(fn (AssertableTurboStream $turboStream): \HotwiredLaravel\TurboLaravel\Testing\AssertableTurboStream => (
                $turboStream->has(2)
            ));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function turbo_assert_has_turbo_stream(): void
    {
        $article = ArticleFactory::new()->create();

        $this->turbo()
            ->post(route('articles.comments.store', $article), ['content' => 'Hello World'])
            ->assertTurboStream(fn (AssertableTurboStream $turboStreams): true => (
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function turbo_allows_custom_actions_with_no_view(): void
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
