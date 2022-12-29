<?php

namespace Tonysm\TurboLaravel\Facades;

use Illuminate\Support\Facades\Facade;
use Tonysm\TurboLaravel\Broadcasting\Factory;

/**
 * @method static \Tonysm\Broadcasting\PendingBroadcast broadcast(string $action, $content = null, \Illuminate\Database\Eloquent\Model|string|null $target = null, ?string $targets = null, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|string|null|array $channel = null)
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
