<?php

namespace Workbench\App\Providers;

use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Workbench\App\View\Components\AppLayout;

class WorkbenchAppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Blade::component('app-layout', AppLayout::class);

        PendingTurboStreamResponse::macro('flash', function (string $message) {
            return $this->append('notifications', view('partials._notification', [
                'message' => $message,
            ]));
        });
    }
}
