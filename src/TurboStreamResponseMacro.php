<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Database\Eloquent\Model;
use Tonysm\TurboLaravel\Http\TurboResponseFactory;

class TurboStreamResponseMacro
{
    public function handle(Model $model, string $action = null)
    {
        if (! $model->exists) {
            return $this->renderModelDeletedStream($model);
        }

        if ($model->wasRecentlyCreated) {
            return $this->renderModelCreatedStream($model, $action);
        }

        return $this->renderModelUpdatedStream($model, $action);
    }

    /**
     * @param Model $model
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    private function renderModelDeletedStream(Model $model)
    {
        $view = $this->turboStreamView($model, 'deleted', 'turbo-laravel::model-removed');

        return TurboResponseFactory::makeStream(view($view, [
            'target' => method_exists($model, 'hotwireTargetDomId')
                ? $model->hotwireTargetDomId()
                : NamesResolver::resourceId($model, $model->id),
            'action' => 'remove',
        ]));
    }

    /**
     * @param Model $model
     * @param string|null $action
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    private function renderModelCreatedStream(Model $model, string $action = null)
    {
        $action = $action ?: 'append';

        $view = $this->turboStreamView($model, 'created', 'turbo-laravel::model-saved');

        return TurboResponseFactory::makeStream(view($view, [
            'target' => method_exists($model, 'hotwireTargetResourcesName')
                ? $model->hotwireTargetResourcesName()
                : NamesResolver::resourceName($model),
            'action' => $action,
            'resourcePartialName' => method_exists($model, 'hotwirePartialName')
                ? $model->hotwirePartialName()
                : NamesResolver::partialNameFor($model),
            'data' => method_exists($model, 'hotwirePartialData')
                ? $model->hotwirePartialData()
                : [NamesResolver::resourceVariableName($model) => $model],
        ]));
    }

    private function renderModelUpdatedStream(Model $model, $action)
    {
        $action = $action ?: 'replace';

        $view = $this->turboStreamView($model, 'created', 'turbo-laravel::model-saved');

        return TurboResponseFactory::makeStream(view($view, [
            'target' => method_exists($model, 'hotwireTargetDomId')
                ? $model->hotwireTargetDomId()
                : NamesResolver::resourceId(get_class($model), $model->id),
            'action' => $action,
            'resourcePartialName' => method_exists($model, 'hotwirePartialName')
                ? $model->hotwirePartialName()
                : NamesResolver::partialNameFor($model),
            'data' => method_exists($model, 'hotwirePartialData')
                ? $model->hotwirePartialData()
                : [NamesResolver::resourceVariableName($model) => $model],
        ]));
    }

    private function turboStreamView(Model $model, string $event, string $default): string
    {
        $resourceName = NamesResolver::resourceName($model);

        $view = "{$resourceName}.turbo.{$event}_stream";

        if (! view()->exists($view)) {
            return $default;
        }

        return $view;
    }
}
