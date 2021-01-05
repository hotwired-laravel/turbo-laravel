<?php

namespace Tonysm\TurboLaravel\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tonysm\TurboLaravel\NamesResolver;
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
        if (!$this->turboVisit($request) && !$this->turboNativeVisit($request)) {
            return $response;
        }

        if (!$response instanceof RedirectResponse) {
            return $response;
        }

        // Turbo expects a 303 redirect status code.
        $response->setStatusCode(303);

        if ($response->exception instanceof ValidationException && !$response->exception->redirectTo) {
            $response->setTargetUrl(
                $this->guessRedirectingRoute($request) ?: $response->getTargetUrl()
            );
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
     */
    private function guessRedirectingRoute($request)
    {
        $route = $request->route();
        $name = optional($route)->getName();

        if (!$route || !$name) {
            return null;
        }

        $formRouteName = NamesResolver::formRouteNameFor($name);

        if (!Route::has($formRouteName)) {
            // @TODO: Not sure if we should just silently fail here or if we should throw another exception.
            return null;
        }

        return route($formRouteName, $route->parameters());
    }
}
