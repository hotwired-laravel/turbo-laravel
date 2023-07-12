<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Http;

use HotwiredLaravel\TurboLaravel\Http\Controllers\Concerns\InteractsWithTurboNativeNavigation;
use HotwiredLaravel\TurboLaravel\Http\Middleware\TurboMiddleware;
use HotwiredLaravel\TurboLaravel\Testing\InteractsWithTurbo;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;

class TurboNativeNavigationControllerTest extends TestCase
{
    use InteractsWithTurbo;

    public function usesTurboNativeRoutes()
    {
        Route::middleware(['web', TurboMiddleware::class])->resource('trays', TraysController::class);
    }

    public function actionsDataProvider()
    {
        return [
            ['recede'],
            ['resume'],
            ['refresh'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider actionsDataProvider
     *
     * @define-route usesTurboNativeRoutes
     */
    public function recede_resume_or_refresh_when_native_or_redirect_when_not(string $action)
    {
        $this->post(route('trays.store'), ['return_to' => "{$action}_or_redirect"])
            ->assertRedirect(route('trays.show', 1));

        $this->turboNative()->post(route('trays.store'), ['return_to' => "{$action}_or_redirect"])
            ->assertRedirect(route("turbo_{$action}_historical_location"));
    }

    /**
     * @test
     *
     * @dataProvider actionsDataProvider
     *
     * @define-route usesTurboNativeRoutes
     */
    public function recede_resume_or_refresh_when_native_or_redirect_back(string $action)
    {
        $this->post(route('trays.store'), ['return_to' => "{$action}_or_redirect_back"])
            ->assertRedirect(route('trays.show', 5));

        $this->from(url('/past_place'))->post(route('trays.store'), ['return_to' => "{$action}_or_redirect_back"])
            ->assertRedirect(url('/past_place'));

        $this->turboNative()->from(url('/past_place'))->post(route('trays.store'), ['return_to' => "{$action}_or_redirect_back"])
            ->assertRedirect(route("turbo_{$action}_historical_location"));
    }

    /**
     * @test
     *
     * @define-route usesTurboNativeRoutes
     */
    public function historical_location_url_responds_with_html()
    {
        $this->get(route('turbo_recede_historical_location'))
            ->assertOk()
            ->assertSee('Going back...')
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8');

        $this->get(route('turbo_resume_historical_location'))
            ->assertOk()
            ->assertSee('Staying put...')
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8');

        $this->get(route('turbo_refresh_historical_location'))
            ->assertOk()
            ->assertSee('Refreshing...')
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }
}

class TraysController extends Controller
{
    use InteractsWithTurboNativeNavigation;

    public function show($trayId)
    {
        return [
            'tray_id' => $trayId,
        ];
    }

    public function store()
    {
        return match (request('return_to')) {
            'recede_or_redirect' => $this->recedeOrRedirectTo(route('trays.show', 1)),
            'resume_or_redirect' => $this->resumeOrRedirectTo(route('trays.show', 1)),
            'refresh_or_redirect' => $this->refreshOrRedirectTo(route('trays.show', 1)),
            'recede_or_redirect_back' => $this->recedeOrRedirectBack(route('trays.show', 5)),
            'resume_or_redirect_back' => $this->resumeOrRedirectBack(route('trays.show', 5)),
            'refresh_or_redirect_back' => $this->refreshOrRedirectBack(route('trays.show', 5)),
        };
    }
}
