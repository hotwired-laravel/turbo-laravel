<?php

namespace Tonysm\TurboLaravel\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Tonysm\TurboLaravel\Events\TurboStreamBroadcast;

class BroadcastAction implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    public array $channels;
    public string $target;
    public string $action;
    public ?string $partial;
    public ?array $partialData;
    public ?string $socket;

    public function __construct(array $channels, string $target, string $action, ?string $partial = null, ?array $partialData = [], $socket = null)
    {
        $this->channels = $channels;
        $this->target = $target;
        $this->action = $action;
        $this->partial = $partial;
        $this->partialData = $partialData;
        $this->socket = $socket;
    }

    public function handle()
    {
        broadcast($this->asEvent());
    }

    public function asEvent()
    {
        $event = new TurboStreamBroadcast(
            $this->channels,
            $this->target,
            $this->action,
            $this->partial,
            $this->partialData
        );

        $event->socket = $this->socket;

        return $event;
    }
}
