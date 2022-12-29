<?php

namespace Tonysm\TurboLaravel;

use Closure;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Tonysm\TurboLaravel\Broadcasters\Broadcaster;
use Tonysm\TurboLaravel\Facades\TurboStream;

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
        return TurboStream::broadcast($action, $content, $target, $targets, $channel);
    }

    public function fakeBroadcasting()
    {
        return TurboStream::fake();
    }

    public function assertBroadcasted($callback)
    {
        return TurboStream::assertBroadcasted($callback);
    }

    public function assertBroadcastedTimes($callback, $times = 1, $message = null)
    {
        return TurboStream::assertBroadcastedTimes($callback, $times, $message);
    }

    public function assertNothingWasBroadcasted()
    {
        return TurboStream::assertNothingWasBroadcasted();
    }
}
