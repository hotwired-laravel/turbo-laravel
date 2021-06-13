<?php

namespace Tonysm\TurboLaravel\Broadcasting;

use Illuminate\Broadcasting\Channel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Broadcast;
use Tonysm\TurboLaravel\Facades\Turbo;

class PendingBroadcast
{
    /** @var Channel[] */
    public array $channels;
    public string $action;
    public string $target;
    public ?string $partialView = null;
    public ?array $partialData = [];
    public bool $sendToOthers = false;
    public bool $sendLater = false;

    public function __construct(array $channels, string $action, string $target, Rendering $rendering)
    {
        $this->channels = $channels;
        $this->action = $action;
        $this->target = $target;
        $this->partialView = $rendering->partial;
        $this->partialData = $rendering->data;
    }

    public function to($channel): self
    {
        $this->channels = Arr::wrap($channel);

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

    public function __destruct()
    {
        $broadcaster = Turbo::broadcaster();

        $socket = $this->sendToOthers || Turbo::shouldBroadcastToOthers()
            ? Broadcast::socket()
            : null;

        $broadcaster->broadcast(
            $this->channels,
            $this->sendLater,
            $this->target,
            $this->action,
            $this->partialView,
            $this->partialData,
            $socket,
        );
    }
}
