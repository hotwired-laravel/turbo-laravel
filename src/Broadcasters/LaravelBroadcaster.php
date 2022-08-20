<?php

namespace Tonysm\TurboLaravel\Broadcasters;

use Illuminate\Broadcasting\Channel;
use Tonysm\TurboLaravel\Jobs\BroadcastAction;

class LaravelBroadcaster implements Broadcaster
{
    /**
     * @param Channel[] $channels
     * @param bool $later
     * @param string $action
     * @param ?string $target = null
     * @param ?string $targets = null
     * @param ?string $partial = null
     * @param ?array $partialData = []
     * @param ?string $exceptSocket = null
     */
    public function broadcast(
        array $channels,
        bool $later,
        string $action,
        ?string $target = null,
        ?string $targets = null,
        ?string $partial = null,
        ?array $partialData = [],
        ?string $exceptSocket = null,
    ): void {
        $job = new BroadcastAction(
            $channels,
            $action,
            $target,
            $targets,
            $partial,
            $partialData,
            $exceptSocket,
        );

        if ($later) {
            dispatch($job);
        } else {
            dispatch_sync($job);
        }
    }
}
