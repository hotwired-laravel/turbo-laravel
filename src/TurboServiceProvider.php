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
    public function boot(): void
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

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/turbo-laravel.php', 'turbo-laravel');

        $this->app->scoped(Turbo::class);
        $this->app->bind(Broadcaster::class, LaravelBroadcaster::class);
        $this->app->scoped(Limiter::class);
    }

    private function configureComponents(): void
    {
        $this->callAfterResolving('blade.compiler', function (BladeCompiler $blade): void {
            $blade->anonymousComponentPath(__DIR__.'/../resources/views/components', 'turbo');
        });
    }

    private function configurePublications(): void
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
        Blade::if('turbonative', fn () => TurboFacade::isHotwireNativeVisit());

        Blade::if('unlessturbonative', fn (): bool => ! TurboFacade::isHotwireNativeVisit());

        Blade::if('hotwirenative', fn () => TurboFacade::isHotwireNativeVisit());

        Blade::if('unlesshotwirenative', fn (): bool => ! TurboFacade::isHotwireNativeVisit());

        Blade::directive('domid', fn ($expression): string => "<?php echo e(\\HotwiredLaravel\\TurboLaravel\\dom_id($expression)); ?>");

        Blade::directive('domclass', fn ($expression): string => "<?php echo e(\\HotwiredLaravel\\TurboLaravel\\dom_class($expression)); ?>");

        Blade::directive('channel', fn ($expression): string => "<?php echo {$expression}->broadcastChannel(); ?>");
    }

    private function configureRequestAndResponseMacros(): void
    {
        ResponseFacade::macro('turboStream', fn ($model = null, ?string $action = null): MultiplePendingTurboStreamResponse|PendingTurboStreamResponse => turbo_stream($model, $action));

        ResponseFacade::macro('turboStreamView', fn ($view, array $data = []): Response|ResponseFactory => turbo_stream_view($view, $data));

        Request::macro('wantsTurboStream', fn (): bool => Str::contains($this->header('Accept'), Turbo::TURBO_STREAM_FORMAT));

        Request::macro('wantsTurboStreams', fn (): bool => $this->wantsTurboStream());

        Request::macro('wasFromTurboNative', fn (): bool => TurboFacade::isHotwireNativeVisit());

        Request::macro('wasFromHotwireNative', fn (): bool => TurboFacade::isHotwireNativeVisit());

        Request::macro('wasFromTurboFrame', function (?string $frame = null): bool {
            if (! $frame) {
                return $this->hasHeader('Turbo-Frame');
            }

            return $this->header('Turbo-Frame', null) === $frame;
        });
    }

    private function configureTestResponseMacros(): void
    {
        if (! app()->environment('testing')) {
            return;
        }

        TestResponse::macro('assertTurboStream', function (?callable $callback = null): void {
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

        TestResponse::macro('assertNotTurboStream', function (): void {
            Assert::assertStringNotContainsString(
                Turbo::TURBO_STREAM_FORMAT,
                $this->headers->get('Content-Type'),
            );
        });

        TestResponse::macro('assertRedirectRecede', function (array $with = []): void {
            $this->assertRedirectToRoute('turbo_recede_historical_location', $with);
        });

        TestResponse::macro('assertRedirectResume', function (array $with = []): void {
            $this->assertRedirectToRoute('turbo_resume_historical_location', $with);
        });

        TestResponse::macro('assertRedirectRefresh', function (array $with = []): void {
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
