<?php

namespace Tonysm\TurboLaravel\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\View;

class TurboStreamBroadcast implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    /** @var Channel[] */
    public array $channels;
    public string $target;
    public string $action;
    public ?string $partial = null;
    public ?array $partialData = [];

    public function __construct(array $channels, string $target, string $action, ?string $partial = null, ?array $partialData = [])
    {
        $this->channels = $channels;
        $this->target = $target;
        $this->action = $action;
        $this->partial = $partial;
        $this->partialData = $partialData;
    }

    public function broadcastOn()
    {
        return $this->channels;
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->render(),
        ];
    }

    public function render(): string
    {
        return View::make('turbo-laravel::turbo-stream', [
            'target' => $this->target,
            'action' => $this->action,
            'partial' => $this->partial ?: null,
            'partialData' => $this->partialData ?: [],
        ])->render();
    }
}
