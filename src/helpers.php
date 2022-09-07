<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Tonysm\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use Tonysm\TurboLaravel\Http\PendingTurboStreamResponse;
use Tonysm\TurboLaravel\Views\RecordIdentifier;

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
        return (new RecordIdentifier($model))->domId($prefix);
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
        return (new RecordIdentifier($model))->domClass($prefix);
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
        if (is_array($model) || $model instanceof Collection) {
            return MultiplePendingTurboStreamResponse::forStreams($model);
        }

        if ($model === null) {
            return new PendingTurboStreamResponse();
        }

        return PendingTurboStreamResponse::forModel($model, $action);
    }
}
