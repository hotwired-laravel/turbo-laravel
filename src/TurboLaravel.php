<?php

namespace Tonysm\TurboLaravel;

class TurboLaravel
{
    /**
     * This will be used to detect if the request being made is coming from a Turbo Native visit
     * instead of a regular visit. This property will be set on the TurboMiddleware.
     *
     * @var bool
     */
    private bool $visitFromTurboNative = false;

    public function isTurboNativeVisit(): bool
    {
        return $this->visitFromTurboNative;
    }

    public function setVisitingFromTurboNative(): self
    {
        $this->visitFromTurboNative = true;

        return $this;
    }
}
