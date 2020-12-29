<?php

namespace Tonysm\TurboLaravel;

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

            $migrationFileName = 'create_turbo_laravel_table.php';
            if (! $this->migrationFileExists($migrationFileName)) {
                $this->publishes([
                    __DIR__ . "/../database/migrations/{$migrationFileName}.stub" => database_path('migrations/' . date('Y_m_d_His', time()) . '_' . $migrationFileName),
                ], 'migrations');
            }

            $this->commands([
                TurboLaravelCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'turbo-laravel');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/turbo-laravel.php', 'turbo-laravel');
    }

    public static function migrationFileExists(string $migrationFileName): bool
    {
        $len = strlen($migrationFileName);
        foreach (glob(database_path("migrations/*.php")) as $filename) {
            if ((substr($filename, -$len) === $migrationFileName)) {
                return true;
            }
        }

        return false;
    }
}
