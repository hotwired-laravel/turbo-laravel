<?php

namespace HotwiredLaravel\TurboLaravel\Tests;

use HotwiredLaravel\TurboLaravel\Facades\TurboStream;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        TurboStream::fake();
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testing');

        $app['config']->set('turbo-laravel.models_namespace', [
            'Workbench\\App\\Models\\',
        ]);
    }
}
