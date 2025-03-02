<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Models;

use HotwiredLaravel\TurboLaravel\Models\Naming\Name;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use Workbench\App\Models;

class NamingTest extends TestCase
{
    private \HotwiredLaravel\TurboLaravel\Models\Naming\Name $modelName;

    protected function setUp(): void
    {
        parent::setUp();

        $this->modelName = Name::build(Models\User\Profile::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function class_name(): void
    {
        $this->assertEquals(Models\User\Profile::class, $this->modelName->className);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function class_name_without_root_namespace(): void
    {
        $this->assertEquals('User\\Profile', $this->modelName->classNameWithoutRootNamespace);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function singular(): void
    {
        $this->assertEquals('user_profile', $this->modelName->singular);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function plural(): void
    {
        $this->assertEquals('user_profiles', $this->modelName->plural);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function element(): void
    {
        $this->assertEquals('profile', $this->modelName->element);
    }
}
