<?php

namespace Tonysm\TurboLaravel\Tests\Views\Components;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Tonysm\TurboLaravel\Tests\TestCase;
use Tonysm\TurboLaravel\Tests\TestModel;
use Tonysm\TurboLaravel\TurboLaravelFacade;

class TurboComponentsTest extends TestCase
{
    /** @test */
    public function renders_frame_component()
    {
        $expected = <<<'HTML'
<turbo-frame id="my_frame">
    <h1>Hello from Frame!</h1>
</turbo-frame>
HTML;

        $rendered = View::file(__DIR__ . '/fixtures/frame.blade.php')->render();

        $this->assertEquals($expected, trim($rendered));
    }

    /** @test */
    public function renders_turbo_native_correctly()
    {
        $this->assertFalse(TurboLaravelFacade::isTurboNativeVisit());
        $rendered = View::file(__DIR__ . '/fixtures/turbo_native.blade.php')->render();
        $this->assertTrue(Str::contains($rendered, 'Without Turbo Native'));

        TurboLaravelFacade::setVisitingFromTurboNative();
        $this->assertTrue(TurboLaravelFacade::isTurboNativeVisit());
        $rendered = View::file(__DIR__ . '/fixtures/turbo_native.blade.php')->render();
        $this->assertTrue(Str::contains($rendered, 'With Turbo Native'));
    }

    /** @test */
    public function renders_dom_id()
    {
        $testModel = TestModel::create(['name' => 'lorem']);

        $renderedDomId = View::file(__DIR__ . '/fixtures/domid.blade.php', ['model' => $testModel])->render();
        $renderedDomIdWithPrefix = View::file(__DIR__ . '/fixtures/domid_with_prefix.blade.php', ['model' => $testModel])->render();

        $this->assertEquals('<div id="test_model_1"></div>', trim($renderedDomId));
        $this->assertEquals('<div id="favorites_test_model_1"></div>', trim($renderedDomIdWithPrefix));
    }
}
