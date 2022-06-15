<?php

namespace Tonysm\TurboLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TurboInstallCommand extends Command
{
    const ALPINE_COMMENT = '//=inject-alpine';
    const STIMULUS_COMMENT = '//=inject-stimulus';

    public $signature = 'turbo:install
        {--alpine : To add Alpine as a JS dependency.}
        {--jet : To update the Jetstream templates.}
        {--stimulus : To add Stimulus as a JS dependency.}
    ';

    public $description = 'Installs Turbo.';

    public function handle()
    {
        if (! $this->usingImportmaps()) {
            $this->updateNpmDependencies();
        } else {
            $this->updateImportmapsDependencies();
        }

        $this->updateTemplates();

        // JS scaffold...
        File::ensureDirectoryExists(resource_path('js/elements'));
        File::ensureDirectoryExists(resource_path('js/libs'));
        File::copy(__DIR__ . '/../../stubs/resources/js/libs/turbo.js', resource_path('js/libs/turbo.js'));
        File::copy(__DIR__ . '/../../stubs/resources/js/bootstrap.js', resource_path('js/bootstrap.js'));
        File::copy(__DIR__ . '/../../stubs/resources/js/elements/turbo-echo-stream-tag.js', resource_path('js/elements/turbo-echo-stream-tag.js'));
        File::copy(__DIR__ . '/../../stubs/resources/js/app.js', resource_path('js/app.js'));

        $this->replaceJsStub(resource_path('js/app.js'), '{IMPORTMAPS_PLACEHOLDER}', $this->usingImportmaps() ? '' : './');

        if ($this->option('alpine') || $this->option('jet')) {
            $this->injectAlpine();
        } else {
            $this->removeJsPlaceholder(self::ALPINE_COMMENT);
        }

        if ($this->option('stimulus')) {
            $this->injectStimulus();
        } else {
            $this->removeJsPlaceholder(self::STIMULUS_COMMENT);
        }

        $this->info('Turbo Laravel scaffolding installed successfully.');

        if (! $this->usingImportmaps()) {
            $this->comment('Please, run "npm install && npm run dev" to build your assets.');
        }
    }

    private function updateNpmDependencies(): void
    {
        $this->comment('Updating NPM dependencies...');

        $this->updateNodePackages(function ($packages) {
            return $this->jsDependencies() + $packages;
        });
    }

    private function updateImportmapsDependencies(): void
    {
        $this->comment('Pulling JS dependencies via Importmaps...');

        $dependencies = array_keys($this->jsDependencies());

        Artisan::call('importmap:pin ' . implode(' ', $dependencies));
    }

    private function jsDependencies(): array
    {
        return [
            '@hotwired/turbo' => '^7.1.0',
            'laravel-echo' => '^1.11.3',
            'pusher-js' => '^7.0.3',
        ] + $this->stimulusDependencies() + $this->alpineDependencies();
    }

    private function stimulusDependencies(): array
    {
        if (! $this->option('stimulus')) {
            return [];
        }

        if ($this->usingImportmaps()) {
            return [
                '@hotwired/stimulus' => '^3.0.1',
            ];
        }

        return [
            '@hotwired/stimulus' => '^3.0.1',
            '@hotwired/stimulus-webpack-helpers' => '^1.0.1',
        ];
    }

    private function alpineDependencies(): array
    {
        if (! $this->option('alpine') && ! $this->option('jet')) {
            return [];
        }

        return [
            'alpinejs' => '^3.7.0',
        ];
    }

    private function usingImportmaps(): bool
    {
        return File::exists(base_path('routes/importmap.php'));
    }

    private function injectAlpine(): void
    {
        File::copy(__DIR__ . '/../../stubs/resources/js/libs/alpine.js', resource_path('js/libs/alpine.js'));

        $this->replaceJsStub(
            resource_path('js/app.js'),
            self::ALPINE_COMMENT,
            sprintf('import "%slibs/alpine";', $this->usingImportmaps() ? '' : './'),
        );
    }

    private function injectStimulus(): void
    {
        $this->comment('Setting up Stimulus.js...');

        File::ensureDirectoryExists(resource_path('js/controllers'));
        File::copy(__DIR__ . '/../../stubs/resources/js/controllers/hello_controller.js', resource_path('js/controllers/hello_controller.js'));

        if ($this->usingImportmaps()) {
            File::copy(__DIR__ . '/../../stubs/resources/js/controllers/index-importmap.js', resource_path('js/controllers/index.js'));
            File::copy(__DIR__ . '/../../stubs/resources/js/libs/stimulus-importmap.js', resource_path('js/libs/stimulus.js'));
        } else {
            File::copy(__DIR__ . '/../../stubs/resources/js/libs/stimulus-webpack.js', resource_path('js/libs/stimulus.js'));
        }

        $this->replaceJsStub(
            resource_path('js/app.js'),
            self::STIMULUS_COMMENT,
            sprintf('import "%slibs/stimulus";', $this->usingImportmaps() ? '' : './'),
        );
    }

    private function updateTemplates(): void
    {
        if (File::exists(resource_path('views/layouts/app.blade.php'))) {
            $this->updateAppLayout();
        }

        if (File::exists(resource_path('views/layouts/guest.blade.php'))) {
            $this->updateGuestLayout();
        }
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

    private function updateAppLayout(): void
    {
        if ($this->option('jet')) {
            $this->comment('Adding the Livewire/Turbo bridge to the "app" layout file...');

            File::put(
                resource_path('views/layouts/app.blade.php'),
                str_replace(
                    '        @livewireScripts',
                    '        @livewireScripts' . "\n" . '        <script src="https://cdn.jsdelivr.net/gh/livewire/turbolinks@v0.1.4/dist/livewire-turbolinks.js" data-turbolinks-eval="false" data-turbo-eval="false"></script>',
                    File::get(resource_path('views/layouts/app.blade.php'))
                )
            );
        }

        if (! str_contains(File::get(resource_path('views/layouts/app.blade.php')), 'csrf-token')) {
            $this->comment('Adding CSRF-Token meta tag in the "app" layout, trying to add it...');

            File::put(
                resource_path('views/layouts/app.blade.php'),
                str_replace(
                    '        <title>',
                    '        <meta name="csrf-token" content="{{ csrf_token() }}">' . "\n" . '        <title>',
                    File::get(resource_path('views/layouts/app.blade.php')),
                ),
            );
        }
    }

    private function updateGuestLayout(): void
    {
        // Also add the Livewire scripts to the guest layout. This is done because
        // Livewire and Alpine don't seem to play well with Turbo Drive when it
        // was already started, as app.js is loaded in the guests layout too.

        if ($this->option('jet')) {
            $this->comment('Adding the Livewire Styles and Livewire/Turbo bridge to the "guest" layout...');

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

        if (! str_contains(File::get(resource_path('views/layouts/guest.blade.php')), 'csrf-token')) {
            $this->comment('Adding CSRF-Token meta tag in the "guest" layout, trying to add it...');

            File::put(
                resource_path('views/layouts/guest.blade.php'),
                str_replace(
                    '        <title>',
                    '        <meta name="csrf-token" content="{{ csrf_token() }}">' . "\n" . '        <title>',
                    File::get(resource_path('views/layouts/guest.blade.php')),
                ),
            );
        }
    }
}
