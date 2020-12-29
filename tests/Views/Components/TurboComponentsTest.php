<?php

namespace Tonysm\TurboLaravel\Tests\Views\Components;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Tonysm\TurboLaravel\Tests\TestCase;
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

        $rendered = View::file(__DIR__.'/fixtures/frame.blade.php')->render();

        $this->assertEquals($expected, trim($rendered));
    }

    /** @test */
    public function renders_turbo_native_correctly()
    {
        $this->assertFalse(TurboLaravelFacade::isTurboNativeVisit());
        $rendered = View::file(__DIR__.'/fixtures/turbo_native.blade.php')->render();
        $this->assertTrue(Str::contains($rendered, 'Without Turbo Native'));

        TurboLaravelFacade::setVisitingFromTurboNative();
        $this->assertTrue(TurboLaravelFacade::isTurboNativeVisit());
        $rendered = View::file(__DIR__.'/fixtures/turbo_native.blade.php')->render();
        $this->assertTrue(Str::contains($rendered, 'With Turbo Native'));
    }
}
