<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Http\Middleware;

use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use HotwiredLaravel\TurboLaravel\Facades\Turbo as TurboFacade;
use HotwiredLaravel\TurboLaravel\Http\Middleware\TurboMiddleware;
use HotwiredLaravel\TurboLaravel\Tests\Stubs\TestFormRequest;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use HotwiredLaravel\TurboLaravel\Tests\TestModel;
use HotwiredLaravel\TurboLaravel\Turbo;

class TurboMiddlewareTest extends TestCase
{
    public function usesTestModelResourceRoutes()
    {
        Route::get('/test-models/create', function () {
            return 'show create form' . (request()->has('frame') ? ' (frame=' . request('frame') . ')' : '');
        })->name('test-models.create');

        Route::post('/test-models', function () {
            request()->validate(['name' => 'required']);
        })->name('test-models.store')->middleware(TurboMiddleware::class);

        Route::get('/test-models/{testModel}/edit', function () {
            return 'show edit form' . (request()->has('frame') ? ' (frame=' . request('frame') . ')' : '');
        })->name('test-models.edit');

        Route::put('/test-models/{testModel}', function (TestModel $model) {
            request()->validate(['name' => 'required']);
        })->name('test-models.update')->middleware(TurboMiddleware::class);

        Route::get('/test-models-form-request', function () {
            return 'show create form when using form requests';
        })->name('test-models-form-requests.create');

        Route::post('/test-models-form-request', function (TestFormRequest $request) {
        })->name('test-models-form-requests.store')->middleware(TurboMiddleware::class);
    }

    /**
     * @test
     * @define-route usesTestModelResourceRoutes
     */
    public function doesnt_change_redirect_response_when_not_turbo_visit()
    {
        $response = $this->from('/source')->post('/test-models', []);

        $response->assertRedirect('/source');
        $response->assertStatus(302);
    }

    /**
     * @test
     * @define-route usesTestModelResourceRoutes
     */
    public function handles_invalid_forms_with_an_internal_redirect()
    {
        $response = $this->from('/source')->post('/test-models', [], [
            'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
        ]);

        $response->assertSee('show create form');
        $response->assertStatus(422);
    }

    /**
     * @test
     * @define-route usesTestModelResourceRoutes
     */
    public function handles_invalid_forms_with_an_internal_redirect_when_using_form_requests()
    {
        $response = $this->from('/source')->post('/test-models-form-request', [], [
            'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
        ]);

        $response->assertSee('show create form when using form requests');
        $response->assertStatus(422);
    }

    public function usesTurboNativeRoute()
    {
        Route::get('/test-models', function () {
            if (TurboFacade::isTurboNativeVisit()) {
                return 'hello turbo native';
            }

            return 'hello not turbo native';
        })->name('test-models.index')->middleware(TurboMiddleware::class);
    }

    /**
     * @test
     * @define-route usesTurboNativeRoute
     */
    public function can_detect_turbo_native_visits()
    {
        $this->assertFalse(
            TurboFacade::isTurboNativeVisit(),
            'Expected to not have started saying it is a Turbo Native visit, but it said it is.'
        );

        $this->get('/test-models', [
            'User-Agent' => 'Turbo Native Android',
        ])->assertSee('hello turbo native');

        $this->assertTrue(
            TurboFacade::isTurboNativeVisit(),
            'Expected to have detected a Turbo Native visit, but it did not.'
        );
    }

    public function usesTestModelRoutesWithCustomRedirect()
    {
        Route::get('/somewhere-else', function () {
            return 'show somewhere else';
        })->name('somewhere-else');

        Route::get('/test-models/create', function () {
            return 'show create form' . (request()->has('frame') ? ' (frame=' . request('frame') . ')' : '');
        });

        Route::post('/test-models', function (TestFormRequest $request) {
            // Laravel sets the redirectTo when the form request validation fails...
        })->middleware(TurboMiddleware::class);
    }

    /**
     * @test
     * @define-route usesTestModelRoutesWithCustomRedirect
     */
    public function uses_the_redirect_to_when_guessed_route_doesnt_exist()
    {
        $response = $this->from('/somewhere-else')->post('/test-models', [], [
            'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
        ]);

        $response->assertSee('show somewhere else');
        $response->assertStatus(422);
    }

    public function usesNamedTestRoutesWithFormRequest()
    {
        Route::get('/somewhere-else', function () {
            return 'show somewhere else';
        })->name('somewhere-else');

        Route::get('/test-models/create', function () {
            return 'show create form';
        })->name('test-models.create');

        Route::post('/test-models', function (TestFormRequest $request) {
            // Laravel sets the redirectTo when the form request validation fails...
        })->name('test-models.store')->middleware(TurboMiddleware::class);
    }

    /**
     * @test
     * @define-route usesNamedTestRoutesWithFormRequest
     */
    public function can_prevent_redirect_route()
    {
        config()->set('turbo-laravel.redirect_guessing_exceptions', [
            '/test-models',
        ]);

        $response = $this->from('/somewhere-else')->post('/test-models', [], [
            'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
        ]);

        $response->assertSee('show somewhere else');
        $response->assertStatus(422);
    }

    /**
     * @test
     * @define-route usesTestModelResourceRoutes
     */
    public function sends_an_internal_redirect_to_resource_create_routes_on_failed_validation_follows_laravel_conventions_and_returns_422_status_code()
    {
        $response = $this->from('/source')->post(route('test-models.store'), [], [
            'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
        ]);

        $response->assertSee('show create form');
        $response->assertStatus(422);
    }

    /**
     * @test
     * @define-route usesTestModelResourceRoutes
     */
    public function redirects_back_to_resource_edit_routes_on_failed_validation_follows_laravel_conventions()
    {
        $testModel = TestModel::create(['name' => 'Dummy model']);

        $response = $this->from('/source')->put(route('test-models.update', $testModel), [], [
            'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
        ]);

        $response->assertSee('show edit form');
        $response->assertStatus(422);
    }

    /**
     * @test
     * @define-route usesTestModelResourceRoutes
     */
    public function redirects_include_query_params()
    {
        $testModel = TestModel::create(['name' => 'Dummy model']);

        $response = $this->from('/source')->put(route('test-models.update', ['testModel' => $testModel, 'frame' => 'lorem']), [], [
            'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
        ]);

        $response->assertSee('show edit form (frame=lorem)');
        $response->assertStatus(422);
    }

    public function usesTestModelUpdateRouteWithoutEdit()
    {
        Route::put('/test-models/{testModel}', function (TestModel $model) {
            request()->validate(['name' => 'required']);
        })->name('test-models.update')->middleware(TurboMiddleware::class);
    }

    /**
     * @test
     * @define-route usesTestModelUpdateRouteWithoutEdit
     */
    public function lets_it_crash_when_redirect_route_does_not_exist()
    {
        $testModel = TestModel::create(['name' => 'Dummy model']);

        $response = $this->from('/source')->put(route('test-models.update', $testModel), [], [
            'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
        ]);

        $response->assertRedirect('/source');
        $response->assertStatus(303);
    }

    public function usesNonResourceRoutes()
    {
        Route::name('app.')->middleware(TurboMiddleware::class)->group(function () {
            Route::get('login', function () {
                return 'login form';
            })->name('login');

            Route::post('login', function () {
                request()->validate([
                    'email' => 'required',
                    'password' => 'required',
                ]);
            });
        });
    }

    /**
     * @test
     * @define-route usesNonResourceRoutes
     */
    public function only_guess_route_on_resource_routes()
    {
        $this->from(route('app.login'))
            ->withHeaders([
                'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
            ])
            ->post('/login')
            ->assertRedirect(route('app.login'))
            ->assertStatus(303);
    }

    public function usesRoutesWhichExceptCookies()
    {
        Route::get('posts/create', function () {
            $firstRequestCookie = request()->cookie('my-cookie', 'no-cookie');

            $responseCookie = request()->cookie('response-cookie', 'no-cookie');

            return response(sprintf('Request Cookie: %s; Response Cookie: %s', $firstRequestCookie, $responseCookie));
        })->name('posts.create');

        Route::post('posts', function () {
            $exception = ValidationException::withMessages([
                'title' => ['Title cannot be blank.'],
            ]);

            $exception->response = redirect()->to('/')->withCookie('response-cookie', 'response-cookie-value');

            throw $exception;
        })->name('posts.store')->middleware(TurboMiddleware::class);
    }

    /**
     * @test
     * @define-route usesRoutesWhichExceptCookies
     */
    public function passes_the_request_cookies_to_the_internal_request()
    {
        $this->withHeaders([
                'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
            ])
            ->withUnencryptedCookie('my-cookie', 'test-value')
            ->post(route('posts.store'))
            ->assertSee('Request Cookie: test-value; Response Cookie: response-cookie-value')
            ->assertStatus(422);
    }
}
