<?php

namespace Tonysm\TurboLaravel\Http;

use Tonysm\TurboLaravel\Turbo;

class TurboResponseFactory
{
    public static function makeStream($content, int $status = 200)
    {
        return response($content, $status, ['Content-Type' => Turbo::TURBO_STREAM_FORMAT]);
    }
}
