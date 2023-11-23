<?php

namespace HotwiredLaravel\TurboLaravel\Facades;

use HotwiredLaravel\TurboLaravel\Broadcasters\Broadcaster;
use HotwiredLaravel\TurboLaravel\Turbo as BaseTurbo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;

/**
 * @see \HotwiredLaravel\TurboLaravel\Turbo
 *
 * @mixin \HotwiredLaravel\TurboLaravel\Turbo
 *
 * @method static bool isTurboNativeVisit()
 * @method static self setVisitingFromTurboNative()
 * @method static mixed broadcastToOthers(bool|\Closure $toOthers = true)
 * @method static bool shouldBroadcastToOthers
 * @method static string domId(Model $model, string $prefix = "")
 * @method static Broadcaster broadcaster()
 * @method static \HotwiredLaravel\TurboLaravel\Turbo setTurboTrackingRequestId(string $requestId)
 * @method static ?string currentRequestId()
 */
class Turbo extends Facade
{
    protected static function getFacadeAccessor()
    {
        return BaseTurbo::class;
    }
}
