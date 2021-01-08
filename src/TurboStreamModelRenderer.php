<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;

class TurboStreamModelRenderer
{
    public function renderCreated(Model $model, string $action): ViewContract
    {
        $target = method_exists($model, 'hotwireTargetResourcesName')
            ? $model->hotwireTargetResourcesName()
            : NamesResolver::resourceName($model);

        return $this->renderSaved($model, $action, $target, 'created');
    }

    public function renderUpdated(Model $model, string $action): ViewContract
    {
        $target = method_exists($model, 'hotwireTargetDomId')
            ? $model->hotwireTargetDomId()
            : NamesResolver::resourceIdFor($model);

        return $this->renderSaved($model, $action, $target, 'updated');
    }

    public function renderDeleted(Model $model, string $action): ViewContract
    {
        if ($turboView = $this->turboStreamView($model, 'deleted')) {
            $partialData = method_exists($model, 'hotwirePartialData')
                ? $model->hotwirePartialData()
                : [NamesResolver::resourceVariableName($model) => $model];

            return View::make($turboView, $partialData);
        }

        return View::make('turbo-laravel::model-removed', [
            'target' => method_exists($model, 'hotwireTargetDomId')
                ? $model->hotwireTargetDomId()
                : NamesResolver::resourceId($model, $model->id),
            'action' => $action,
        ]);
    }

    private function turboStreamView(Model $model, string $event): ?string
    {
        $resourceName = NamesResolver::resourceName($model);

        $view = "{$resourceName}.turbo.{$event}_stream";

        if (! view()->exists($view)) {
            return null;
        }

        return $view;
    }

    private function renderSaved(Model $model, string $action, string $target, string $event): ViewContract
    {
        $partialData = method_exists($model, 'hotwirePartialData')
            ? $model->hotwirePartialData()
            : [NamesResolver::resourceVariableName($model) => $model];

        if ($turboView = $this->turboStreamView($model, $event)) {
            return View::make($turboView, $partialData);
        }

        return View::make('turbo-laravel::model-saved', [
            'target' => $target,
            'action' => $action,
            'resourcePartial' => method_exists($model, 'hotwirePartialName')
                ? $model->hotwirePartialName()
                : NamesResolver::partialNameFor($model),
            'resourcePartialData' => $partialData,
        ]);
    }
}
