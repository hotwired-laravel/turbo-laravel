<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Broadcasting;

use HotwiredLaravel\TurboLaravel\Broadcasting\Limiter;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;

class LimiterTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function debounces(): void
    {
        $this->freezeTime();

        $debouncer = new Limiter;

        $this->assertFalse($debouncer->shouldLimit('my-key'));
        $this->assertTrue($debouncer->shouldLimit('my-key'));

        $this->travel(501)->milliseconds();

        $this->assertFalse($debouncer->shouldLimit('my-key'));
        $this->assertTrue($debouncer->shouldLimit('my-key'));
    }
}
