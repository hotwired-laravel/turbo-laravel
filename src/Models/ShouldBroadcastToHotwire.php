<?php

namespace Tonysm\TurboLaravel\Models;

interface ShouldBroadcastToHotwire
{
    public function hotwireStreamTarget();
    public function hotwireResourcePartialName(): string;
}
