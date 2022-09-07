<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use function Tonysm\TurboLaravel\dom_class as base_dom_class;
use function Tonysm\TurboLaravel\dom_id as base_dom_id;

use Tonysm\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use Tonysm\TurboLaravel\Http\PendingTurboStreamResponse;
use function Tonysm\TurboLaravel\turbo_stream as base_turbo_stream;

if (! function_exists('dom_id')) {
    /**
     * Generates the DOM ID for a specific model.
     *
     * @param object $model
     * @param string $prefix
     *
     * @return string
     */
    function dom_id(object $model, string $prefix = ""): string
    {
        return base_dom_id($model, $prefix);
    }
}

if (! function_exists('dom_class')) {
    /**
     * Generates the DOM CSS Class for a specific model.
     *
     * @param object $model
     * @param string $prefix
     * @return string
     */
    function dom_class(object $model, string $prefix = ""): string
    {
        return base_dom_class($model, $prefix);
    }
}

if (! function_exists('turbo_stream')) {
    /**
     * Builds the Turbo Streams.
     *
     * @param Model|Collection|array|string|null $model = null
     * @param string|null $action = null
     */
    function turbo_stream($model = null, string $action = null): MultiplePendingTurboStreamResponse|PendingTurboStreamResponse
    {
        return base_turbo_stream($model, $action);
    }
}
