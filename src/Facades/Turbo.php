<?php

namespace HotwiredLaravel\TurboLaravel\Facades;

use Closure;
use HotwiredLaravel\TurboLaravel\Broadcasters\Broadcaster;
use HotwiredLaravel\TurboLaravel\NamesResolver;
use HotwiredLaravel\TurboLaravel\Turbo as BaseTurbo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;

/**
 * @see \HotwiredLaravel\TurboLaravel\Turbo
 *
 * @mixin \HotwiredLaravel\TurboLaravel\Turbo
 *
 * @method static bool isHotwireNativeVisit()
 * @method static self setVisitingFromHotwireNative()
 * @method static mixed broadcastToOthers(bool|\Closure $toOthers = true)
 * @method static bool shouldBroadcastToOthers
 * @method static string domId(Model $model, string $prefix = "")
 * @method static Broadcaster broadcaster()
 * @method static \HotwiredLaravel\TurboLaravel\Turbo setTurboTrackingRequestId(string $requestId)
 * @method static ?string currentRequestId()
 */
class Turbo extends Facade
{
    public static function usePartialsSubfolderPattern(): void
    {
        static::resolvePartialsPathUsing('{plural}.partials.{singular}');
    }

    public static function resolvePartialsPathUsing(string|Closure $pattern): void
    {
        NamesResolver::resolvePartialsPathUsing($pattern);
    }

    protected static function getFacadeAccessor()
    {
        return BaseTurbo::class;
    }
}
