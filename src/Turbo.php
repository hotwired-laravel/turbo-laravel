<?php

namespace HotwiredLaravel\TurboLaravel;

use Closure;
use HotwiredLaravel\TurboLaravel\Broadcasters\Broadcaster;

class Turbo
{
    const TURBO_STREAM_FORMAT = 'text/vnd.turbo-stream.html';

    /**
     * This will be used to detect if the request being made is coming from a Hotwire Native visit
     * instead of a regular visit. This property will be set on the TurboMiddleware.
     */
    private bool $visitFromHotwireNative = false;

    /**
     * Whether or not the events should broadcast to other users only or to all.
     */
    private bool $broadcastToOthersOnly = false;

    /**
     * Stores the request ID sent by Turbo in the `X-Turbo-Request-Id` HTTP header.
     */
    private ?string $turboRequestId = null;

    /**
     * @deprecated use isHotwireNativeVisit
     */
    public function isTurboNativeVisit(): bool
    {
        return $this->isHotwireNativeVisit();
    }

    /**
     * @deprecated use setVisitingFromHotwireNative
     */
    public function setVisitingFromTurboNative(): self
    {
        return $this->setVisitingFromHotwireNative();
    }

    public function isHotwireNativeVisit(): bool
    {
        return $this->visitFromHotwireNative;
    }

    public function setVisitingFromHotwireNative(): self
    {
        $this->visitFromHotwireNative = true;

        return $this;
    }

    public function setTurboTrackingRequestId(string $requestId): self
    {
        $this->turboRequestId = $requestId;

        return $this;
    }

    public function currentRequestId(): ?string
    {
        return $this->turboRequestId;
    }

    /**
     * @param  bool|Closure  $toOthers
     * @return \Illuminate\Support\HigherOrderTapProxy|mixed
     */
    public function broadcastToOthers($toOthers = true)
    {
        if (is_bool($toOthers)) {
            $this->broadcastToOthersOnly = $toOthers;

            return null;
        }

        $this->broadcastToOthersOnly = true;

        if ($toOthers instanceof Closure) {
            return tap($toOthers(), function (): void {
                $this->broadcastToOthersOnly = false;
            });
        }

        return null;
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
