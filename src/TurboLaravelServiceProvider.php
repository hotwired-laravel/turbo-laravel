<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Tonysm\TurboLaravel\Commands\TurboLaravelInstallCommand;

class TurboLaravelServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/turbo-laravel.php' => config_path('turbo-laravel.php'),
            ], 'config');

            $this->commands([
                TurboLaravelInstallCommand::class,
            ]);
        }

        Blade::if('turbonative', function () {
            return TurboLaravelFacade::isTurboNativeVisit();
        });
        Blade::directive('domid', function ($expression) {
            return "<?php echo e(\Tonysm\TurboLaravel\NamesResolver::resourceIdFor($expression)); ?>";
        });

        ResponseFactory::macro('turboStream', function (Model $model, string $action = null) {
            return (new TurboStreamResponseMacro())->handle($model, $action);
        });

        ResponseFactory::macro('turboStreamView', function (View $view) {
            return TurboResponseFactory::makeStream($view->render());
        });

        Request::macro('wantsTurboStream', function () {
            return Str::contains($this->header('Accept'), TurboLaravel::TURBO_STREAM_FORMAT);
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/turbo-laravel.php', 'turbo-laravel');

        $this->app->singleton(TurboLaravel::class);
    }
}
