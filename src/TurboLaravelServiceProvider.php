<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Tonysm\TurboLaravel\Commands\TurboLaravelInstallCommand;
use Tonysm\TurboLaravel\Http\TurboResponseFactory;

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
                TurboLaravelInstallCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views/pub', 'turbo');
        Blade::componentNamespace('Tonysm\\TurboLaravel\\Views\\Components', 'turbo');

        Blade::if('turbonative', function () {
            return TurboLaravelFacade::isTurboNativeVisit();
        });
        Blade::directive('domid', function ($expression) {
            return "<?php echo e(\Tonysm\TurboLaravel\NamesResolver::resourceIdFor($expression)); ?>";
        });

        ResponseFactory::macro('turboStream', function (Model $model) {
            if ($model->exists) {
                return TurboResponseFactory::makeStream(view()->file(__DIR__.'/../resources/views/priv/model-saved.blade.php', [
                    'target' => method_exists($model, 'hotwireTargetDomId')
                        ? $model->hotwireTargetDomId()
                        : NamesResolver::resourceName($model),
                    'action' => $model->wasRecentlyCreated ? 'append' : 'update',
                    'resourcePartialName' => method_exists($model, 'hotwirePartialName')
                        ? $model->hotwirePartialName()
                        : NamesResolver::partialNameFor($model),
                    'data' => method_exists($model, 'hotwirePartialData')
                        ? $model->hotwirePartialData()
                        : [ NamesResolver::resourceNameSingular($model) => $model ],
                ]));
            } else {
                return TurboResponseFactory::makeStream(view()->file(__DIR__.'/../resources/views/priv/model-removed.blade.php', [
                    'target' => method_exists($model, 'hotwireTargetDomId')
                        ? $model->hotwireTargetDomId()
                        : NamesResolver::resourceId($model, $model->id),
                ]));
            }
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/turbo-laravel.php', 'turbo-laravel');

        $this->app->singleton(TurboLaravel::class);
    }
}
