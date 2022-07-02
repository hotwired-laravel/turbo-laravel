<?php

namespace Tonysm\TurboLaravel\Commands\Tasks;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class EnsureLivewireTurboBridgeExists
{
    public function __invoke($file, $next)
    {
        // Also add the Livewire scripts to the guest layout. This is done because
        // Livewire and Alpine don't seem to play well with Turbo Drive when it
        // was already started, as app.js is loaded in the guests layout too.

        $fileContents = File::get($file);

        if (! Str::contains($fileContents, ['@livewireStyles', '<livewire:styles />', '<livewire:styles></livewire:styles>'])) {
            $fileContents = preg_replace(
                '/(\s*)(<\/head>)/',
                "\\1    @livewireStyles\n\\1\\2",
                $fileContents,
            );
        }

        if (! Str::contains($fileContents, ['@livewireScripts', '<livewire:scripts />', '<livewire:scripts></livewire:scripts>'])) {
            $fileContents = preg_replace(
                '/(\s*)(<\/body>)/',
                "\\1    @livewireScripts\n\\1\\2",
                $fileContents,
            );
        }

        if (! Str::contains($fileContents, ['livewire-turbolinks.js'])) {
            $fileContents = preg_replace(
                '/(\s*)(<\/body>)/',
                "\\1    <script src=\"https://cdn.jsdelivr.net/gh/livewire/turbolinks@v0.1.4/dist/livewire-turbolinks.js\" data-turbolinks-eval=\"false\" data-turbo-eval=\"false\"></script>\n\\1\\2",
                $fileContents,
            );
        }

        File::put($file, $fileContents);

        return $next($file);
    }
}
