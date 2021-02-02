<?php

namespace Tonysm\TurboLaravel\Http\Middleware;

use Illuminate\Support\Str;

class RouteRedirectGuesser
{
    public function guess(string $routeName): string
    {
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
