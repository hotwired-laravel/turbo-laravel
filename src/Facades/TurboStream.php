<?php

namespace Tonysm\TurboLaravel\Facades;

use Illuminate\Support\Facades\Facade;
use Tonysm\TurboLaravel\Broadcasting\Factory;

/**
 * @method static \Tonysm\TurboLaravel\Broadcasting\PendingBroadcast broadcastActionTo(string $action, $content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null|array $channel = null)
 * @method static \Tonysm\TurboLaravel\Broadcasting\PendingBroadcast broadcastAppendTo($content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null)
 * @method static \Tonysm\TurboLaravel\Broadcasting\PendingBroadcast broadcastPrependTo($content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null)
 * @method static \Tonysm\TurboLaravel\Broadcasting\PendingBroadcast broadcastBeforeTo($content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null)
 * @method static \Tonysm\TurboLaravel\Broadcasting\PendingBroadcast broadcastAfterTo($content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null)
 * @method static \Tonysm\TurboLaravel\Broadcasting\PendingBroadcast broadcastUpdateTo($content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null)
 * @method static \Tonysm\TurboLaravel\Broadcasting\PendingBroadcast broadcastReplaceTo($content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null)
 * @method static \Tonysm\TurboLaravel\Broadcasting\PendingBroadcast broadcastRemoveTo(\Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null)
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
