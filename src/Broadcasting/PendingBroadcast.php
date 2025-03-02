<?php

namespace HotwiredLaravel\TurboLaravel\Broadcasting;

use HotwiredLaravel\TurboLaravel\Events\TurboStreamBroadcast;
use HotwiredLaravel\TurboLaravel\Facades\Turbo;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\HasBroadcastChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\HtmlString;

class PendingBroadcast
{
    /** @var Channel[] */
    public array $channels;

    public ?string $partialView = null;

    public ?array $partialData = [];

    public ?string $inlineContent = null;

    public bool $escapeInlineContent = true;

    /**
     * Whether we should broadcast only to other users and
     * ignore the current user's broadcasting socket.
     */
    public bool $sendToOthers = false;

    /**
     * Defines if the broadcast should happen sent
     * to a queue or processed right away.
     */
    protected bool $sendLater = false;

    /**
     * Indicates whether this pending broadcast was cancelled or not.
     */
    protected bool $wasCancelled = false;

    /**
     * Indicates whether the broadcasting is being faked or not.
     */
    protected bool $isRecording = false;

    /**
     * This is the testing recorder. Used when faking the Turbo Stream broadcasts.
     *
     * @var ?\HotwiredLaravel\TurboLaravel\Broadcasting\Factory = null
     */
    protected $recorder;

    /**
     * These cancel callbacks will run right before the broadcasting is fired on __destruct.
     *
     * @var array<callable>
     */
    protected array $deferredCancelCallbacks = [];

    public function __construct(array $channels, public string $action, Rendering $rendering, public ?string $target = null, public ?string $targets = null, public array $attributes = [])
    {
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
        return $this->view($partial, $data);
    }

    public function view(?string $view, array $data = []): self
    {
        return $this->rendering(new Rendering($view, $data));
    }

    public function content(\Illuminate\Contracts\View\View|\Illuminate\Support\HtmlString|string $content)
    {
        return $this->rendering(Rendering::forContent($content));
    }

    public function attributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function morph(): self
    {
        return $this->method('morph');
    }

    public function method(?string $method = null): self
    {
        if ($method) {
            return $this->attributes(array_merge($this->attributes, [
                'method' => $method,
            ]));
        }

        return $this->attributes(Arr::except($this->attributes, 'method'));
    }

    public function rendering(Rendering $rendering): static
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

    public function cancel(): static
    {
        $this->wasCancelled = true;

        return $this;
    }

    public function cancelIf($condition): static
    {
        $this->wasCancelled = $this->wasCancelled || boolval(value($condition, $this));

        return $this;
    }

    public function lazyCancelIf(callable $condition): static
    {
        $this->deferredCancelCallbacks[] = $condition;

        return $this;
    }

    public function fake($recorder = null): static
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
        if ($this->shouldBeCancelled()) {
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

    protected function shouldBeCancelled(): bool
    {
        if ($this->wasCancelled) {
            return true;
        }

        foreach ($this->deferredCancelCallbacks as $condition) {
            if (value($condition, $this)) {
                return true;
            }
        }

        return false;
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
