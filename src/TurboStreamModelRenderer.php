<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Tonysm\TurboLaravel\Models\Naming\Name;
use Tonysm\TurboLaravel\Views\RecordIdentifier;

class TurboStreamModelRenderer
{
    public function renderCreated(Model $model, string $action): ViewContract
    {
        $target = method_exists($model, 'hotwireTargetResourcesName')
            ? $model->hotwireTargetResourcesName()
            : Name::forModel($model)->plural;

        return $this->renderSaved($model, $action, $target, 'created');
    }

    public function renderUpdated(Model $model, string $action): ViewContract
    {
        $target = method_exists($model, 'hotwireTargetDomId')
            ? $model->hotwireTargetDomId()
            : (new RecordIdentifier($model))->domId();

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

        return View::make('turbo-laravel::turbo-stream', [
            'target' => method_exists($model, 'hotwireTargetDomId')
                ? $model->hotwireTargetDomId()
                : (new RecordIdentifier($model))->domId(),
            'action' => $action,
        ]);
    }

    private function turboStreamView(Model $model, string $event): ?string
    {
        $resourceName = Name::forModel($model)->plural;

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

        return View::make('turbo-laravel::turbo-stream', [
            'target' => $target,
            'action' => $action,
            'partial' => method_exists($model, 'hotwirePartialName')
                ? $model->hotwirePartialName()
                : NamesResolver::partialNameFor($model),
            'partialData' => $partialData,
        ]);
    }
}
