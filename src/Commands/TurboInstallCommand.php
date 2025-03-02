<?php

namespace HotwiredLaravel\TurboLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process as ProcessFacade;
use RuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class TurboInstallCommand extends Command
{
    public $signature = 'turbo:install';

    public $description = 'Installs Turbo.';

    public function handle(): void
    {
        $this->updateLayouts();
        $this->publishJsFiles();
        $this->installJsDependencies();

        $this->newLine();
        $this->components->info('Turbo Laravel was installed successfully.');
    }

    private function publishJsFiles(): void
    {
        File::ensureDirectoryExists(resource_path('js/elements'));
        File::ensureDirectoryExists(resource_path('js/libs'));

        File::copy(__DIR__.'/../../stubs/resources/js/libs/turbo.js', resource_path('js/libs/turbo.js'));
        File::copy(__DIR__.'/../../stubs/resources/js/elements/turbo-echo-stream-tag.js', resource_path('js/elements/turbo-echo-stream-tag.js'));

        File::put(resource_path('js/app.js'), $this->appJsImportLines());
        File::put(resource_path('js/libs/index.js'), $this->libsIndexJsImportLines());
    }

    private function appJsImportLines(): string
    {
        $prefix = $this->usingImportmaps() ? '' : './';

        $imports = [
            "import '{$prefix}bootstrap';",
            "import '{$prefix}elements/turbo-echo-stream-tag';",
            "import '{$prefix}libs';",
        ];

        return implode("\n", $imports);
    }

    private function libsIndexJsImportLines(): string
    {
        $imports = [];

        $imports[] = $this->usingImportmaps()
            ? "import 'libs/turbo';"
            : "import './turbo';";

        return implode("\n", $imports);
    }

    private function installJsDependencies(): void
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
        static::updateNodePackages(fn ($packages): array => $this->jsDependencies() + $packages);
    }

    private function runInstallAndBuildCommand(): void
    {
        if (file_exists(base_path('pnpm-lock.yaml'))) {
            $this->runCommands(['pnpm install', 'pnpm run build']);
        } elseif (file_exists(base_path('yarn.lock'))) {
            $this->runCommands(['yarn install', 'yarn run build']);
        } elseif (file_exists(base_path('bun.lockb'))) {
            $this->runCommands(['bun install', 'bun run build']);
        } else {
            $this->runCommands(['npm install', 'npm run build']);
        }
    }

    private function runCommands(array $commands): void
    {
        $process = Process::fromShellCommandline(implode(' && ', $commands), null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->output->writeln('  <bg=yellow;fg=black> WARN </> '.$e->getMessage().PHP_EOL);
            }
        }

        $process->run(function ($type, string $line): void {
            $this->output->write('    '.$line);
        });
    }

    private function updateImportmapsDependencies(): void
    {
        $dependencies = array_keys($this->jsDependencies());

        ProcessFacade::forever()->run(array_merge([
            $this->phpBinary(),
            'artisan',
            'importmap:pin',
        ], $dependencies), fn ($_type, $output) => $this->output->write($output));
    }

    private function jsDependencies(): array
    {
        return [
            '@hotwired/turbo' => '^8.0.4',
            'laravel-echo' => '^1.15.0',
            'pusher-js' => '^8.0.1',
        ];
    }

    private function usingImportmaps(): bool
    {
        return File::exists(base_path('routes/importmap.php'));
    }

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
        $this->existingLayoutFiles()->each(fn ($file) => (new Pipeline(app()))
            ->send($file)
            ->through(array_filter([
                Tasks\EnsureCsrfTokenMetaTagExists::class,
            ]))
            ->thenReturn());
    }

    private function existingLayoutFiles()
    {
        return collect(['app', 'guest'])
            ->map(fn ($file) => resource_path("views/layouts/{$file}.blade.php"))
            ->filter(fn ($file) => File::exists($file));
    }

    private function phpBinary(): string
    {
        return (new PhpExecutableFinder)->find(false) ?: 'php';
    }
}
