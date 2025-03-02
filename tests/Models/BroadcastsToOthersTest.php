<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Models;

use HotwiredLaravel\TurboLaravel\Facades\Turbo;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;

class BroadcastsToOthersTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function should_broadcast_to_others(): void
    {
        $this->assertFalse(Turbo::shouldBroadcastToOthers());

        Turbo::broadcastToOthers(function (): void {
            $this->assertTrue(Turbo::shouldBroadcastToOthers());
        });

        $this->assertFalse(Turbo::shouldBroadcastToOthers());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function should_broadcast_to_others_forever(): void
    {
        $this->assertFalse(Turbo::shouldBroadcastToOthers());

        Turbo::broadcastToOthers();

        $this->assertTrue(Turbo::shouldBroadcastToOthers());
    }
}
