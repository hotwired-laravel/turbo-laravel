<?php

namespace Tonysm\TurboLaravel\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Tonysm\TurboLaravel\Models\Broadcasts;
use Tonysm\TurboLaravel\NamesResolver;

class TurboStreamModelDeleted implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public Model $model;
    public string $action;

    /**
     * TurboStreamModelDeleted constructor.
     *
     * @param Model|Broadcasts $model
     * @param string $action
     */
    public function __construct(Model $model, string $action = "remove")
    {
        $this->model = $model;
        $this->action = $action;
    }

    public function broadcastOn()
    {
        return $this->model->hotwireBroadcastsOn();
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->render(),
        ];
    }

    public function render(): string
    {
        if ($turboView = $this->turboStreamView($this->model, 'deleted')) {
            return View::make($turboView, $this->model->hotwirePartialData())->render();
        }

        return View::make('turbo-laravel::model-removed', [
            'target' => $this->model->hotwireTargetDomId(),
            'action' => $this->action,
            'resourcePartialName' => $this->model->hotwirePartialName(),
            'data' => $this->model->hotwirePartialData(),
        ])->render();
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
}
