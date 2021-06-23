<?php

namespace Tonysm\TurboLaravel\Tests;

use Tonysm\TurboLaravel\Views\TurboStreamable;

class TestTurboStreamable implements TurboStreamable
{
    public function getDomId()
    {
        return 'turbo-dom-id';
    }
}
