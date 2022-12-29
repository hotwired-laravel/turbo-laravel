<?php

namespace Tonysm\TurboLaravel;

use Closure;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SebastianBergmann\CodeCoverage\Report\Html\Renderer;
use Tonysm\TurboLaravel\Broadcasters\Broadcaster;
use Tonysm\TurboLaravel\Broadcasting\PendingBroadcast;
use Tonysm\TurboLaravel\Broadcasting\Rendering;
use Tonysm\TurboLaravel\Models\Naming\Name;

class Turbo
{
    const TURBO_STREAM_FORMAT = 'text/vnd.turbo-stream.html';

    /**
     * This will be used to detect if the request being made is coming from a Turbo Native visit
     * instead of a regular visit. This property will be set on the TurboMiddleware.
     *
     * @var bool
     */
    private bool $visitFromTurboNative = false;

    /**
     * Whether or not the events should broadcast to other users only or to all.
     *
     * @var bool
     */
    private bool $broadcastToOthersOnly = false;

    public function isTurboNativeVisit(): bool
    {
        return $this->visitFromTurboNative;
    }

    public function setVisitingFromTurboNative(): self
    {
        $this->visitFromTurboNative = true;

        return $this;
    }

    /**
     * @param bool|Closure $toOthers
     *
     * @return \Illuminate\Support\HigherOrderTapProxy|mixed
     */
    public function broadcastToOthers($toOthers = true)
    {
        if (is_bool($toOthers)) {
            $this->broadcastToOthersOnly = $toOthers;

            return;
        }

        $this->broadcastToOthersOnly = true;

        if ($toOthers instanceof Closure) {
            return tap($toOthers(), function () {
                $this->broadcastToOthersOnly = false;
            });
        }
    }

    public function shouldBroadcastToOthers(): bool
    {
        return $this->broadcastToOthersOnly;
    }

    public function broadcaster(): Broadcaster
    {
        return resolve(Broadcaster::class);
    }

    public function broadcastAppendTo($content = null, Model|string|null $target = null, ?string $targets = null, Channel|Model|Collection|array|string|null $channel = null)
    {
        return $this->broadcastActionTo('append', $content, $target, $targets, $channel);
    }

    public function broadcastPrependTo($content = null, Model|string|null $target = null, ?string $targets = null, Channel|Model|Collection|array|string|null $channel = null)
    {
        return $this->broadcastActionTo('prepend', $content, $target, $targets, $channel);
    }

    public function broadcastBeforeTo($content = null, Model|string|null $target = null, ?string $targets = null, Channel|Model|Collection|array|string|null $channel = null)
    {
        return $this->broadcastActionTo('before', $content, $target, $targets, $channel);
    }

    public function broadcastAfterTo($content = null, Model|string|null $target = null, ?string $targets = null, Channel|Model|Collection|array|string|null $channel = null)
    {
        return $this->broadcastActionTo('after', $content, $target, $targets, $channel);
    }

    public function broadcastUpdateTo($content = null, Model|string|null $target = null, ?string $targets = null, Channel|Model|Collection|array|string|null $channel = null)
    {
        return $this->broadcastActionTo('update', $content, $target, $targets, $channel);
    }

    public function broadcastReplaceTo($content = null, Model|string|null $target = null, ?string $targets = null, Channel|Model|Collection|array|string|null $channel = null)
    {
        return $this->broadcastActionTo('replace', $content, $target, $targets, $channel);
    }

    public function broadcastRemoveTo(Model|string|null $target = null, ?string $targets = null, Channel|Model|Collection|array|string|null $channel = null)
    {
        return $this->broadcastActionTo('remove', null, $target, $targets, $channel);
    }

    public function broadcastActionTo(string $action, $content = null, Model|string|null $target = null, ?string $targets = null, Channel|Model|Collection|array|string|null $channel = null)
    {
        return new PendingBroadcast(
            $channel ? $this->resolveChannels($channel) : [],
            action: $action,
            target: $target instanceof Model ? $this->resolveTargetFor($target, resource: true) : $target,
            targets: $targets,
            rendering: $this->resolveRendering($content),
        );
    }

    protected function resolveRendering($content)
    {
        if ($content instanceof Rendering) {
            return $content;
        }

        return $content ? Rendering::forContent($content) : Rendering::empty();
    }

    protected function resolveChannels(Channel|Model|Collection|array|string $channel)
    {
        if (is_array($channel) || $channel instanceof Collection) {
            return collect($channel)->map(function ($channel) {
                return $this->resolveChannels($channel);
            })->all();
        }

        if (is_string($channel)) {
            return [new Channel($channel)];
        }

        if ($channel instanceof Model) {
            return $channel->asTurboStreamBroadcastingChannel();
        }

        return $channel;
    }

    protected function resolveTargetFor(Model|string $target, bool $resource = false): string
    {
        if (is_string($target)) {
            return $target;
        }

        if ($resource) {
            return $this->getResourceNameFor($target);
        }

        return dom_id($target);
    }

    protected function getResourceNameFor(Model $model): string
    {
        return Name::forModel($model)->plural;
    }
}
