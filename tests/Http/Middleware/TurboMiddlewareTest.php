<?php

namespace Tonysm\TurboLaravel\Tests\Http\Middleware;

use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Tonysm\TurboLaravel\Facades\Turbo as TurboFacade;
use Tonysm\TurboLaravel\Http\Middleware\TurboMiddleware;
use Tonysm\TurboLaravel\Tests\TestCase;
use Tonysm\TurboLaravel\Tests\TestModel;
use Tonysm\TurboLaravel\Turbo;

class TurboMiddlewareTest extends TestCase
{
    public function usesTestModelResourceRoutes()
    {
        Route::get('/test-models/create', function () {
            return 'show form';
        })->name('test-models.create');

        Route::post('/test-models', function () {
            request()->validate(['name' => 'required']);
        })->name('test-models.store')->middleware(TurboMiddleware::class);

        Route::get('/test-models/{testModel}/edit', function () {
            return 'show form';
        })->name('test-models.edit');

        Route::put('/test-models/{testModel}', function (TestModel $model) {
            request()->validate(['name' => 'required']);
        })->name('test-models.update')->middleware(TurboMiddleware::class);
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
    public function handles_redirect_responses()
    {
        $response = $this->from('/source')->post('/test-models', [], [
            'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
        ]);

        $response->assertRedirect('/test-models/create');
        $response->assertStatus(303);
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
            return 'show form';
        })->name('somewhere-else');

        Route::get('/test-models/create', function () {
            return 'show form';
        })->name('test-models.create');

        Route::post('/test-models', function () {
            throw ValidationException::withMessages(['field' => ['Failed field']])->redirectTo(route('somewhere-else'));
        })->name('test-models.store')->middleware(TurboMiddleware::class);
    }

    /**
     * @test
     * @define-route usesTestModelRoutesWithCustomRedirect
     */
    public function respects_the_redirects_to_property_of_the_validation_failed_exception()
    {
        $response = $this->from('/source')->post('/test-models', [], [
            'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
        ]);

        $response->assertRedirect('/somewhere-else');
        $response->assertStatus(303);
    }

    /**
     * @test
     * @define-route usesTestModelResourceRoutes
     */
    public function redirects_back_to_resource_create_routes_on_failed_validation_follows_laravel_conventions()
    {
        $response = $this->from('/source')->post(route('test-models.store'), [], [
            'Accept' => sprintf('%s, text/html, application/xhtml+xml', Turbo::TURBO_STREAM_FORMAT),
        ]);

        $response->assertRedirect(route('test-models.create'));
        $response->assertStatus(303);
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

        $response->assertRedirect(route('test-models.edit', $testModel));
        $response->assertStatus(303);
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

        $response->assertRedirect(route('test-models.edit', ['testModel' => $testModel, 'frame' => 'lorem']));
        $response->assertStatus(303);
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
}
