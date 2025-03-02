<?php

namespace HotwiredLaravel\TurboLaravel;

class Features
{
    public static function enabled(string $feature): bool
    {
        return in_array($feature, config('turbo-laravel.features', []));
    }

    /**
     * @deprecated use hotwireNativeRoutes
     */
    public static function turboNativeRoutes(): string
    {
        return static::hotwireNativeRoutes();
    }

    public static function hotwireNativeRoutes(): string
    {
        return 'turbo_routes';
    }
}
