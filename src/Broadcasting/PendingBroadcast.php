<?php

namespace Tonysm\TurboLaravel\Broadcasting;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Tonysm\TurboLaravel\Facades\Turbo;
use Tonysm\TurboLaravel\Facades\TurboStream;

class PendingBroadcast
{
    /** @var Channel[] */
    public array $channels;
    public string $action;
    public ?string $target = null;
    public ?string $targets = null;
    public ?string $partialView = null;
    public ?array $partialData = [];
    public bool $sendToOthers = false;
    public bool $sendLater = false;

    /**
     * Indicates whether this pending broadcast was cancelled or not.
     *
     * @var bool
     */
    protected bool $wasCancelled = false;

    /**
     * Indicates whether the broadcasting is being faked or not.
     *
     * @var bool
     */
    protected bool $isRecording = false;

    /**
     * @var ?\Tonysm\TurboLaravel\Broadcasting\Factory $recorded = null
     */
    protected $recorder = null;

    public function __construct(array $channels, string $action, Rendering $rendering, ?string $target = null, ?string $targets = null)
    {
        $this->channels = $channels;
        $this->action = $action;
        $this->target = $target;
        $this->targets = $targets;
        $this->partialView = $rendering->partial;
        $this->partialData = $rendering->data;
    }

    public function to($channel): self
    {
        $this->channels = Arr::wrap($channel);

        return $this;
    }

    public function toPrivateChannel($channel): self
    {
        $this->channels = [new PrivateChannel($channel)];

        return $this;
    }

    public function toPresenceChannel($channel): self
    {
        $this->channels = [new PresenceChannel($channel)];

        return $this;
    }

    public function action(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function target(string $target): self
    {
        $this->target = $target;
        $this->targets = null;

        return $this;
    }

    public function targets(string $targets): self
    {
        $this->targets = $targets;
        $this->target = null;

        return $this;
    }

    public function toOthers(bool $toOthers = true): self
    {
        $this->sendToOthers = $toOthers;

        return $this;
    }

    public function partial(?string $partial, array $data = []): self
    {
        $this->partialView = $partial;
        $this->partialData = $data;

        return $this;
    }

    public function later(bool $later = true): self
    {
        $this->sendLater = $later;

        return $this;
    }

    public function cancel()
    {
        $this->wasCancelled = true;

        return $this;
    }

    public function fake($recorder = null)
    {
        $this->isRecording = true;
        $this->recorder = $recorder;

        return $this;
    }

    public function render(): HtmlString
    {
        return new HtmlString(
            View::make('turbo-laravel::turbo-stream', [
                'action' => $this->action,
                'target' => $this->target,
                'targets' => $this->targets,
                'partial' => $this->partialView ?: null,
                'partialData' => $this->partialData ?: [],
            ])->render()
        );
    }

    public function __destruct()
    {
        if ($this->isRecording) {
            $this->recorder?->record($this);
            return;
        }

        if ($this->wasCancelled) {
            return;
        }

        $broadcaster = Turbo::broadcaster();

        $socket = $this->sendToOthers || Turbo::shouldBroadcastToOthers()
            ? Broadcast::socket()
            : null;

        $broadcaster->broadcast(
            $this->channels,
            $this->sendLater,
            $this->action,
            $this->target,
            $this->targets,
            $this->partialView,
            $this->partialData,
            $socket,
        );
    }
}
