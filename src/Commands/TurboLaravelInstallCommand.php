<?php

namespace Tonysm\TurboLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class TurboLaravelInstallCommand extends Command
{
    public $signature = 'turbo:install';
    public $description = 'Install the Turbo resources';

    public function handle()
    {
        $this->updateNodePackages(function ($packages) {
            return [
                    '@hotwired/turbo' => '^7.0.0-beta.1',
                    'stimulus' => '^2.0.0',
                    '@stimulus/webpack-helpers' => '^2.0.0',
                    'laravel-echo' => '^1.10.0',
                    'pusher-js' => '^7.0.2',
                ] + $packages;
        });

        // JS scaffold...
        (new Filesystem())->ensureDirectoryExists(resource_path('js/controllers'));
        copy(__DIR__ . '/../../stubs/resources/js/app.js', resource_path('js/app.js'));
        copy(__DIR__ . '/../../stubs/resources/js/bootstrap.js', resource_path('js/bootstrap.js'));
        copy(__DIR__ . '/../../stubs/resources/js/echo.js', resource_path('js/echo.js'));
        copy(__DIR__ . '/../../stubs/resources/js/turbo-echo-stream-tag.js', resource_path('js/turbo-echo-stream-tag.js'));
        copy(__DIR__ . '/../../stubs/resources/js/controllers/hello_controller.js', resource_path('js/controllers/hello_controller.js'));

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
        if (!file_exists(base_path('package.json'))) {
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
}
