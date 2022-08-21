<?php

namespace Tonysm\TurboLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Terminal;

class TurboInstallCommand extends Command
{
    public $signature = 'turbo:install
        {--alpine : To add Alpine as a JS dependency.}
        {--jet : To update the Jetstream templates.}
    ';

    public $description = 'Installs Turbo.';

    private $afterMessages = [];

    public function handle()
    {
        $this->displayHeader('Installing Turbo Laravel', '<bg=blue;fg=black> INFO </>');
        $this->installJsDependencies();
        $this->updateTemplates();
        $this->publishJsFiles();

        $this->displayAfterNotes();

        $this->newLine();
        $this->line(' <fg=white>Done!</>');
    }

    private function publishJsFiles()
    {
        $this->displayTask('updating JS files', function () {
            File::ensureDirectoryExists(resource_path('js/elements'));
            File::ensureDirectoryExists(resource_path('js/libs'));

            File::copy(__DIR__ . '/../../stubs/resources/js/libs/turbo.js', resource_path('js/libs/turbo.js'));
            File::copy(__DIR__ . '/../../stubs/resources/js/elements/turbo-echo-stream-tag.js', resource_path('js/elements/turbo-echo-stream-tag.js'));

            if ($this->option('alpine') || $this->option('jet')) {
                File::copy(__DIR__ . '/../../stubs/resources/js/libs/alpine.js', resource_path('js/libs/alpine.js'));
            }

            $imports = $this->appJsImportLines();

            File::put(
                $appJsFile = resource_path('js/app.js'),
                preg_replace(
                    '/(.*[\'"](?:\.\/)?bootstrap[\'"]\)?;?)/',
                    <<<JS
                    \\1
                    {$imports}
                    JS,
                    File::get($appJsFile),
                ),
            );


            return self::SUCCESS;
        });
    }

    private function appJsImportLines()
    {
        $prefix = $this->usingImportmaps() ? '' : './';

        $imports = [
            "import '{$prefix}elements/turbo-echo-stream-tag';",
            "import '{$prefix}libs/turbo';",
        ];

        if ($this->option('alpine') || $this->option('jet')) {
            $imports[] = "import '{$prefix}libs/alpine';";
        }

        return implode("\n", $imports);
    }

    private function installJsDependencies()
    {
        if (! $this->usingImportmaps()) {
            $this->updateNpmDependencies();
        } else {
            $this->updateImportmapsDependencies();
        }
    }

    private function updateNpmDependencies(): void
    {
        $this->displayTask('updating NPM dependecies', function () {
            $this->afterMessages[] = '<fg=white>Run: `<fg=yellow>npm install && npm run dev</>`</>';

            $this->updateNodePackages(function ($packages) {
                return $this->jsDependencies() + $packages;
            });

            return self::SUCCESS;
        });
    }

    private function updateImportmapsDependencies(): void
    {
        $this->displayTask('pinning JS dependencies (Importmap)', function () {
            $dependencies = array_keys($this->jsDependencies());

            return Artisan::call('importmap:pin ' . implode(' ', $dependencies));
        });
    }

    private function jsDependencies(): array
    {
        return [
            '@hotwired/turbo' => '^7.1.0',
            'laravel-echo' => '^1.11.3',
            'pusher-js' => '^7.0.3',
        ] + $this->alpineDependencies();
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

    private function updateTemplates(): void
    {
        $this->displayTask('updating templates', function () {
            $this->updateLayouts();

            return self::SUCCESS;
        });
    }

    /**
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

    private function updateLayouts(): void
    {
        $this->existingLayoutFiles()
            ->each(
                fn ($file) => (new Pipeline(app()))
                ->send($file)
                ->through(array_filter([
                    $this->option('jet') ? Tasks\EnsureLivewireTurboBridgeExists::class : null,
                    Tasks\EnsureCsrfTokenMetaTagExists::class,
                ]))
                ->thenReturn()
            );

        if ($this->option('jet')) {
            $this->afterMessages[] = '<fg=white>Ensured the Livewire/Turbo bridge was added to your layout files.</>';
            $this->afterMessages[] = '<fg=white>Ensured the Livewire scripts and styles were added to your `guest` layout.</>';
        }

        $this->afterMessages[] = '<fg=white>Ensured a CSRF Token meta tag exists in your layout files.</>';
    }

    private function displayHeader($text, $prefix)
    {
        $this->newLine();
        $this->line(sprintf(' %s <fg=white>%s</>  ', $prefix, $text));
        $this->newLine();
    }

    private function displayTask($description, $task)
    {
        $width = (new Terminal())->getWidth();
        $dots = max(str_repeat('<fg=gray>.</>', $width - strlen($description) - 13), 0);
        $this->output->write(sprintf('    <fg=white>%s</> %s ', $description, $dots));
        $output = $task();

        if ($output === self::SUCCESS) {
            $this->output->write('<info>DONE</info>');
        } elseif ($output === self::FAILURE) {
            $this->output->write('<error>FAIL</error>');
        } elseif ($output === self::INVALID) {
            $this->output->write('<fg=yellow>WARN</>');
        }

        $this->newLine();
    }

    private function displayAfterNotes()
    {
        if (count($this->afterMessages) > 0) {
            $this->displayHeader('After Notes & Next Steps', '<bg=yellow;fg=black> NOTES </>');

            foreach ($this->afterMessages as $message) {
                $this->line('    '.$message);
            }
        }
    }

    private function existingLayoutFiles()
    {
        return collect(['app', 'guest'])
            ->map(fn ($file) => resource_path("views/layouts/{$file}.blade.php"))
            ->filter(fn ($file) => File::exists($file));
    }
}
