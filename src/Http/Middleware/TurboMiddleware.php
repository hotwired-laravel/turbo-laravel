<?php

namespace Tonysm\TurboLaravel\Http\Middleware;

use Closure;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Facade;
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

        // When throwing a ValidationException and the app uses named routes convention, we can guess
        // the form route for the current endpoint, make an internal request there, and return the
        // response body with the form over a 422 status code, which is better for Turbo Native.

        if ($response->exception instanceof ValidationException && ($formRedirectUrl = $this->getRedirectUrl($request, $response))) {
            $response->setTargetUrl($formRedirectUrl);

            return tap($this->handleRedirectInternally($this->kernel(), $request, $response), function () use ($request) {
                App::instance('request', $request);
                Facade::clearResolvedInstance('request');
            });
        }

        return $response->setStatusCode(303);
    }

    private function getRedirectUrl($request, $response)
    {
        if ($response->exception->redirectTo) {
            return $response->exception->redirectTo;
        }

        return $this->guessFormRedirectUrl($request);
    }

    private function kernel(): Kernel
    {
        return App::make(Kernel::class);
    }

    /**
     * @param Kernel $kernel
     * @param Request $request
     * @param Response $response
     *
     * @return Response
     */
    private function handleRedirectInternally($kernel, $request, $response)
    {
        do {
            $response = $kernel->handle(
                $request = $this->createRequestFrom($response->headers->get('Location'), $request)
            );
        } while ($response->isRedirect());

        return $response->setStatusCode(422);
    }

    private function createRequestFrom(string $url, Request $baseRequest)
    {
        $request = Request::create($url, 'GET');

        $request->headers->replace($baseRequest->headers->all());

        return $request;
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
    private function guessFormRedirectUrl($request)
    {
        $route = $request->route();
        $name = optional($route)->getName();

        if (! $route || ! $name) {
            return null;
        }

        $formRouteName = $this->redirectGuesser->guess($name);

        // If the guessed route doesn't exist, send it back to wherever Laravel defaults to.

        if (! $formRouteName || ! Route::has($formRouteName)) {
            return null;
        }

        return route($formRouteName, $route->parameters() + request()->query());
    }
}
