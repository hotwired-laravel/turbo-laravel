<?php

namespace Tonysm\TurboLaravel\Tests\Http;

use Illuminate\Http\Request;
use Tonysm\TurboLaravel\Tests\TestCase;

class RequestMacrosTest extends TestCase
{
    /** @test */
    public function wants_turbo_stream()
    {
        $request = Request::create('/hello');
        $this->assertFalse($request->wantsTurboStream(), 'Expected request to not want a turbo stream response, but it did.');

        $request = Request::create('/hello');
        $request->headers->add([
            'Accept' => 'text/html; turbo-stream, text/html, application/xhtml+xml',
        ]);
        $this->assertTrue($request->wantsTurboStream(), 'Expected request to want a turbo stream response, but it did not.');
    }
}
