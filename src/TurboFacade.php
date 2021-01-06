<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Tonysm\TurboLaravel\Turbo
 * @mixin \Tonysm\TurboLaravel\Turbo
 */
class TurboFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Turbo::class;
    }
}
