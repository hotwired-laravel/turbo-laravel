<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Http;

use HotwiredLaravel\TurboLaravel\Testing\AssertableTurboStream;
use HotwiredLaravel\TurboLaravel\Testing\InteractsWithTurbo;
use HotwiredLaravel\TurboLaravel\Testing\TurboStreamMatcher;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use Workbench\Database\Factories\ArticleFactory;

class MacroablePendingStreamTest extends TestCase
{
    use InteractsWithTurbo;

    #[\PHPUnit\Framework\Attributes\Test]
    public function turbo_stream_can_be_macroable(): void
    {
        $article = ArticleFactory::new()->create();

        $this->turbo()
            ->put(route('articles.update', $article), ['title' => 'Title Updated'])
            ->assertTurboStream(fn (AssertableTurboStream $streams): true => (
                $streams->hasTurboStream(fn (TurboStreamMatcher $stream): \HotwiredLaravel\TurboLaravel\Testing\TurboStreamMatcher => (
                    $stream->where('action', 'replace')
                        ->where('target', 'article_'.$article->id)
                        ->see('Title Updated')
                ))
                && $streams->hasTurboStream(fn (TurboStreamMatcher $stream): \HotwiredLaravel\TurboLaravel\Testing\TurboStreamMatcher => (
                    $stream->where('action', 'append')
                        ->where('target', 'notifications')
                        ->see('Article updated.')
                ))
                && $streams->has(2)
            ));
    }
}
