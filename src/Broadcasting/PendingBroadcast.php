<?php

namespace Tonysm\TurboLaravel\Broadcasting;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\HasBroadcastChannel;
use Illuminate\Database\Eloquent\Model;
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
    public array $attributes = [];

    /**
     * Whether we should broadcast only to other users and
     * ignore the current user's broadcasting socket.
     *
     * @var bool
     */
    public bool $sendToOthers = false;

    /**
     * Defines if the broadcast should happen sent
     * to a queue or processed right away.
     *
     * @var bool
     */
    protected bool $sendLater = false;

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
     * This is the testing recorder. Used when faking the Turbo Stream broadcasts.
     * @var ?\Tonysm\TurboLaravel\Broadcasting\Factory = null
     */
    protected $recorder = null;

    public function __construct(array $channels, string $action, Rendering $rendering, ?string $target = null, ?string $targets = null, array $attributes = [])
    {
        $this->action = $action;
        $this->target = $target;
        $this->targets = $targets;
        $this->attributes = $attributes;

        $this->to($channels);
        $this->rendering($rendering);
    }

    public function to($channel): self
    {
        $this->channels = $this->normalizeChannels($channel, Channel::class);

        return $this;
    }

    public function toPrivateChannel($channel): self
    {
        $this->channels = $this->normalizeChannels($channel, PrivateChannel::class);

        return $this;
    }

    public function toPresenceChannel($channel): self
    {
        $this->channels = $this->normalizeChannels($channel, PresenceChannel::class);

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

    public function attributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
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

    public function cancelIf($condition)
    {
        $this->wasCancelled = boolval(value($condition));

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
            $this->attributes,
        );

        return new HtmlString($event->render());
    }

    public function __destruct()
    {
        if ($this->wasCancelled) {
            return;
        }

        if ($this->isRecording) {
            $this->recorder?->record($this);

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
            $this->attributes,
            $socket,
        );
    }

    protected function normalizeChannels($channel, $channelClass)
    {
        if ($channel instanceof Channel) {
            return [$channel];
        }

        return collect(Arr::wrap($channel))
            ->flatMap(function ($channel) use ($channelClass) {
                if ($channel instanceof Model && method_exists($channel, 'asTurboStreamBroadcastingChannel')) {
                    return $channel->asTurboStreamBroadcastingChannel();
                }

                if ($channel instanceof Channel) {
                    return [$channel];
                }

                return [
                    new $channelClass(
                        $channel instanceof HasBroadcastChannel ? $channel->broadcastChannel() : $channel
                    ),
                ];
            })
            ->values()
            ->filter()
            ->all();
    }
}
