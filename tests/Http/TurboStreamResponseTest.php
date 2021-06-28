<?php

namespace Tonysm\TurboLaravel\Tests\Http;

use Illuminate\Support\Facades\View;
use Tonysm\TurboLaravel\Tests\TestCase;

class TurboStreamResponseTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__ . '/../Stubs/views');
    }

    protected function defineRoutes($router)
    {
        $router->get('/testing/turbo-stream', function () {
            return response()->turboStreamView('turbo_streams');
        })->name('testing.turbo-stream');

        $router->get('/testing/non-turbo-stream', function () {
            return 'No Turbo Stream';
        })->name('testing.non-turbo-stream');
    }

    /** @test */
    public function turbo_stream_response()
    {
        $this->get(route('testing.turbo-stream'))
            ->assertTurboStream();

        $this->get(route('testing.non-turbo-stream'))
            ->assertNotTurboStream();
    }
}
