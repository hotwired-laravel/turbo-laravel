<?php

namespace HotwiredLaravel\TurboLaravel\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;

class TurboStreamBroadcast implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public function __construct(
        /** @var Channel[] */
        public array $channels,
        public string $action,
        public ?string $target = null,
        public ?string $targets = null,
        public ?string $partial = null,
        public ?array $partialData = [],
        public ?string $inlineContent = null,
        public bool $escapeInlineContent = true,
        public array $attrs = []
    ) {}

    public function broadcastOn()
    {
        return $this->channels;
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->render(),
        ];
    }

    public function render(): string
    {
        return View::make('turbo-laravel::turbo-stream', [
            'action' => $this->action,
            'target' => $this->target,
            'targets' => $this->targets,
            'partial' => $this->partial ?: null,
            'partialData' => $this->partialData ?: [],
            'content' => $this->escapeInlineContent ? $this->inlineContent : new HtmlString($this->inlineContent),
            'attrs' => $this->attrs ?: [],
        ])->render();
    }
}
