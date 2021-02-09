<?php

namespace Tonysm\TurboLaravel\Tests\Models;

use Tonysm\TurboLaravel\Tests\TestCase;
use Tonysm\TurboLaravel\Facades\Turbo;

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
