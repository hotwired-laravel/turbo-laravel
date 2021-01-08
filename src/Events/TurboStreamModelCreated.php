<?php

namespace Tonysm\TurboLaravel\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Tonysm\TurboLaravel\Models\Broadcasts;

class TurboStreamModelCreated implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public Model $model;
    public string $action;

    /**
     * TurboStreamModelCreated constructor.
     *
     * @param Model|Broadcasts $model
     * @param string $action
     */
    public function __construct(Model $model, string $action = "append")
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

    public function render()
    {
        return View::make('turbo-laravel::model-saved', [
            'target' => $this->model->hotwireTargetResourcesName(),
            'action' => $this->action,
            'resourcePartialName' => $this->model->hotwirePartialName(),
            'data' => $this->model->hotwirePartialData(),
        ])->render();
    }
}
