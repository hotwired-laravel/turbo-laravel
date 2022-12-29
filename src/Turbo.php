<?php

namespace Tonysm\TurboLaravel;

use Closure;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;
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

    public function broadcastAppendTo(Channel|Model|string $channel, $content = null, Model|string|null $target = null, ?string $targets = null)
    {
        return $this->broadcastActionTo($channel, 'append', $content, $target, $targets);
    }

    public function broadcastPrependTo(Channel|Model|string $channel, $content = null, Model|string|null $target = null, ?string $targets = null)
    {
        return $this->broadcastActionTo($channel, 'prepend', $content, $target, $targets);
    }

    public function broadcastBeforeTo(Channel|Model|string $channel, $content = null, Model|string|null $target = null, ?string $targets = null)
    {
        return $this->broadcastActionTo($channel, 'before', $content, $target, $targets);
    }

    public function broadcastAfterTo(Channel|Model|string $channel, $content = null, Model|string|null $target = null, ?string $targets = null)
    {
        return $this->broadcastActionTo($channel, 'after', $content, $target, $targets);
    }

    public function broadcastUpdateTo(Channel|Model|string $channel, $content = null, Model|string|null $target = null, ?string $targets = null)
    {
        return $this->broadcastActionTo($channel, 'update', $content, $target, $targets);
    }

    public function broadcastReplaceTo(Channel|Model|string $channel, $content = null, Model|string|null $target = null, ?string $targets = null)
    {
        return $this->broadcastActionTo($channel, 'replace', $content, $target, $targets);
    }

    public function broadcastActionTo(Channel|Model|string $channel, string $action, $content = null, Model|string|null $target = null, ?string $targets = null)
    {
        return new PendingBroadcast(
            $this->resolveChannels($channel),
            action: $action,
            target: $target instanceof Model ? $this->resolveTargetFor($target, resource: true) : $target,
            targets: $targets,
            rendering: Rendering::forContent($content),
        );
    }

    protected function resolveChannels(Channel|Model|string $channel)
    {
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
