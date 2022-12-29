<?php

namespace Tonysm\TurboLaravel\Broadcasting;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\HtmlString;
use Tonysm\TurboLaravel\Events\TurboStreamBroadcast;
use Tonysm\TurboLaravel\Facades\Turbo;

class PendingBroadcast
{
    /** @var Channel[] */
    public array $channels;
    public string $action;
    public ?string $target = null;
    public ?string $targets = null;
    public ?string $partialView = null;
    public ?array $partialData = [];
    public ?string $inlineContent = null;
    public bool $escapeInlineContent = true;
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
     * @var ?\Tonysm\TurboLaravel\Broadcasting\Factory = null
     */
    protected $recorder = null;

    public function __construct(array $channels, string $action, Rendering $rendering, ?string $target = null, ?string $targets = null)
    {
        $this->channels = $channels;
        $this->action = $action;
        $this->target = $target;
        $this->targets = $targets;

        $this->rendering($rendering);
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
        return $this->rendering(new Rendering($partial, $data));
    }

    public function content($content)
    {
        return $this->rendering(Rendering::forContent($content));
    }

    public function rendering(Rendering $rendering)
    {
        $this->partialView = $rendering->partial;
        $this->partialData = $rendering->data;
        $this->inlineContent = $rendering->inlineContent;
        $this->escapeInlineContent = $rendering->escapeInlineContent;

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
        $event = new TurboStreamBroadcast(
            $this->channels,
            $this->action,
            $this->target,
            $this->targets,
            $this->partialView,
            $this->partialData,
            $this->inlineContent,
            $this->escapeInlineContent,
        );

        return new HtmlString($event->render());
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
            $this->inlineContent,
            $this->escapeInlineContent,
            $socket,
        );
    }
}
