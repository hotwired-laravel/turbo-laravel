<?php

namespace Tonysm\TurboLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class TurboInstallCommand extends Command
{
    public $signature = 'turbo:install {--jet} {--stimulus : To install Stimulus or not.}';
    public $description = 'Install the Turbo resources';

    public function handle()
    {
        $this->updateNodePackages(function ($packages) {
            return [
                '@hotwired/turbo' => '^7.0.0-beta.3',
                'laravel-echo' => '^1.10.0',
                'pusher-js' => '^7.0.2',
            ] + $packages;
        });

        if ($this->option('stimulus')) {
            $this->updateNodePackages(function ($packages) {
                return [
                    'stimulus' => '^2.0.0',
                    '@stimulus/webpack-helpers' => '^2.0.0',
                ] + $packages;
            });
        }

        if ($this->option('jet')) {
            $this->updateNodePackages(function ($packages) {
                return [
                    'alpinejs' => '^2.8.0',
                    'alpine-turbo-drive-adapter' => '^1.0.1',
                ] + $packages;
            });

            if ((new Filesystem())->exists(resource_path('views/layouts/app.blade.php'))) {
                $this->updateJetstreamLayouts();
            }
        }

        // JS scaffold...
        (new Filesystem())->ensureDirectoryExists(resource_path('js/elements'));

        copy(__DIR__ . '/../../stubs/resources/js/app.js', resource_path('js/app.js'));

        $this->replaceJsStub(
            resource_path('js/app.js'),
            '//=inject-alpine',
            $this->option('jet')
                ? (new Filesystem())->get(__DIR__ . '/../../stubs/resources/js/alpine.js')
                : ''
        );

        $this->replaceJsStub(
            resource_path('js/app.js'),
            '//=inject-stimulus',
            $this->option('stimulus')
                ? (new Filesystem())->get(__DIR__ . '/../../stubs/resources/js/stimulus.js')
                : ''
        );

        copy(__DIR__ . '/../../stubs/resources/js/bootstrap.js', resource_path('js/bootstrap.js'));
        copy(__DIR__ . '/../../stubs/resources/js/echo.js', resource_path('js/echo.js'));
        copy(__DIR__ . '/../../stubs/resources/js/elements/turbo-echo-stream-tag.js', resource_path('js/elements/turbo-echo-stream-tag.js'));

        if ($this->option('stimulus')) {
            (new Filesystem())->ensureDirectoryExists(resource_path('js/controllers'));
            copy(__DIR__ . '/../../stubs/resources/js/controllers/hello_controller.js', resource_path('js/controllers/hello_controller.js'));
        }

        $this->info('Turbo Laravel scaffolding installed successfully.');
        $this->comment('Please execute the "npm install && npm run dev" command to build your assets.');
    }

    /**
     * Update the "package.json" file.
     *
     * @param callable $callback
     * @param bool $dev
     * @return void
     */
    protected static function updateNodePackages(callable $callback, $dev = true)
    {
        if (! file_exists(base_path('package.json'))) {
            return;
        }

        $configurationKey = $dev ? 'devDependencies' : 'dependencies';

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $packages[$configurationKey] = $callback(
            array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [],
            $configurationKey
        );

        ksort($packages[$configurationKey]);

        file_put_contents(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL
        );
    }

    private function replaceJsStub(string $inFile, string $lookFor, string $replaceWith): void
    {
        (new Filesystem())->put(
            $inFile,
            (string)Str::of((new Filesystem())->get($inFile))
                ->replace($lookFor, $replaceWith
                )
        );
    }

    private function updateJetstreamLayouts(): void
    {
        (new Filesystem())->put(
            resource_path('views/layouts/app.blade.php'),
            str_replace(
                '        @livewireScripts',
                "        @livewireScripts\n" . '        <script src="https://cdn.jsdelivr.net/gh/livewire/turbolinks@v0.1.x/dist/livewire-turbolinks.js" data-turbolinks-eval="false" data-turbo-eval="false"></script>',
                (new Filesystem())->get(resource_path('views/layouts/app.blade.php'))
            )
        );

        // Also add the Livewire scripts to the guest layout. This is done because
        // Livewire and Alpine don't seem to play well with Turbo Drive when it
        // was already started, as app.js is loaded in the guests layout too.

        (new Filesystem())->put(
            resource_path('views/layouts/guest.blade.php'),
            str_replace(
                '        <link rel="stylesheet" href="{{ mix(\'css/app.css\') }}">',
                '        <link rel="stylesheet" href="{{ mix(\'css/app.css\') }}">' .
                "\n        @livewireStyles",
                (new Filesystem())->get(resource_path('views/layouts/guest.blade.php'))
            )
        );

        (new Filesystem())->put(
            resource_path('views/layouts/guest.blade.php'),
            str_replace(
                '    </body>',
                "        @livewireScripts\n" .
                '        <script src="https://cdn.jsdelivr.net/gh/livewire/turbolinks@v0.1.x/dist/livewire-turbolinks.js" data-turbolinks-eval="false" data-turbo-eval="false"></script>' .
                "\n    </body>",
                (new Filesystem())->get(resource_path('views/layouts/guest.blade.php'))
            )
        );
    }
}
