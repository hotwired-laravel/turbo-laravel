<?php

namespace Tonysm\TurboLaravel\Tests\Http\Middleware;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Tonysm\TurboLaravel\Http\Middleware\TurboMiddleware;

class TurboMiddlewareTest extends TestCase
{
    /** @test */
    public function handles_redirect_responses()
    {
        $request = Request::create('/source');
        $response = new RedirectResponse('/destination');
        $next = function () use ($response) {
            return $response;
        };

        $result = (new TurboMiddleware())->handle($request, $next);

        $this->assertEquals($response->getTargetUrl(), $result->getTargetUrl());
        $this->assertEquals(303, $result->getStatusCode());
    }
}
