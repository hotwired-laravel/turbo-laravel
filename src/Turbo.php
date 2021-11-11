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

    /**
     * Whether or not the turbo middleware should be automatically added to the "web" middleware group stack.
     *
     * @var bool
     */
    private bool $registerMiddleware = true;

    public function isTurboNativeVisit(): bool
    {
        return $this->visitFromTurboNative;
    }

    public function setVisitingFromTurboNative(): self
    {
        $this->visitFromTurboNative = true;

        return $this;
    }

    public function withoutRegisteringMiddleware(): self
    {
        $this->registerMiddleware = true;

        return $this;
    }

    public function shouldRegisterMiddleware(): bool
    {
        return $this->registerMiddleware;
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
