<?php

namespace Tonysm\TurboLaravel\Tests\Models;

use Tonysm\TurboLaravel\Models\Naming\Name;
use Tonysm\TurboLaravel\Tests\TestCase;
use Tonysm\TurboLaravel\Tests\TestModel;

class NamingTest extends TestCase
{
    /** @var Name */
    private $modelName;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('turbo-laravel.models_namespace', [
            'Tonysm\\TurboLaravel\\Tests\\Views\\Models\\',
        ]);

        $this->modelName = Name::build(\Tonysm\TurboLaravel\Tests\Views\Models\Account\TestModel::class);
    }

    /** @test */
    public function className()
    {
        $this->assertEquals(\Tonysm\TurboLaravel\Tests\Views\Models\Account\TestModel::class, $this->modelName->className);
    }

    /** @test */
    public function classNameWithoutRootNamespace()
    {
        $this->assertEquals('Account\\TestModel', $this->modelName->classNameWithoutRootNamespace);
    }

    /** @test */
    public function singular()
    {
        $this->assertEquals('account_test_model', $this->modelName->singular);
    }

    /** @test */
    public function plural()
    {
        $this->assertEquals('account_test_models', $this->modelName->plural);
    }

    /** @test */
    public function element()
    {
        $this->assertEquals('test_model', $this->modelName->element);
    }
}
