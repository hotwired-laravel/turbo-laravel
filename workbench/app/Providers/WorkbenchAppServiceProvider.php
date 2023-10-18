<?php

namespace Workbench\App\Providers;

use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Workbench\App\View\Components\AppLayout;

class WorkbenchAppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->booted(function () {
            // Route::middleware('web')
            //     ->group(dirname(__DIR__, levels: 2).'/routes/web.php');
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::addLocation(dirname(__DIR__, levels: 2).'/resources/views');

        Blade::component('app-layout', AppLayout::class);

        $this->loadMigrationsFrom(dirname(__DIR__, levels: 2).'/database/migrations');

        PendingTurboStreamResponse::macro('flash', function (string $message) {
            return $this->append('notifications', view('partials._notification', [
                'message' => $message,
            ]));
        });
    }
}
