<?php

namespace HotwiredLaravel\TurboLaravel\Jobs;

use HotwiredLaravel\TurboLaravel\Events\TurboStreamBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastAction implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    public function __construct(public array $channels, public string $action, public ?string $target = null, public ?string $targets = null, public ?string $partial = null, public ?array $partialData = [], public ?string $inlineContent = null, public bool $escapeInlineContent = true, public array $attributes = [], public ?string $socket = null) {}

    public function handle(): void
    {
        broadcast($this->asEvent());
    }

    public function asEvent(): \HotwiredLaravel\TurboLaravel\Events\TurboStreamBroadcast
    {
        $event = new TurboStreamBroadcast(
            $this->channels,
            $this->action,
            $this->target,
            $this->targets,
            $this->partial,
            $this->partialData,
            $this->inlineContent,
            $this->escapeInlineContent,
            $this->attributes,
        );

        $event->socket = $this->socket;

        return $event;
    }
}
