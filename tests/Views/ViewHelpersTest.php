<?php

namespace Tonysm\TurboLaravel\Tests\Views;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use function Tonysm\TurboLaravel\dom_id;
use Tonysm\TurboLaravel\Tests\TestCase;
use Tonysm\TurboLaravel\TurboFacade;

class ViewHelpersTest extends TestCase
{
    /** @test */
    public function renders_turbo_native_correctly()
    {
        $this->assertFalse(TurboFacade::isTurboNativeVisit());
        $rendered = View::file(__DIR__ . '/fixtures/turbo_native.blade.php')->render();
        $this->assertTrue(Str::contains($rendered, 'Without Turbo Native'));

        TurboFacade::setVisitingFromTurboNative();
        $this->assertTrue(TurboFacade::isTurboNativeVisit());
        $rendered = View::file(__DIR__ . '/fixtures/turbo_native.blade.php')->render();
        $this->assertTrue(Str::contains($rendered, 'With Turbo Native'));
    }

    /** @test */
    public function renders_dom_id()
    {
        $testModel = Models\TestModel::create(['name' => 'lorem']);

        $renderedDomId = View::file(__DIR__ . '/fixtures/domid.blade.php', ['model' => $testModel])->render();
        $renderedDomIdWithPrefix = View::file(__DIR__ . '/fixtures/domid_with_prefix.blade.php', ['model' => $testModel])->render();
        $rendersDomIdOfNewModel = View::file(__DIR__ . '/fixtures/domid.blade.php', ['model' => new Models\TestModel()])->render();

        $this->assertEquals('<div id="test_model_1"></div>', trim($renderedDomId));
        $this->assertEquals('<div id="favorites_test_model_1"></div>', trim($renderedDomIdWithPrefix));
        $this->assertEquals('<div id="create_test_model"></div>', trim($rendersDomIdOfNewModel));
    }

    /** @test */
    public function renders_dom_class()
    {
        $testModel = Models\TestModel::create(['name' => 'lorem']);

        $renderedDomClass = View::file(__DIR__ . '/fixtures/domclass.blade.php', ['model' => $testModel])->render();
        $renderedDomClassWithPrefix = View::file(__DIR__ . '/fixtures/domclass_with_prefix.blade.php', ['model' => $testModel])->render();
        $rendersDomClassOfNewModel = View::file(__DIR__ . '/fixtures/domclass.blade.php', ['model' => new Models\TestModel()])->render();

        $this->assertEquals('<div class="test_model"></div>', trim($renderedDomClass));
        $this->assertEquals('<div class="favorites_test_model"></div>', trim($renderedDomClassWithPrefix));
        $this->assertEquals('<div class="test_model"></div>', trim($rendersDomClassOfNewModel));
    }

    /** @test */
    public function can_use_helper_function()
    {
        $testModel = Models\TestModel::create(['name' => 'lorem']);

        $this->assertEquals("test_model_1", dom_id($testModel));
        $this->assertEquals("my_context_test_model_1", dom_id($testModel, "my_context"));
    }

    /** @test */
    public function generates_model_ids_for_models_in_nested_folders()
    {
        config()->set('turbo-laravel.models_namespace', [
            __NAMESPACE__.'\\Models\\',
        ]);

        $testModel = Models\TestModel::create(['name' => 'lorem']);

        $this->assertEquals("test_model_1", dom_id($testModel));
        $this->assertEquals("my_context_test_model_1", dom_id($testModel, "my_context"));

        $accountTestModel = Models\Account\TestModel::create(['name' => 'lorem']);

        $this->assertEquals("account_test_model_{$accountTestModel->getKey()}", dom_id($accountTestModel));
        $this->assertEquals("my_context_account_test_model_{$accountTestModel->getKey()}", dom_id($accountTestModel, "my_context"));

        $this->assertEquals("create_account_test_model", dom_id(new Models\Account\TestModel()));
    }
}
