<?php

namespace Tonysm\TurboLaravel\Facades;

use Illuminate\Support\Facades\Facade;
use Tonysm\TurboLaravel\Broadcasting\Factory;

/**
 * @method static \Tonysm\TurboLaravel\Broadcasting\PendingBroadcast broadcastAction(string $action, $content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null|array $channel = null, array $attributes = [])
 * @method static \Tonysm\TurboLaravel\Broadcasting\PendingBroadcast broadcastAppend($content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null, array $attributes = [])
 * @method static \Tonysm\TurboLaravel\Broadcasting\PendingBroadcast broadcastPrepend($content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null, array $attributes = [])
 * @method static \Tonysm\TurboLaravel\Broadcasting\PendingBroadcast broadcastBefore($content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null, array $attributes = [])
 * @method static \Tonysm\TurboLaravel\Broadcasting\PendingBroadcast broadcastAfter($content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null, array $attributes = [])
 * @method static \Tonysm\TurboLaravel\Broadcasting\PendingBroadcast broadcastUpdate($content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null, array $attributes = [])
 * @method static \Tonysm\TurboLaravel\Broadcasting\PendingBroadcast broadcastReplace($content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null, array $attributes = [])
 * @method static \Tonysm\TurboLaravel\Broadcasting\PendingBroadcast broadcastRemove(\Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null, array $attributes = [])
 *
 * @method static \Tonysm\TurboLaravel\Broadcasting\Factory fake()
 * @method static \Tonysm\TurboLaravel\Broadcasting\Factory assertNothingWasBroadcasted()
 * @method static \Tonysm\TurboLaravel\Broadcasting\Factory assertBroadcasted(callable $callback)
 * @method static \Tonysm\TurboLaravel\Broadcasting\Factory assertBroadcastedTimes(callable $callback, int $times = 1)
 *
 * @mixin \Tonysm\TurboLaravel\Broadcasting\Factory
 */
class TurboStream extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }

    public static function fake($callback = null)
    {
        return tap(static::getFacadeRoot(), function ($fake) use ($callback) {
            static::swap($fake->fake($callback));
        });
    }
}
