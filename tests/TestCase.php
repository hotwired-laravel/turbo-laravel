<?php

namespace HotwiredLaravel\TurboLaravel\Tests;

use HotwiredLaravel\TurboLaravel\TurboServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Workbench\App\Providers\WorkbenchAppServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(dirname(__DIR__).'/workbench/database/migrations/');
    }

    protected function getPackageProviders($app)
    {
        return [
            TurboServiceProvider::class,
            WorkbenchAppServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');

        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('turbo-laravel.models_namespace', [
            'Workbench\\App\\Models\\',
        ]);
    }
}
