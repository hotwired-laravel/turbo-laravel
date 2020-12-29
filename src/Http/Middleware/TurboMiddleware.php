<?php

namespace Tonysm\TurboLaravel\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;

class TurboMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Turbo expects a 303 redirect status code.
        if ($response instanceof RedirectResponse) {
            $response->setStatusCode(303);
        }

        return $response;
    }
}
