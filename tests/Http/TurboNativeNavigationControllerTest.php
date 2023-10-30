<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Http;

use HotwiredLaravel\TurboLaravel\Testing\InteractsWithTurbo;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;

class TurboNativeNavigationControllerTest extends TestCase
{
    use InteractsWithTurbo;

    public static function actionsDataProvider()
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
     */
    public function recede_resume_or_refresh_when_native_or_redirect_when_not_without_flash(string $action)
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
     */
    public function recede_resume_or_refresh_when_native_or_redirect_when_not_with_flash(string $action)
    {
        // Non-Turbo Native redirect with only flash...
        $this->post(route('trays.store'), ['return_to' => "{$action}_or_redirect", 'with' => true])
            ->assertRedirect(route('trays.show', ['tray' => 1]))
            ->assertSessionHas('status', __('Tray created.'));

        // Non-Turbo Native redirect with only flash & fragments...
        $this->post(route('trays.store'), ['return_to' => "{$action}_or_redirect", 'with' => true, 'fragment' => true])
            ->assertRedirect(route('trays.show', ['tray' => 1]).'#newly-created-tray')
            ->assertSessionHas('status', __('Tray created.'));

        // Non-Turbo Native redirect with only flash & fragments & queries...
        $this->post(route('trays.store'), ['return_to' => "{$action}_or_redirect", 'with' => true, 'fragment' => true, 'query' => true])
            ->assertRedirect(route('trays.show', ['tray' => 1, 'lorem' => 'ipsum']).'#newly-created-tray')
            ->assertSessionHas('status', __('Tray created.'));

        // Turbo Native redirect with only flash...
        $this->turboNative()
            ->post(route('trays.store'), ['return_to' => "{$action}_or_redirect", 'with' => true])
            ->assertRedirect(route("turbo_{$action}_historical_location", ['status' => urlencode(__('Tray created.'))]))
            ->assertSessionMissing('status');

        // Turbo Native redirect with only flash & fragments...
        $this->turboNative()
            ->post(route('trays.store'), ['return_to' => "{$action}_or_redirect", 'with' => true, 'fragment' => true])
            ->assertRedirect(route("turbo_{$action}_historical_location", ['status' => urlencode(__('Tray created.'))]).'#newly-created-tray')
            ->assertSessionMissing('status');

        // Turbo Native redirect with only flash & fragments & query...
        $this->turboNative()
            ->post(route('trays.store'), ['return_to' => "{$action}_or_redirect", 'with' => true, 'fragment' => true, 'query' => true])
            ->assertRedirect(route("turbo_{$action}_historical_location", ['lorem' => 'ipsum', 'status' => urlencode(__('Tray created.'))]).'#newly-created-tray')
            ->assertSessionMissing('status');
    }

    /**
     * @test
     *
     * @dataProvider actionsDataProvider
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

    /** @test */
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
