<?php

namespace Tonysm\TurboLaravel\Http;

use Illuminate\Contracts\Support\Responsable;

class PendingTurboStreamResponse implements Responsable
{
    public string $target;
    public string $action;
    public ?string $partial;
    public array $partialData;

    public function toResponse($request)
    {
    }
}
