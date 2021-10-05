<?php

namespace Tonysm\TurboLaravel\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tonysm\TurboLaravel\Facades\Turbo as TurboFacade;
use Tonysm\TurboLaravel\Turbo;

class TurboMiddleware
{
    /** @var \Tonysm\TurboLaravel\Http\Middleware\RouteRedirectGuesser */
    private $redirectGuesser;

    public function __construct(RouteRedirectGuesser $redirectGuesser)
    {
        $this->redirectGuesser = $redirectGuesser;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return RedirectResponse|mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->turboNativeVisit($request)) {
            TurboFacade::setVisitingFromTurboNative();
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

        if (! $response instanceof RedirectResponse) {
            return $response;
        }

        // When dealing with invalid form responses, instead of flashing the
        // errors and inputs to then redirect to the form page, we'll send
        // an internal request to render the page with a 422 status code.

        $formRoute = $this->guessRedirectingRoute($request);

        if ($response->exception instanceof ValidationException && ! $response->exception->redirectTo && $formRoute) {
            return $this->dispatchInternalRequest($request, $formRoute);
        }

        // Turbo expects a 303 redirect. We are also changing the default behavior of Laravel's failed
        // validation redirection to send the user to a page where the form of the current resource
        // is rendered (instead of just "back"), since Frames could have been used in many pages.

        $response->setStatusCode(303);

        return $response;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param string $route
     * @return Response
     */
    private function dispatchInternalRequest($request, $route)
    {
        return Route::dispatch(Request::create(
            $route,
            'GET',
            [],
            $request->cookies->all(),
            $request->files->all(),
            $request->headers->all(),
        ))->setStatusCode(422);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    private function turboVisit($request)
    {
        return Str::contains($request->header('Accept', ''), Turbo::TURBO_STREAM_FORMAT);
    }

    /**
     * @param \Illuminate\Http\Request $request
     */
    private function guessRedirectingRoute($request)
    {
        $route = $request->route();
        $name = optional($route)->getName();

        if (! $route || ! $name) {
            return null;
        }

        $formRouteName = $this->redirectGuesser->guess($name);

        // If the guessed route doesn't exist, send it back to wherever Laravel defaults to.

        if (! Route::has($formRouteName)) {
            return null;
        }

        return route($formRouteName, $route->parameters() + request()->query());
    }
}
