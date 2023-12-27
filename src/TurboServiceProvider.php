<?php

namespace HotwiredLaravel\TurboLaravel;

use HotwiredLaravel\TurboLaravel\Broadcasters\Broadcaster;
use HotwiredLaravel\TurboLaravel\Broadcasters\LaravelBroadcaster;
use HotwiredLaravel\TurboLaravel\Broadcasting\Limiter;
use HotwiredLaravel\TurboLaravel\Commands\TurboInstallCommand;
use HotwiredLaravel\TurboLaravel\Facades\Turbo as TurboFacade;
use HotwiredLaravel\TurboLaravel\Http\Middleware\TurboMiddleware;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use HotwiredLaravel\TurboLaravel\Testing\AssertableTurboStream;
use HotwiredLaravel\TurboLaravel\Testing\ConvertTestResponseToTurboStreamCollection;
use HotwiredLaravel\TurboLaravel\Views\Components as ViewComponents;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Illuminate\View\Compilers\BladeCompiler;
use PHPUnit\Framework\Assert;

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
        $this->configureMiddleware();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/turbo-laravel.php', 'turbo-laravel');

        $this->app->scoped(Turbo::class);
        $this->app->bind(Broadcaster::class, LaravelBroadcaster::class);
        $this->app->scoped(Limiter::class);
    }

    private function configureComponents()
    {
        $this->callAfterResolving('blade.compiler', function (BladeCompiler $blade) {
            $blade->anonymousComponentPath(__DIR__.'/../resources/views/components', 'turbo');
        });
    }

    private function configurePublications()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/turbo-laravel.php' => config_path('turbo-laravel.php'),
        ], 'turbo-config');

        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/turbo-laravel'),
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
            return "<?php echo e(\\HotwiredLaravel\\TurboLaravel\\dom_id($expression)); ?>";
        });

        Blade::directive('domclass', function ($expression) {
            return "<?php echo e(\\HotwiredLaravel\\TurboLaravel\\dom_class($expression)); ?>";
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

        Request::macro('wantsTurboStreams', function (): bool {
            return $this->wantsTurboStream();
        });

        Request::macro('wasFromTurboNative', function (): bool {
            return TurboFacade::isTurboNativeVisit();
        });

        Request::macro('wasFromTurboFrame', function (string $frame = null): bool {
            if (! $frame) {
                return $this->hasHeader('Turbo-Frame');
            }

            return $this->header('Turbo-Frame', null) === $frame;
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

        TestResponse::macro('assertRedirectRecede', function (array $with = []) {
            $this->assertRedirectToRoute('turbo_recede_historical_location', $with);
        });

        TestResponse::macro('assertRedirectResume', function (array $with = []) {
            $this->assertRedirectToRoute('turbo_resume_historical_location', $with);
        });

        TestResponse::macro('assertRedirectRefresh', function (array $with = []) {
            $this->assertRedirectToRoute('turbo_refresh_historical_location', $with);
        });
    }

    protected function configureMiddleware(): void
    {
        if (! config('turbo-laravel.automatically_register_middleware', true)) {
            return;
        }

        /** @var Kernel $kernel */
        $kernel = resolve(Kernel::class);
        $kernel->prependMiddlewareToGroup('web', TurboMiddleware::class);
    }
}
