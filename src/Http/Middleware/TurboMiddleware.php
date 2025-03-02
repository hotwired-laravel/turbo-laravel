<?php

namespace HotwiredLaravel\TurboLaravel\Http\Middleware;

use Closure;
use HotwiredLaravel\TurboLaravel\Facades\Turbo as TurboFacade;
use HotwiredLaravel\TurboLaravel\Turbo;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Cookie;

class TurboMiddleware
{
    /**
     * Encrypted cookies to be added to the internal requests following redirects.
     */
    private array $encryptedCookies;

    /**
     * The URIs that should be excluded from the route guessing behavior.
     *
     * @var array<int, string>
     */
    private array $except = [];

    public function __construct()
    {
        $this->except = config('turbo-laravel.redirect_guessing_exceptions', []);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return RedirectResponse|mixed
     */
    public function handle($request, Closure $next)
    {
        $this->encryptedCookies = $request->cookies->all();

        if ($this->hotwireNativeVisit($request)) {
            TurboFacade::setVisitingFromHotwireNative();
        }

        if ($requestId = $request->header('X-Turbo-Request-Id', null)) {
            TurboFacade::setTurboTrackingRequestId($requestId);
        }

        return $this->turboResponse($next($request), $request);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    private function hotwireNativeVisit($request): bool
    {
        return Str::contains($request->userAgent(), ['Hotwire Native', 'Turbo Native']);
    }

    /**
     * @param  mixed  $next
     * @return RedirectResponse|mixed
     */
    private function turboResponse($response, Request $request)
    {
        if (! $this->turboVisit($request) && ! $this->hotwireNativeVisit($request)) {
            return $response;
        }

        if (! $response instanceof RedirectResponse) {
            return $response;
        }

        // We get the response's encrypted cookies and merge them with the
        // encrypted cookies of the first request to make sure that are
        // sub-sequent request will use the most up-to-date values.

        $responseCookies = collect($response->headers->getCookies())
            ->mapWithKeys(fn (Cookie $cookie) => [$cookie->getName() => $cookie->getValue()])
            ->all();

        $this->encryptedCookies = array_replace_recursive($this->encryptedCookies, $responseCookies);

        // When throwing a ValidationException and the app uses named routes convention, we can guess
        // the form route for the current endpoint, make an internal request there, and return the
        // response body with the form over a 422 status code (works better for Hotwire Native).

        if ($response->exception instanceof ValidationException && ($formRedirectUrl = $this->guessFormRedirectUrl($request, $response->exception->redirectTo))) {
            $response->setTargetUrl($formRedirectUrl);

            return tap($this->handleRedirectInternally($request, $response), function () use ($request): void {
                App::instance('request', $request);
                Facade::clearResolvedInstance('request');
            });
        }

        return $response->setStatusCode(303);
    }

    private function kernel(): Kernel
    {
        return App::make(Kernel::class);
    }

    /**
     * @param  Response  $response
     * @return Response
     */
    private function handleRedirectInternally(\Illuminate\Http\Request $request, \Illuminate\Http\RedirectResponse $response)
    {
        $kernel = $this->kernel();

        do {
            $response = $kernel->handle(
                $request = $this->createRequestFrom($response->headers->get('Location'), $request)
            );
        } while ($response->isRedirect());

        if ($response->isOk()) {
            $response->setStatusCode(422);
        }

        return $response;
    }

    private function createRequestFrom(string $url, Request $baseRequest)
    {
        $request = Request::create($url, 'GET');

        $request->headers->replace($baseRequest->headers->all());
        $request->cookies->replace($this->encryptedCookies);

        return $request;
    }

    /**
     * @return bool
     */
    private function turboVisit(\Illuminate\Http\Request $request)
    {
        return Str::contains($request->header('Accept', ''), Turbo::TURBO_STREAM_FORMAT);
    }

    private function guessFormRedirectUrl(\Illuminate\Http\Request $request, ?string $defaultRedirectUrl = null)
    {
        if ($this->inExceptArray($request)) {
            return $defaultRedirectUrl;
        }

        $route = $request->route();
        $name = optional($route)->getName();

        if (! $route || ! $name) {
            return $defaultRedirectUrl;
        }

        $formRouteName = $this->guessRouteName($name);

        // If the guessed route doesn't exist, send it back to wherever Laravel defaults to.

        if (! $formRouteName || ! Route::has($formRouteName)) {
            return $defaultRedirectUrl;
        }

        return route($formRouteName, $route->parameters() + request()->query());
    }

    protected function guessRouteName(string $routeName): ?string
    {
        if (! Str::endsWith($routeName, ['.store', '.update', '.destroy'])) {
            return null;
        }

        return str_replace(['.store', '.update', '.destroy'], ['.create', '.edit', '.delete'], $routeName);
    }

    protected function inExceptArray(Request $request): bool
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
