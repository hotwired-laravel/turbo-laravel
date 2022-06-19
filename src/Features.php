<?php

namespace Tonysm\TurboLaravel;

class Features
{
    private static $features = [];

    public static function turboNativeRoutes()
    {
        static::$features['turbo_routes'] = true;
    }

    public static function shouldEnableTurboNativeRoutes(): bool
    {
        return static::$features['turbo_routes'] ?? false;
    }
}
