<?php

namespace Tonysm\TurboLaravel\Http;

class TurboResponseFactory
{
    public static function makeStream($content, int $status = 200)
    {
        return response($content, $status, ['Content-Type' => 'text/vnd.turbo-stream.html']);
    }
}
