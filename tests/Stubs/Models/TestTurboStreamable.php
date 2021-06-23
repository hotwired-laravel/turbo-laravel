<?php

namespace Tonysm\TurboLaravel\Tests\Stubs\Models;

use Tonysm\TurboLaravel\Views\TurboStreamable;

class TestTurboStreamable implements TurboStreamable
{
    public function getDomId()
    {
        return 'turbo-dom-id';
    }
}
