<?php

namespace HotwiredLaravel\TurboLaravel\Facades;

use HotwiredLaravel\TurboLaravel\Broadcasting\Factory;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \HotwiredLaravel\TurboLaravel\Broadcasting\PendingBroadcast broadcastAction(string $action, $content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null|array $channel = null, array $attributes = [])
 * @method static \HotwiredLaravel\TurboLaravel\Broadcasting\PendingBroadcast broadcastAppend($content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null, array $attributes = [])
 * @method static \HotwiredLaravel\TurboLaravel\Broadcasting\PendingBroadcast broadcastPrepend($content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null, array $attributes = [])
 * @method static \HotwiredLaravel\TurboLaravel\Broadcasting\PendingBroadcast broadcastBefore($content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null, array $attributes = [])
 * @method static \HotwiredLaravel\TurboLaravel\Broadcasting\PendingBroadcast broadcastAfter($content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null, array $attributes = [])
 * @method static \HotwiredLaravel\TurboLaravel\Broadcasting\PendingBroadcast broadcastUpdate($content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null, array $attributes = [])
 * @method static \HotwiredLaravel\TurboLaravel\Broadcasting\PendingBroadcast broadcastReplace($content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null, array $attributes = [])
 * @method static \HotwiredLaravel\TurboLaravel\Broadcasting\PendingBroadcast broadcastRemove(\Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null, array $attributes = [])
 * @method static \HotwiredLaravel\TurboLaravel\Broadcasting\PendingBroadcast broadcastRefresh(\Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null $channel = null)
 * @method static \HotwiredLaravel\TurboLaravel\Broadcasting\Factory fake()
 * @method static \HotwiredLaravel\TurboLaravel\Broadcasting\Factory assertNothingWasBroadcasted()
 * @method static \HotwiredLaravel\TurboLaravel\Broadcasting\Factory assertBroadcasted(callable $callback)
 * @method static \HotwiredLaravel\TurboLaravel\Broadcasting\Factory clearRecordedBroadcasts()
 * @method static mixed withoutBroadcasts(callable $callback)
 *
 * @mixin \HotwiredLaravel\TurboLaravel\Broadcasting\Factory
 */
class TurboStream extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }

    public static function fake($callback = null)
    {
        return tap(static::getFacadeRoot(), function ($fake) use ($callback): void {
            static::swap($fake->fake($callback));
        });
    }
}
