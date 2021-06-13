<?php

namespace Tonysm\TurboLaravel\Broadcasters;

use Illuminate\Broadcasting\Channel;
use Tonysm\TurboLaravel\Jobs\BroadcastAction;

class LaravelBroadcaster implements Broadcaster
{
    /**
     * @param Channel[] $channels
     * @param bool $later
     * @param string $target
     * @param string $action
     * @param ?string $partial = null
     * @param ?array $partialData = []
     * @param ?string $exceptSocket = null
     */
    public function broadcast(
        array $channels,
        bool $later,
        string $target,
        string $action,
        ?string $partial = null,
        ?array $partialData = [],
        ?string $exceptSocket = null
    ): void {
        $job = new BroadcastAction(
            $channels,
            $target,
            $action,
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
