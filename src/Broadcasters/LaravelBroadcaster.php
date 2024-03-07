<?php

namespace HotwiredLaravel\TurboLaravel\Broadcasters;

use HotwiredLaravel\TurboLaravel\Jobs\BroadcastAction;

class LaravelBroadcaster implements Broadcaster
{
    /**
     * @param  \Illuminate\Broadcasting\Channel[]  $channels
     * @param  ?string  $target  = null
     * @param  ?string  $targets  = null
     * @param  ?string  $partial  = null
     * @param  ?array  $partialData  = []
     * @param  ?string  $inlineContent  = null
     * @param  bool  $escapeInlineContent  = true
     * @param  array  $attributes  = []
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
    ): void {
        $job = new BroadcastAction(
            $channels,
            $action,
            $target,
            $targets,
            $partial,
            $partialData,
            $inlineContent,
            $escapeInlineContent,
            $attributes,
            $exceptSocket,
        );

        if ($later) {
            dispatch($job);
        } else {
            dispatch_sync($job);
        }
    }
}
