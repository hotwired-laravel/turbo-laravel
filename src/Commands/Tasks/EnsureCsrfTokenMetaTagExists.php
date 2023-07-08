<?php

namespace HotwiredLaravel\TurboLaravel\Commands\Tasks;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class EnsureCsrfTokenMetaTagExists
{
    public function __invoke($file, $next)
    {
        if (! Str::contains($contents = File::get($file), ['csrf-token'])) {
            File::put($file, preg_replace(
                '/(\s*)(<\/title>)/',
                "\\1    <meta name=\"csrf-token\" content=\"{{ csrf_token() }}\">\n\\1\\2",
                $contents,
            ));
        }

        return $next($file);
    }
}
