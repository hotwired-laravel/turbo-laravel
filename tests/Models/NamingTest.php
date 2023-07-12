<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Models;

use HotwiredLaravel\TurboLaravel\Models\Naming\Name;
use HotwiredLaravel\TurboLaravel\Tests\Stubs\Models;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;

class NamingTest extends TestCase
{
    /** @var Name */
    private $modelName;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('turbo-laravel.models_namespace', [
            'HotwiredLaravel\\TurboLaravel\\Tests\\Stubs\\Models\\',
        ]);

        $this->modelName = Name::build(Models\Account\TestModel::class);
    }

    /** @test */
    public function className()
    {
        $this->assertEquals(Models\Account\TestModel::class, $this->modelName->className);
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
