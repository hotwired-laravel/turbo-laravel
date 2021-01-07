<?php

namespace Tonysm\TurboLaravel\Tests\Models;

use Tonysm\TurboLaravel\Tests\TestCase;
use Tonysm\TurboLaravel\TurboFacade;

class BroadcastsToOthersTest extends TestCase
{
    /** @test */
    public function should_broadcast_to_others()
    {
        $this->assertFalse(TurboFacade::shouldBroadcastToOthers());

        TurboFacade::broadcastToOthers(function () {
            $this->assertTrue(TurboFacade::shouldBroadcastToOthers());
        });

        $this->assertFalse(TurboFacade::shouldBroadcastToOthers());
    }

    /** @test */
    public function should_broadcast_to_others_forever()
    {
        $this->assertFalse(TurboFacade::shouldBroadcastToOthers());

        TurboFacade::broadcastToOthers();

        $this->assertTrue(TurboFacade::shouldBroadcastToOthers());
    }
}
