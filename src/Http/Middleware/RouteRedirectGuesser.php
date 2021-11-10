<?php

namespace Tonysm\TurboLaravel\Http\Middleware;

use Illuminate\Support\Str;

class RouteRedirectGuesser
{
    public function guess(string $routeName): ?string
    {
        if (! Str::endsWith($routeName, '.store') && ! Str::endsWith($routeName, '.update')) {
            return null;
        }

        $creating = Str::endsWith($routeName, '.store');

        $lookFor = $creating
            ? '.store'
            : '.update';

        $replaceWith = $creating
            ? '.create'
            : '.edit';

        return str_replace($lookFor, $replaceWith, $routeName);
    }
}
