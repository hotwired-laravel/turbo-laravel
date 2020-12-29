<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Tonysm\TurboLaravel\Commands\TurboLaravelCommand;

class TurboLaravelServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/turbo-laravel.php' => config_path('turbo-laravel.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../resources/views' => base_path('resources/views/vendor/turbo-laravel'),
            ], 'views');

            $this->commands([
                TurboLaravelCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views/pub', 'turbo');
        Blade::componentNamespace('Tonysm\\Views\\Components', 'turbo');

        Blade::if('turbonative', function () {
            return TurboLaravelFacade::isTurboNativeVisit();
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/turbo-laravel.php', 'turbo-laravel');

        $this->app->singleton(TurboLaravel::class);
    }
}
