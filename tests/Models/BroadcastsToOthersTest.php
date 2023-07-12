<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Models;

use HotwiredLaravel\TurboLaravel\Facades\Turbo;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;

class BroadcastsToOthersTest extends TestCase
{
    /** @test */
    public function should_broadcast_to_others()
    {
        $this->assertFalse(Turbo::shouldBroadcastToOthers());

        Turbo::broadcastToOthers(function () {
            $this->assertTrue(Turbo::shouldBroadcastToOthers());
        });

        $this->assertFalse(Turbo::shouldBroadcastToOthers());
    }

    /** @test */
    public function should_broadcast_to_others_forever()
    {
        $this->assertFalse(Turbo::shouldBroadcastToOthers());

        Turbo::broadcastToOthers();

        $this->assertTrue(Turbo::shouldBroadcastToOthers());
    }
}
