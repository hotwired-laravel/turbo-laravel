<?php

namespace HotwiredLaravel\TurboLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Console\Terminal;
use Symfony\Component\Process\Process;

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
        $this->updateTemplates();
        $this->publishJsFiles();
        $this->installJsDependencies();

        $this->displayAfterNotes();

        $this->newLine();
        $this->line(' <fg=white>Done!</>');
    }

    private function publishJsFiles()
    {
        $this->displayTask('updating JS files', function () {
            File::ensureDirectoryExists(resource_path('js/elements'));
            File::ensureDirectoryExists(resource_path('js/libs'));

            File::copy(__DIR__.'/../../stubs/resources/js/libs/turbo.js', resource_path('js/libs/turbo.js'));
            File::copy(__DIR__.'/../../stubs/resources/js/elements/turbo-echo-stream-tag.js', resource_path('js/elements/turbo-echo-stream-tag.js'));

            if ($this->option('jet')) {
                File::copy(__DIR__.'/../../stubs/resources/js/libs/alpine-jet.js', resource_path('js/libs/alpine.js'));
            } elseif ($this->option('alpine')) {
                File::copy(__DIR__.'/../../stubs/resources/js/libs/alpine.js', resource_path('js/libs/alpine.js'));
            }

            File::put(resource_path('js/app.js'), $this->appJsImportLines());
            File::put(resource_path('js/libs/index.js'), $this->libsIndexJsImportLines());

            return self::SUCCESS;
        });
    }

    private function appJsImportLines()
    {
        $prefix = $this->usingImportmaps() ? '' : './';

        $imports = [
            "import '{$prefix}bootstrap';",
            "import '{$prefix}elements/turbo-echo-stream-tag';",
            "import '{$prefix}libs';",
        ];

        return implode("\n", $imports);
    }

    private function libsIndexJsImportLines()
    {
        $imports = [];

        $imports[] = $this->usingImportmaps()
            ? "import 'libs/turbo';"
            : "import './turbo';";

        if ($this->option('alpine') || $this->option('jet')) {
            $imports[] = $this->usingImportmaps()
                ? "import 'libs/alpine';"
                : "import './alpine';";
        }

        return implode("\n", $imports);
    }

    private function installJsDependencies()
    {
        if ($this->usingImportmaps()) {
            $this->updateImportmapsDependencies();
        } else {
            $this->updateNpmDependencies();
            $this->runInstallAndBuildCommand();
        }
    }

    private function updateNpmDependencies(): void
    {
        $this->displayTask('updating NPM dependencies', function () {
            $this->afterMessages[] = '<fg=white>Run: `<fg=yellow>npm install && npm run build</>`</>';

            $this->updateNodePackages(function ($packages) {
                return $this->jsDependencies() + $packages;
            });

            return self::SUCCESS;
        });
    }

    private function runInstallAndBuildCommand(): void
    {
        if (file_exists(base_path('pnpm-lock.yaml'))) {
            $this->runCommands(['pnpm install', 'pnpm run build']);
        } elseif (file_exists(base_path('yarn.lock'))) {
            $this->runCommands(['yarn install', 'yarn run build']);
        } else {
            $this->runCommands(['npm install', 'npm run build']);
        }
    }

    private function runCommands($commands): void
    {
        $process = Process::fromShellCommandline(implode(' && ', $commands), null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->output->writeln('  <bg=yellow;fg=black> WARN </> '.$e->getMessage().PHP_EOL);
            }
        }

        $process->run(function ($type, $line) {
            $this->output->write('    '.$line);
        });
    }

    private function updateImportmapsDependencies(): void
    {
        $this->displayTask('pinning JS dependencies (Importmap)', function () {
            $dependencies = array_keys($this->jsDependencies());

            return Artisan::call('importmap:pin '.implode(' ', $dependencies));
        });
    }

    private function jsDependencies(): array
    {
        return [
            '@hotwired/turbo' => '^7.2.5',
            'laravel-echo' => '^1.15.0',
            'pusher-js' => '^8.0.1',
        ] + $this->alpineDependencies();
    }

    private function alpineDependencies(): array
    {
        if (! $this->option('alpine') && ! $this->option('jet')) {
            return [];
        }

        return [
            'alpinejs' => '^3.11.1',
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
     * @param  bool  $dev
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
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL
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
