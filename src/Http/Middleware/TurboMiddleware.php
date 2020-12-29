<?php

namespace Tonysm\TurboLaravel\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tonysm\TurboLaravel\TurboLaravelFacade;

class TurboMiddleware
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return RedirectResponse|mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->isTurboNativeVisit($request)) {
            TurboLaravelFacade::setVisitingFromTurboNative();
        }

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
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    private function turboVisit($request)
    {
        return Str::contains($request->header('Accept', ''), 'turbo-stream');
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    private function isTurboNativeVisit($request): bool
    {
        return Str::contains($request->userAgent(), 'Turbo Native');
    }
}
