<?php

namespace HotwiredLaravel\TurboLaravel\Broadcasters;

use Illuminate\Broadcasting\Channel;

interface Broadcaster
{
    /**
     * @param  Channel[]  $channels
     * @param  ?string  $target  = null
     * @param  ?string  $targets  = null
     * @param  ?string  $partial  = null
     * @param  ?array  $partialData  = []
     * @param  ?string  $inlineContent  = null
     * @param  bool  $escapeInlineContent  = true
     * @param  ?string  $exceptSocket  = null
     */
    public function broadcast(
        array $channels,
        bool $later,
        string $action,
        ?string $target = null,
        ?string $targets = null,
        ?string $partial = null,
        ?array $partialData = [],
        ?string $inlineContent = null,
        bool $escapeInlineContent = true,
        array $attributes = [],
        ?string $exceptSocket = null,
    ): void;
}
