<?php

namespace Tonysm\TurboLaravel\Tests\Http\Middleware;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Tonysm\TurboLaravel\Http\Middleware\TurboMiddleware;
use Tonysm\TurboLaravel\Tests\TestCase;
use Tonysm\TurboLaravel\Tests\TestModel;
use Tonysm\TurboLaravel\TurboLaravelFacade;

class TurboMiddlewareTest extends TestCase
{
    /** @test */
    public function doesnt_change_response_when_not_turbo_visit()
    {
        $request = Request::create('/source');
        $request->headers->add([
            'Accept' => 'text/html;',
        ]);
        $response = new RedirectResponse('/destination');
        $next = function () use ($response) {
            return $response;
        };

        $result = (new TurboMiddleware())->handle($request, $next);

        $this->assertEquals($response->getTargetUrl(), $result->getTargetUrl());
        $this->assertEquals(302, $result->getStatusCode());
    }

    /** @test */
    public function handles_redirect_responses()
    {
        $request = Request::create('/source');
        $request->headers->add([
            'Accept' => 'text/html; turbo-stream, text/html, application/xhtml+xml',
        ]);
        $response = new RedirectResponse('/destination');
        $next = function () use ($response) {
            return $response;
        };

        $result = (new TurboMiddleware())->handle($request, $next);

        $this->assertEquals($response->getTargetUrl(), $result->getTargetUrl());
        $this->assertEquals(303, $result->getStatusCode());
    }

    /** @test */
    public function can_detect_turbo_native_visits()
    {
        $this->assertFalse(
            TurboLaravelFacade::isTurboNativeVisit(),
            'Expected to not have started saying it is a Turbo Native visit, but it said it is.'
        );

        $request = Request::create('/source');
        $request->headers->add([
            'User-Agent' => 'Turbo Native Android',
        ]);
        $next = function () {
        };

        (new TurboMiddleware())->handle($request, $next);

        $this->assertTrue(
            TurboLaravelFacade::isTurboNativeVisit(),
            'Expected to have detected a Turbo Native visit, but it did not.'
        );
    }

    /** @test */
    public function respects_the_redirects_to_property_of_the_validation_failed_exception()
    {
        Route::get('/test-models/create', function () {
            return 'show form';
        })->name('test-models.create');

        $request = Request::create('/test-models', 'POST');

        $request->headers->add([
            'Accept' => 'text/html; turbo-stream, text/html, application/xhtml+xml',
        ]);

        $next = function () {
            $resp = redirect();

            $resp->exception = ValidationException::withMessages([
                'field' => ['Something failed.'],
            ]);

            $resp->exception->redirectTo('/forced-destination');

            return $resp->to('/destination');
        };

        $response = (new TurboMiddleware())->handle($request, $next);

        $this->assertNotEquals($response->getTargetUrl(), route('test-models.create'));
        $this->assertEquals(303, $response->getStatusCode());
    }

    /** @test */
    public function redirects_back_to_resource_create_routes_on_failed_validation_follows_laravel_conventions()
    {
        Route::get('/test-models/create', function () {
            return 'show form';
        })->name('test-models.create');

        Route::post('/test-models', function () {
            request()->validate(['name' => 'required']);
        })->name('test-models.store')->middleware(TurboMiddleware::class);

        $response = $this->from('/source')->post(route('test-models.store'), [], [
            'Accept' => 'text/html; turbo-stream, text/html, application/xhtml+xml',
        ]);

        $response->assertRedirect(route('test-models.create'));
        $response->assertStatus(303);
    }

    /** @test */
    public function redirects_back_to_resource_edit_routes_on_failed_validation_follows_laravel_conventions()
    {
        Route::get('/test-models/{testModel}/edit', function () {
            return 'show form';
        })->name('test-models.edit');

        Route::put('/test-models/{testModel}', function (TestModel $model) {
            request()->validate(['name' => 'required']);
        })->name('test-models.update')->middleware(TurboMiddleware::class);

        $testModel = TestModel::create(['name' => 'Dummy model']);

        $response = $this->from('/source')->put(route('test-models.update', $testModel), [], [
            'Accept' => 'text/html; turbo-stream, text/html, application/xhtml+xml',
        ]);

        $response->assertRedirect(route('test-models.edit', $testModel));
        $response->assertStatus(303);
    }

    /** @test */
    public function lets_it_crash_when_redirect_route_does_not_exist()
    {
        Route::put('/test-models/{testModel}', function (TestModel $model) {
            request()->validate(['name' => 'required']);
        })->name('test-models.update')->middleware(TurboMiddleware::class);

        $testModel = TestModel::create(['name' => 'Dummy model']);

        $response = $this->from('/source')->put(route('test-models.update', $testModel), [], [
            'Accept' => 'text/html; turbo-stream, text/html, application/xhtml+xml',
        ]);

        $response->assertRedirect('/source');
        $response->assertStatus(303);
    }
}
