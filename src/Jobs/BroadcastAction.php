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
    public string $action;
    public ?string $target = null;
    public ?string $targets = null;
    public ?string $partial = null;
    public ?array $partialData = null;
    public ?string $socket = null;

    public function __construct(array $channels, string $action, ?string $target = null, ?string $targets = null, ?string $partial = null, ?array $partialData = [], $socket = null)
    {
        $this->channels = $channels;
        $this->action = $action;
        $this->target = $target;
        $this->targets = $targets;
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
            $this->action,
            $this->target,
            $this->targets,
            $this->partial,
            $this->partialData,
        );

        $event->socket = $this->socket;

        return $event;
    }
}
