<?php

namespace Tonysm\TurboLaravel\Tests\Http;

use Illuminate\Support\Facades\View;
use Tonysm\TurboLaravel\Testing\AssertableTurboStream;
use Tonysm\TurboLaravel\Testing\InteractsWithTurbo;
use Tonysm\TurboLaravel\Tests\TestCase;

class TurboStreamResponseTest extends TestCase
{
    use InteractsWithTurbo;

    public function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__ . '/../Stubs/views');
    }

    protected function defineRoutes($router)
    {
        $router->get('/testing/turbo-stream', function () {
            if (request()->wantsTurboStream()) {
                return response()->turboStreamView('turbo_streams');
            }

            return 'No Turbo Stream';
        })->name('testing.turbo-stream');
    }

    /** @test */
    public function turbo_stream_response()
    {
        $this->turbo()
            ->get(route('testing.turbo-stream'))
            ->assertTurboStream();
    }

    /** @test */
    public function not_turbo_response()
    {
        $this->get(route('testing.turbo-stream'))
            ->assertNotTurboStream();
    }

    /** @test */
    public function turbo_assert_count_of_turbo_streams()
    {
        $this->turbo()
            ->get(route('testing.turbo-stream'))
            ->assertTurboStream(fn (AssertableTurboStream $turboStream) => (
                $turboStream->has(4)
            ));
    }

    /** @test */
    public function turbo_assert_has_turbo_stream()
    {
        $this->turbo()
            ->get(route('testing.turbo-stream'))
            ->assertTurboStream(fn (AssertableTurboStream $turboStreams) => (
                $turboStreams->has(4)
                && $turboStreams->hasTurboStream(fn ($turboStream) => (
                    $turboStream->where('target', 'posts')
                                ->where('action', 'append')
                                ->see('Post Title')
                ))
                && $turboStreams->hasTurboStream(fn ($turboStream) => (
                    $turboStream->where('target', 'inline_post_123')
                                ->where('action', 'replace')
                                ->see('Inline Post Title')
                ))
                && $turboStreams->hasTurboStream(fn ($turboStream) => (
                    $turboStream->where('target', 'empty_posts')
                                ->where('action', 'remove')
                ))
                && $turboStreams->hasTurboStream(fn ($turboStream) => (
                $turboStream->where('targets', '.post')
                    ->where('action', 'replace')
                ))
            ));
    }
}
