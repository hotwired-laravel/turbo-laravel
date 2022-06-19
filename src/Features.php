<?php

namespace Tonysm\TurboLaravel;

class Features
{
    public static function enabled(string $feature)
    {
        return in_array($feature, config('turbo-laravel.features', []));
    }

    public static function turboNativeRoutes(): string
    {
        return 'turbo_routes';
    }
}
