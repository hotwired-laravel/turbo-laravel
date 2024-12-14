<?php

namespace HotwiredLaravel\TurboLaravel\Testing;

use HotwiredLaravel\TurboLaravel\Turbo;

/**
 * @mixin \Illuminate\Foundation\Testing\Concerns\MakesHttpRequests
 */
trait InteractsWithTurbo
{
    public function turbo(): self
    {
        return $this->withHeader('Accept', Turbo::TURBO_STREAM_FORMAT);
    }

    /**
     * @deprecated use hotwireNative (but the User-Agent will change when yo do that!)
     */
    public function turboNative(): self
    {
        return $this->withHeader('User-Agent', 'Turbo Native Android; Mozilla: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.3 Mozilla/5.0 (Macintosh; Intel Mac OS X x.y; rv:42.0) Gecko/20100101 Firefox/43.4');
    }

    public function hotwireNative(): self
    {
        return $this->withHeader('User-Agent', 'Hotwire Native Android; Mozilla: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.3 Mozilla/5.0 (Macintosh; Intel Mac OS X x.y; rv:42.0) Gecko/20100101 Firefox/43.4');
    }

    public function fromTurboFrame(string $frame): self
    {
        return $this->withHeader('Turbo-Frame', $frame);
    }
}
