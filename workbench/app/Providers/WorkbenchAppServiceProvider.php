<?php

namespace Workbench\App\Providers;

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
            View::addLocation(dirname(__DIR__, levels: 2).'/resources/views');
            Blade::component('app-layout', AppLayout::class);

            Route::middleware('web')
                ->group(dirname(__DIR__, levels: 2).'/routes/web.php');
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
