<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Assert;
use Tonysm\TurboLaravel\Broadcasters\Broadcaster;
use Tonysm\TurboLaravel\Broadcasters\LaravelBroadcaster;
use Tonysm\TurboLaravel\Commands\TurboInstallCommand;
use Tonysm\TurboLaravel\Facades\Turbo as TurboFacade;
use Tonysm\TurboLaravel\Http\Middleware\TurboMiddleware;
use Tonysm\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use Tonysm\TurboLaravel\Http\PendingTurboStreamResponse;
use Tonysm\TurboLaravel\Testing\AssertableTurboStream;
use Tonysm\TurboLaravel\Testing\ConvertTestResponseToTurboStreamCollection;
use Tonysm\TurboLaravel\Views\Components as ViewComponents;

class TurboServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->configurePublications();
        $this->configureRoutes();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'turbo-laravel');

        $this->configureComponents();
        $this->configureMacros();
        $this->configureRequestAndResponseMacros();
        $this->configureTestResponseMacros();

        if (config('turbo-laravel.automatically_register_middleware', true)) {
            Route::prependMiddlewareToGroup('web', TurboMiddleware::class);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/turbo-laravel.php', 'turbo-laravel');

        $this->app->scoped(Turbo::class);
        $this->app->bind(Broadcaster::class, LaravelBroadcaster::class);
    }

    private function configureComponents()
    {
        $this->loadViewComponentsAs('turbo', [
            ViewComponents\Frame::class,
            ViewComponents\Stream::class,
            ViewComponents\StreamFrom::class,
        ]);
    }

    private function configurePublications()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/turbo-laravel.php' => config_path('turbo-laravel.php'),
        ], 'turbo-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => base_path('resources/views/vendor/turbo-laravel'),
        ], 'turbo-views');

        $this->publishes([
            __DIR__.'/../routes/turbo.php' => base_path('routes/turbo.php'),
        ], 'turbo-routes');

        $this->commands([
            TurboInstallCommand::class,
        ]);
    }

    private function configureRoutes(): void
    {
        if (Features::enabled('turbo_routes')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/turbo.php');
        }
    }

    private function configureMacros(): void
    {
        Blade::if('turbonative', function () {
            return TurboFacade::isTurboNativeVisit();
        });

        Blade::if('unlessturbonative', function () {
            return ! TurboFacade::isTurboNativeVisit();
        });

        Blade::directive('domid', function ($expression) {
            return "<?php echo e(\\Tonysm\\TurboLaravel\\dom_id($expression)); ?>";
        });

        Blade::directive('domclass', function ($expression) {
            return "<?php echo e(\\Tonysm\\TurboLaravel\\dom_class($expression)); ?>";
        });

        Blade::directive('channel', function ($expression) {
            return "<?php echo {$expression}->broadcastChannel(); ?>";
        });
    }

    private function configureRequestAndResponseMacros(): void
    {
        ResponseFacade::macro('turboStream', function ($model = null, string $action = null): MultiplePendingTurboStreamResponse|PendingTurboStreamResponse {
            return turbo_stream($model, $action);
        });

        ResponseFacade::macro('turboStreamView', function ($view, array $data = []): Response|ResponseFactory {
            return turbo_stream_view($view, $data);
        });

        Request::macro('wantsTurboStream', function (): bool {
            return Str::contains($this->header('Accept'), Turbo::TURBO_STREAM_FORMAT);
        });

        Request::macro('wasFromTurboNative', function (): bool {
            return TurboFacade::isTurboNativeVisit();
        });
    }

    private function configureTestResponseMacros()
    {
        if (! app()->environment('testing')) {
            return;
        }

        TestResponse::macro('assertTurboStream', function (callable $callback = null) {
            Assert::assertStringContainsString(
                Turbo::TURBO_STREAM_FORMAT,
                $this->headers->get('Content-Type'),
            );

            if ($callback === null) {
                return;
            }

            $turboStreams = (new ConvertTestResponseToTurboStreamCollection)($this);
            $callback(new AssertableTurboStream($turboStreams));
        });

        TestResponse::macro('assertNotTurboStream', function () {
            Assert::assertStringNotContainsString(
                Turbo::TURBO_STREAM_FORMAT,
                $this->headers->get('Content-Type'),
            );
        });
    }
}
