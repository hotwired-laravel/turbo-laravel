<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Database\Eloquent\Model;
use Tonysm\TurboLaravel\Http\TurboResponseFactory;

class TurboStreamResponseMacro
{
    public function handle(Model $model, string $action = null)
    {
        if (!$model->exists) {
            return $this->renderModelRemovedStream($model);
        }

        return $this->renderModelAddedStream($model, $action);
    }

    /**
     * @param Model $model
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    private function renderModelRemovedStream(Model $model)
    {
        return TurboResponseFactory::makeStream(view()->file(__DIR__ . '/../resources/views/model-removed.blade.php', [
            'target' => method_exists($model, 'hotwireTargetDomId')
                ? $model->hotwireTargetDomId()
                : NamesResolver::resourceId($model, $model->id),
        ]));
    }

    /**
     * @param Model $model
     * @param string|null $action
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    private function renderModelAddedStream(Model $model, string $action = null)
    {
        $action = $action ?? ($model->wasRecentlyCreated ? 'append' : 'update');

        return TurboResponseFactory::makeStream(view()->file(__DIR__ . '/../resources/views/model-saved.blade.php', [
            'target' => method_exists($model, 'hotwireTargetDomId')
                ? $model->hotwireTargetDomId()
                : NamesResolver::resourceName($model),
            'action' => $action,
            'resourcePartialName' => method_exists($model, 'hotwirePartialName')
                ? $model->hotwirePartialName()
                : NamesResolver::partialNameFor($model),
            'data' => method_exists($model, 'hotwirePartialData')
                ? $model->hotwirePartialData()
                : [NamesResolver::resourceNameSingular($model) => $model],
        ]));
    }
}
