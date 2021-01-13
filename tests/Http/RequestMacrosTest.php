<?php

namespace Tonysm\TurboLaravel\Tests\Http;

use Illuminate\Http\Request;
use Tonysm\TurboLaravel\Tests\TestCase;
use Tonysm\TurboLaravel\Turbo;

class RequestMacrosTest extends TestCase
{
    /** @test */
    public function wants_turbo_stream()
    {
        $request = Request::create('/hello');
        $this->assertFalse($request->wantsTurboStream(), 'Expected request to not want a turbo stream response, but it did.');

        $request = Request::create('/hello');
        $request->headers->add([
            'Accept' => Turbo::TURBO_STREAM_FORMAT.', text/html, application/xhtml+xml',
        ]);
        $this->assertTrue($request->wantsTurboStream(), 'Expected request to want a turbo stream response, but it did not.');
    }
}
