<?php

namespace Tonysm\TurboLaravel\Tests\Http;

use Illuminate\Support\Facades\View;
use Tonysm\TurboLaravel\Http\PendingTurboStreamResponse;
use Tonysm\TurboLaravel\Testing\AssertableTurboStream;
use Tonysm\TurboLaravel\Testing\InteractsWithTurbo;
use Tonysm\TurboLaravel\Testing\TurboStreamMatcher;
use Tonysm\TurboLaravel\Tests\TestCase;

class MacroablePendingStreamTest extends TestCase
{
    use InteractsWithTurbo;

    public function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__ . '/../Stubs/views');
    }

    protected function defineRoutes($router)
    {
        $router->get('/testing/macroable-streams', function () {
            if (request()->wantsTurboStream()) {
                return turbo_stream()->flash('Hello World');
            }

            return 'No Turbo Stream';
        })->name('testing.macroable-streams');
    }

    /** @test */
    public function turbo_stream_can_be_macroable()
    {
        PendingTurboStreamResponse::macro('flash', function (string $message) {
            return $this->append('notifications', view('notification', [
                'message' => $message,
            ]));
        });

        $this->turbo()
            ->get(route('testing.macroable-streams'))
            ->assertTurboStream(fn (AssertableTurboStream $streams) => (
                $streams->hasTurboStream(fn (TurboStreamMatcher $stream) => (
                    $stream->where('action', 'append')
                        ->where('target', 'notifications')
                        ->see('Hello World')
                ))
            ));
    }
}
