<?php

namespace Tonysm\TurboLaravel\Testing;

use Tonysm\TurboLaravel\Turbo;

/**
 * @mixin \Illuminate\Foundation\Testing\Concerns\MakesHttpRequests
 */
trait InteractsWithTurbo
{
    public function turbo(): self
    {
        return $this->withHeader('Accept', Turbo::TURBO_STREAM_FORMAT);
    }

    public function turboNative(): self
    {
        return $this->withHeader('User-Agent', 'Turbo Native Android; Mozilla: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.3 Mozilla/5.0 (Macintosh; Intel Mac OS X x.y; rv:42.0) Gecko/20100101 Firefox/43.4');
    }
}
