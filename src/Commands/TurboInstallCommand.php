<?php

namespace Tonysm\TurboLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TurboInstallCommand extends Command
{
    const ALPINE_COMMENT = '//=inject-alpine';
    const STIMULUS_COMMENT = '//=inject-stimulus';

    public $signature = 'turbo:install {--jet : To add the Alpine bridges.} {--stimulus : To add Stimulus as an NPM dependency.}';
    public $description = 'Install Turbo.js assets.';

    public function handle()
    {
        $this->updateNodePackages(function ($packages) {
            return [
                '@hotwired/turbo' => '^7.1.0',
                'laravel-echo' => '^1.11.3',
                'pusher-js' => '^7.0.3',
            ] + $packages;
        });

        if ($this->option('stimulus')) {
            $this->updateNodePackages(function ($packages) {
                return [
                    '@hotwired/stimulus' => '^3.0.1',
                    '@hotwired/stimulus-webpack-helpers' => '^1.0.1',
                ] + $packages;
            });
        }

        if ($this->option('jet')) {
            $this->updateNodePackages(function ($packages) {
                return [
                    'alpinejs' => '^3.7.0',
                ] + $packages;
            });

            if (File::exists(resource_path('views/layouts/app.blade.php'))) {
                $this->updateJetstreamLayouts();
            }
        }

        // JS scaffold...
        File::ensureDirectoryExists(resource_path('js/elements'));
        File::ensureDirectoryExists(resource_path('js/libs'));

        File::copy(__DIR__ . '/../../stubs/resources/js/app.js', resource_path('js/app.js'));
        File::copy(__DIR__ . '/../../stubs/resources/js/libs/turbo.js', resource_path('js/libs/turbo.js'));

        if ($this->option('jet')) {
            $this->injectAlpine();
        } else {
            $this->removeJsPlaceholder(self::ALPINE_COMMENT);
        }

        if ($this->option('stimulus')) {
            $this->injectStimulus();
        } else {
            $this->removeJsPlaceholder(self::STIMULUS_COMMENT);
        }

        File::copy(__DIR__ . '/../../stubs/resources/js/bootstrap.js', resource_path('js/bootstrap.js'));
        File::copy(__DIR__ . '/../../stubs/resources/js/elements/turbo-echo-stream-tag.js', resource_path('js/elements/turbo-echo-stream-tag.js'));

        if ($this->option('stimulus')) {
            File::ensureDirectoryExists(resource_path('js/controllers'));
            File::copy(__DIR__ . '/../../stubs/resources/js/controllers/hello_controller.js', resource_path('js/controllers/hello_controller.js'));
        }

        $this->info('Turbo Laravel scaffolding installed successfully.');
        $this->comment('Please execute the "npm install && npm run dev" command to build your assets.');
    }

    private function injectAlpine(): void
    {
        File::copy(__DIR__ . '/../../stubs/resources/js/libs/alpine.js', resource_path('js/libs/alpine.js'));

        $this->replaceJsStub(
            resource_path('js/app.js'),
            self::ALPINE_COMMENT,
            'import \'./libs/alpine\';'
        );
    }

    private function injectStimulus(): void
    {
        File::copy(__DIR__ . '/../../stubs/resources/js/libs/stimulus.js', resource_path('js/libs/stimulus.js'));

        $this->replaceJsStub(
            resource_path('js/app.js'),
            self::STIMULUS_COMMENT,
            'import \'./libs/stimulus\';'
        );
    }

    private function removeJsPlaceholder(string $placeholder): void
    {
        $lines = File::lines(resource_path('js/app.js'))
            ->filter(fn (string $line) => ! Str::contains($line, $placeholder))
            ->implode(PHP_EOL);

        File::put(resource_path('js/app.js'), $lines);
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
        if (! File::exists(base_path('package.json'))) {
            return;
        }

        $configurationKey = $dev ? 'devDependencies' : 'dependencies';

        $packages = json_decode(File::get(base_path('package.json')), true);

        $packages[$configurationKey] = $callback(
            array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [],
            $configurationKey
        );

        ksort($packages[$configurationKey]);

        File::put(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL
        );
    }

    private function replaceJsStub(string $inFile, string $lookFor, string $replaceWith): void
    {
        File::put(
            $inFile,
            (string) Str::of(File::get($inFile))->replace($lookFor, $replaceWith)
        );
    }

    private function updateJetstreamLayouts(): void
    {
        File::put(
            resource_path('views/layouts/app.blade.php'),
            str_replace(
                '        @livewireScripts',
                "        @livewireScripts\n" . '        <script src="https://cdn.jsdelivr.net/gh/livewire/turbolinks@v0.1.4/dist/livewire-turbolinks.js" data-turbolinks-eval="false" data-turbo-eval="false"></script>',
                File::get(resource_path('views/layouts/app.blade.php'))
            )
        );

        // Also add the Livewire scripts to the guest layout. This is done because
        // Livewire and Alpine don't seem to play well with Turbo Drive when it
        // was already started, as app.js is loaded in the guests layout too.

        File::put(
            resource_path('views/layouts/guest.blade.php'),
            str_replace(
                '        <link rel="stylesheet" href="{{ mix(\'css/app.css\') }}">',
                '        <link rel="stylesheet" href="{{ mix(\'css/app.css\') }}">' .
                "\n        @livewireStyles",
                File::get(resource_path('views/layouts/guest.blade.php'))
            )
        );

        File::put(
            resource_path('views/layouts/guest.blade.php'),
            str_replace(
                '    </body>',
                "        @livewireScripts\n" .
                '        <script src="https://cdn.jsdelivr.net/gh/livewire/turbolinks@v0.1.4/dist/livewire-turbolinks.js" data-turbolinks-eval="false" data-turbo-eval="false"></script>' .
                "\n    </body>",
                File::get(resource_path('views/layouts/guest.blade.php'))
            )
        );
    }
}
