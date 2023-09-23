<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Http;

use HotwiredLaravel\TurboLaravel\Testing\InteractsWithTurbo;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;

class TurboNativeNavigationControllerTest extends TestCase
{
    use InteractsWithTurbo;

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
