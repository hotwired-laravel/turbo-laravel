<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Tonysm\TurboLaravel\Commands\TurboInstallCommand;
use Tonysm\TurboLaravel\Http\TurboResponseFactory;

class TurboServiceProvider extends ServiceProvider
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
                TurboInstallCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'turbo-laravel');

        $this->registerBladeMacros();
        $this->registerRequestAndResponseMacros();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/turbo-laravel.php', 'turbo-laravel');

        $this->app->singleton(Turbo::class);
    }

    private function registerBladeMacros(): void
    {
        Blade::if('turbonative', function () {
            return TurboFacade::isTurboNativeVisit();
        });

        Blade::directive('domid', function ($expression) {
            return "<?php echo e(\Tonysm\TurboLaravel\NamesResolver::resourceIdFor($expression)); ?>";
        });
    }

    private function registerRequestAndResponseMacros(): void
    {
        ResponseFactory::macro('turboStream', function (Model $model, string $action = null) {
            return (new TurboStreamResponseMacro())->handle($model, $action);
        });

        ResponseFactory::macro('turboStreamView', function (View $view) {
            return TurboResponseFactory::makeStream($view->render());
        });

        Request::macro('wantsTurboStream', function () {
            return Str::contains($this->header('Accept'), Turbo::TURBO_STREAM_FORMAT);
        });
    }
}
