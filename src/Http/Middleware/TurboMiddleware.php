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
        if ($this->turboNativeVisit($request)) {
            TurboLaravelFacade::setVisitingFromTurboNative();
        }

        return $this->turboResponse($next($request), $request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    private function turboNativeVisit($request): bool
    {
        return Str::contains($request->userAgent(), 'Turbo Native');
    }

    /**
     * @param mixed $next
     * @param Request $request
     * @return RedirectResponse|mixed
     */
    private function turboResponse($response, Request $request)
    {
        if (! $this->turboVisit($request) && ! $this->turboNativeVisit($request)) {
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
}
