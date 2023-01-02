<?php

namespace Tonysm\TurboLaravel\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;

class TurboStreamBroadcast implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    /** @var Channel[] */
    public array $channels;
    public string $action;
    public ?string $target = null;
    public ?string $targets = null;
    public ?string $partial = null;
    public ?array $partialData = [];
    public ?string $inlineContent = null;
    public array $attrs = [];
    public bool $escapeInlineContent = true;

    public function __construct(array $channels, string $action, ?string $target = null, ?string $targets = null, ?string $partial = null, ?array $partialData = [], ?string $inlineContent = null, bool $escapeInlineContent = true, array $attributes = [])
    {
        $this->channels = $channels;
        $this->action = $action;
        $this->target = $target;
        $this->targets = $targets;
        $this->partial = $partial;
        $this->partialData = $partialData;
        $this->inlineContent = $inlineContent;
        $this->escapeInlineContent = $escapeInlineContent;
        $this->attrs = $attributes;
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
