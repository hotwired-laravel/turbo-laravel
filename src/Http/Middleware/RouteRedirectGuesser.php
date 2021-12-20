<?php

namespace Tonysm\TurboLaravel\Http\Middleware;

use Illuminate\Support\Str;

class RouteRedirectGuesser
{
    public function guess(string $routeName): ?string
    {
        if (! Str::endsWith($routeName, ['.store', '.update'])) {
            return null;
        }

        return str_replace(['.store', '.update'], ['.create', '.edit'], $routeName);
    }
}
