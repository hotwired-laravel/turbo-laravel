<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Http;

use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use HotwiredLaravel\TurboLaravel\Testing\AssertableTurboStream;
use HotwiredLaravel\TurboLaravel\Testing\InteractsWithTurbo;
use HotwiredLaravel\TurboLaravel\Testing\TurboStreamMatcher;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use Illuminate\Support\Facades\View;

class MacroablePendingStreamTest extends TestCase
{
    use InteractsWithTurbo;

    public function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__.'/../Stubs/views');
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
