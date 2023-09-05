<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Events;

use HotwiredLaravel\TurboLaravel\Events\TurboStreamBroadcast;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use Illuminate\Support\Facades\View;
use Workbench\Database\Factories\ArticleFactory;

class TurboStreamBroadcastTest extends TestCase
{
    /** @test */
    public function renders_turbo_stream()
    {
        $article = ArticleFactory::new()->create()->fresh();

        $event = new TurboStreamBroadcast(
            [],
            'replace',
            'article_'.$article->id,
            null,
            'articles._article',
            ['article' => $article],
        );

        $expected = View::make('turbo-laravel::turbo-stream', [
            'target' => 'article_'.$article->id,
            'action' => 'replace',
            'partial' => 'articles._article',
            'partialData' => [
                'article' => $article,
            ],
        ])->render();

        $this->assertEquals(trim($expected), trim($event->render()));
    }

    /** @test */
    public function renders_turbo_stream_targets()
    {
        $article = ArticleFactory::new()->create()->fresh();

        $event = new TurboStreamBroadcast(
            [],
            'replace',
            null,
            '.articles',
            'articles._article',
            ['article' => $article],
        );

        $expected = View::make('turbo-laravel::turbo-stream', [
            'action' => 'replace',
            'targets' => '.articles',
            'partial' => 'articles._article',
            'partialData' => [
                'article' => $article,
            ],
        ])->render();

        $this->assertEquals(trim($expected), trim($event->render()));
    }
}
