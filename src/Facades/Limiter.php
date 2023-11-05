<?php

namespace HotwiredLaravel\TurboLaravel\Facades;

use HotwiredLaravel\TurboLaravel\Broadcasting\Limiter as BaseLimiter;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \HotwiredLaravel\TurboLaravel\Broadcasting\Limiter
 *
 * @see \HotwiredLaravel\TurboLaravel\Broadcasting\Limiter
 *
 * @method static bool shouldLimit(string $key)
 */
class Limiter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return BaseLimiter::class;
    }
}
