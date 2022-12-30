<?php

namespace Tonysm\TurboLaravel;

use Closure;
use Tonysm\TurboLaravel\Broadcasters\Broadcaster;

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
}
