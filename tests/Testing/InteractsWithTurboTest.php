<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Http;

use HotwiredLaravel\TurboLaravel\Testing\InteractsWithTurbo;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use Workbench\Database\Factories\ArticleFactory;

class InteractsWithTurboTest extends TestCase
{
    use InteractsWithTurbo;

    /** @test */
    public function was_from_turbo_frame_works_when_no_frame_set()
    {
        $article = ArticleFactory::new()->create();

        $this->get(route('articles.comments.create', $article))
            ->assertOk()
            ->assertSee('Turbo Frame: no-frame.')
            ->assertSee('Was From Turbo Frame: No.')
            ->assertSee('Was From Create Article Comment Turbo Frame: No.')
            ->assertSee('Was From Other Turbo Frame: No.');

        $this
            ->fromTurboFrame($frame = dom_id($article, 'create_comment'))
            ->get(route('articles.comments.create', $article))
            ->assertOk()
            ->assertSee(sprintf('Turbo Frame: %s.', $frame))
            ->assertSee('Was From Turbo Frame: Yes.')
            ->assertSee('Was From Create Article Comment Turbo Frame: Yes.')
            ->assertSee('Was From Other Turbo Frame: No.');
    }
}
