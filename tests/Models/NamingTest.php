<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Models;

use HotwiredLaravel\TurboLaravel\Models\Naming\Name;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use Workbench\App\Models;

class NamingTest extends TestCase
{
    /** @var Name */
    private $modelName;

    protected function setUp(): void
    {
        parent::setUp();

        $this->modelName = Name::build(Models\User\Profile::class);
    }

    /** @test */
    public function className()
    {
        $this->assertEquals(Models\User\Profile::class, $this->modelName->className);
    }

    /** @test */
    public function classNameWithoutRootNamespace()
    {
        $this->assertEquals('User\\Profile', $this->modelName->classNameWithoutRootNamespace);
    }

    /** @test */
    public function singular()
    {
        $this->assertEquals('user_profile', $this->modelName->singular);
    }

    /** @test */
    public function plural()
    {
        $this->assertEquals('user_profiles', $this->modelName->plural);
    }

    /** @test */
    public function element()
    {
        $this->assertEquals('profile', $this->modelName->element);
    }
}
