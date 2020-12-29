<?php

namespace Tonysm\TurboLaravel\Commands;

use Illuminate\Console\Command;

class TurboLaravelCommand extends Command
{
    public $signature = 'turbo-laravel';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
