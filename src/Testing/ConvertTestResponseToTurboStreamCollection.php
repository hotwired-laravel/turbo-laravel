<?php

namespace Tonysm\TurboLaravel\Testing;

use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;

class ConvertTestResponseToTurboStreamCollection
{
    public function __invoke(TestResponse $response): Collection
    {
        $parsed = simplexml_load_string(<<<XML
        <xml>{$response->content()}</xml>
        XML);

        return collect(json_decode(json_encode($parsed), true)['turbo-stream']);
    }
}
