<?php

namespace Tonysm\TurboLaravel\Tests\Views\Components;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Tonysm\TurboLaravel\Tests\TestCase;
use Tonysm\TurboLaravel\Tests\TestModel;
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
        $testModel = TestModel::create(['name' => 'lorem']);

        $renderedDomId = View::file(__DIR__ . '/fixtures/domid.blade.php', ['model' => $testModel])->render();
        $renderedDomIdWithPrefix = View::file(__DIR__ . '/fixtures/domid_with_prefix.blade.php', ['model' => $testModel])->render();
        $rendersDomIdOfNewModel = View::file(__DIR__ . '/fixtures/domid.blade.php', ['model' => new TestModel()])->render();

        $this->assertEquals('<div id="test_model_1"></div>', trim($renderedDomId));
        $this->assertEquals('<div id="favorites_test_model_1"></div>', trim($renderedDomIdWithPrefix));
        $this->assertEquals('<div id="test_model_new"></div>', trim($rendersDomIdOfNewModel));
    }
}
