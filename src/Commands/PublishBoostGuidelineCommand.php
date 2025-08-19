<?php

namespace HotwiredLaravel\TurboLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishBoostGuidelineCommand extends Command
{
    public $signature = 'turbo:publish-boost';

    public $description = 'Publishes the Boost Guideline';

    public function handle(): void
    {
        $from = dirname(__DIR__, levels: 2) . DIRECTORY_SEPARATOR . '.ai' . DIRECTORY_SEPARATOR . 'hotwire.blade.php';

        File::ensureDirectoryExists(base_path(implode(DIRECTORY_SEPARATOR, ['.ai', 'guidelines'])), recursive: true);
        File::copy($from, base_path(implode(DIRECTORY_SEPARATOR, ['.ai', 'guidelines', 'hotwire.blade.php'])));

        $this->info('Boost guideline was published!');
    }
}
