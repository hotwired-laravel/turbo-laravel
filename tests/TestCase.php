<?php

namespace Tonysm\TurboLaravel\Tests;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\App;
use Orchestra\Testbench\TestCase as Orchestra;
use Tonysm\TurboLaravel\TurboServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    protected function getPackageProviders($app)
    {
        return [
            TurboServiceProvider::class,
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
            __NAMESPACE__.'\\Stubs\\Models\\',
        ]);
    }

    private function setUpDatabase(Application $app): void
    {
        $app['db']->connection()->getSchemaBuilder()->create('broadcast_test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->foreignId('parent_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function onLaravel9(Closure $test, string $skippedMessage)
    {
        if (version_compare(App::version(), '9.0') >= 0) {
            return $test();
        } else {
            $this->markTestSkipped($skippedMessage);
        }
    }
}
