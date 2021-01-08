<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Tonysm\TurboLaravel\Turbo
 * @mixin \Tonysm\TurboLaravel\Turbo
 *
 * @method static bool isTurboNativeVisit()
 * @method static self setVisitingFromTurboNative()
 * @method static mixed broadcastToOthers(bool|\Closure $toOthers = true)
 * @method static bool shouldBroadcastToOthers
 * @method static string domId(Model $model, string $prefix = "")
 */
class TurboFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Turbo::class;
    }
}
