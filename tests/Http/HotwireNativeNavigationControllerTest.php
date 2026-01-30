<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Http;

use HotwiredLaravel\TurboLaravel\Testing\InteractsWithTurbo;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class HotwireNativeNavigationControllerTest extends TestCase
{
    use InteractsWithTurbo;

    public static function actionsDataProvider(): array
    {
        return [
            ['recede'],
            ['resume'],
            ['refresh'],
        ];
    }

    #[Test]
    #[DataProvider('actionsDataProvider')]
    public function recede_resume_or_refresh_when_native_or_redirect_when_not_without_flash(string $action): void
    {
        $this->post(route('trays.store'), ['return_to' => "{$action}_or_redirect"])
            ->assertRedirect(route('trays.show', 1));

        $this->turboNative()->post(route('trays.store'), ['return_to' => "{$action}_or_redirect"])
            ->assertRedirect(route("turbo_{$action}_historical_location"));

        $this->hotwireNative()->post(route('trays.store'), ['return_to' => "{$action}_or_redirect"])
            ->assertRedirect(route("turbo_{$action}_historical_location"));
    }

    #[Test]
    #[DataProvider('actionsDataProvider')]
    public function recede_resume_or_refresh_when_native_or_redirect_when_not_with_flash(string $action): void
    {
        // Non-Turbo Native redirect with only flash...
        $this->post(route('trays.store'), ['return_to' => "{$action}_or_redirect", 'with' => true])
            ->assertRedirect(route('trays.show', ['tray' => 1]))
            ->assertSessionHas('status', __('Tray created.'));

        // Non-Turbo Native redirect with only flash & fragments...
        $this->post(route('trays.store'), ['return_to' => "{$action}_or_redirect", 'with' => true, 'fragment' => true])
            ->assertRedirect(route('trays.show', ['tray' => 1]) . '#newly-created-tray')
            ->assertSessionHas('status', __('Tray created.'));

        // Non-Turbo Native redirect with only flash & fragments & queries...
        $this->post(route('trays.store'), ['return_to' => "{$action}_or_redirect", 'with' => true, 'fragment' => true, 'query' => true])
            ->assertRedirect(route('trays.show', ['tray' => 1, 'lorem' => 'ipsum']) . '#newly-created-tray')
            ->assertSessionHas('status', __('Tray created.'));

        // Turbo Native redirect with only flash...
        $this->turboNative()
            ->post(route('trays.store'), ['return_to' => "{$action}_or_redirect", 'with' => true])
            ->assertRedirect(route("turbo_{$action}_historical_location", ['status' => urlencode(__('Tray created.'))]))
            ->assertSessionMissing('status');

        // Hotwire Native redirect with only flash...
        $this->hotwireNative()
            ->post(route('trays.store'), ['return_to' => "{$action}_or_redirect", 'with' => true])
            ->assertRedirect(route("turbo_{$action}_historical_location", ['status' => urlencode(__('Tray created.'))]))
            ->assertSessionMissing('status');

        // Turbo Native redirect with only flash & fragments...
        $this->turboNative()
            ->post(route('trays.store'), ['return_to' => "{$action}_or_redirect", 'with' => true, 'fragment' => true])
            ->assertRedirect(route("turbo_{$action}_historical_location", ['status' => urlencode(__('Tray created.'))]) . '#newly-created-tray')
            ->assertSessionMissing('status');

        // Hotwire Native redirect with only flash & fragments...
        $this->hotwireNative()
            ->post(route('trays.store'), ['return_to' => "{$action}_or_redirect", 'with' => true, 'fragment' => true])
            ->assertRedirect(route("turbo_{$action}_historical_location", ['status' => urlencode(__('Tray created.'))]) . '#newly-created-tray')
            ->assertSessionMissing('status');

        // Turbo Native redirect with only flash & fragments & query...
        $this->turboNative()
            ->post(route('trays.store'), ['return_to' => "{$action}_or_redirect", 'with' => true, 'fragment' => true, 'query' => true])
            ->assertRedirect(route("turbo_{$action}_historical_location", ['lorem' => 'ipsum', 'status' => urlencode(__('Tray created.'))]) . '#newly-created-tray')
            ->assertSessionMissing('status');

        // Hotwire Native redirect with only flash & fragments & query...
        $this->hotwireNative()
            ->post(route('trays.store'), ['return_to' => "{$action}_or_redirect", 'with' => true, 'fragment' => true, 'query' => true])
            ->assertRedirect(route("turbo_{$action}_historical_location", ['lorem' => 'ipsum', 'status' => urlencode(__('Tray created.'))]) . '#newly-created-tray')
            ->assertSessionMissing('status');
    }

    #[Test]
    #[DataProvider('actionsDataProvider')]
    public function recede_resume_or_refresh_when_native_or_redirect_back(string $action): void
    {
        $this->post(route('trays.store'), ['return_to' => "{$action}_or_redirect_back"])
            ->assertRedirect(route('trays.show', 5));

        $this->from(url('/past_place'))->post(route('trays.store'), ['return_to' => "{$action}_or_redirect_back"])
            ->assertRedirect(url('/past_place'));

        $this->turboNative()->from(url('/past_place'))->post(route('trays.store'), ['return_to' => "{$action}_or_redirect_back"])
            ->assertRedirect(route("turbo_{$action}_historical_location"));

        $this->hotwireNative()->from(url('/past_place'))->post(route('trays.store'), ['return_to' => "{$action}_or_redirect_back"])
            ->assertRedirect(route("turbo_{$action}_historical_location"));
    }

    #[Test]
    public function historical_location_url_responds_with_html(): void
    {
        $this->get(route('turbo_recede_historical_location'))
            ->assertOk()
            ->assertSee('Going back...')
            ->assertHeader('Content-Type', 'text/html; charset=utf-8');

        $this->get(route('turbo_resume_historical_location'))
            ->assertOk()
            ->assertSee('Staying put...')
            ->assertHeader('Content-Type', 'text/html; charset=utf-8');

        $this->get(route('turbo_refresh_historical_location'))
            ->assertOk()
            ->assertSee('Refreshing...')
            ->assertHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
