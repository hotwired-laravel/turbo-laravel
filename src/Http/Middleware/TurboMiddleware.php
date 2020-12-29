<?php

namespace Tonysm\TurboLaravel\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TurboMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (!$this->turboVisit($request)) {
            return $response;
        }

        // Turbo expects a 303 redirect status code.
        if ($response instanceof RedirectResponse) {
            $response->setStatusCode(303);
        }

        return $response;
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function turboVisit($request)
    {
        return Str::contains($request->header('Accept', ''), 'turbo-stream');
    }
}
