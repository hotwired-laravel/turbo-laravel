<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Tonysm\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use Tonysm\TurboLaravel\Http\PendingTurboStreamResponse;
use Tonysm\TurboLaravel\Http\TurboResponseFactory;
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

if (! function_exists('turbo_stream_view')) {
    /**
     * Renders a Turbo Stream view wrapped with the correct Content-Types in the response.
     *
     * @param string|\Illuminate\View\View $view
     * @param array $data = [] the binding params to be passed to the view.
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    function turbo_stream_view($view, array $data = []): Response|ResponseFactory
    {
        if (! $view instanceof View) {
            $view = view($view, $data);
        }

        return TurboResponseFactory::makeStream($view->render());
    }
}
