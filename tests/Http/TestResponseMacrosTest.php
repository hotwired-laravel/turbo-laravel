<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Http;

use HotwiredLaravel\TurboLaravel\Testing\InteractsWithTurbo;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;

class TestResponseMacrosTest extends TestCase
{
    use InteractsWithTurbo;

    /**
     * @test
     *
     * @testWith ["recede_or_redirect", "turbo_recede_historical_location", "assertRedirectRecede"]
     *           ["resume_or_redirect", "turbo_resume_historical_location", "assertRedirectResume"]
     *           ["refresh_or_redirect", "turbo_refresh_historical_location", "assertRedirectRefresh"]
     */
    public function asserts_historical_locations_without_flashes($returnTo, $route, $method)
    {
        $this->turboNative()->post(route('trays.store', 1), [
            'return_to' => $returnTo,
        ])->assertRedirectToRoute($route)->{$method}();
    }

    /**
     * @test
     *
     * @testWith ["recede_or_redirect", "turbo_recede_historical_location", "assertRedirectRecede"]
     *           ["resume_or_redirect", "turbo_resume_historical_location", "assertRedirectResume"]
     *           ["refresh_or_redirect", "turbo_refresh_historical_location", "assertRedirectRefresh"]
     */
    public function asserts_historical_locations_with_flashes($returnTo, $route, $method)
    {
        $this->turboNative()->post(route('trays.store', 1), [
            'return_to' => $returnTo,
            'with' => true
        ])->assertRedirectToRoute($route, $with = [
            'status' => urlencode(__('Tray created.')),
        ])->{$method}($with);
    }
}
