<?php

namespace Tonysm\TurboLaravel\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use Tonysm\TurboLaravel\Broadcasters\Broadcaster;
use Tonysm\TurboLaravel\Turbo as BaseTurbo;

/**
 * @see \Tonysm\TurboLaravel\Turbo
 * @mixin \Tonysm\TurboLaravel\Turbo
 *
 * @method static bool isTurboNativeVisit()
 * @method static self setVisitingFromTurboNative()
 * @method static mixed broadcastToOthers(bool|\Closure $toOthers = true)
 * @method static bool shouldBroadcastToOthers
 * @method static string domId(Model $model, string $prefix = "")
 * @method static Broadcaster broadcaster()
 * @method static \Tonysm\TurboLaravel\Broadcasting\PendingBroadcast broadcastAppendTo(\Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|string $channel, $content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null)
 */
class Turbo extends Facade
{
    protected static function getFacadeAccessor()
    {
        return BaseTurbo::class;
    }
}
