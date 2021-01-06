<?php

namespace Tonysm\TurboLaravel\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\View;

class HotwireBroadcast implements ShouldBroadcast
{
    public $model;
    public $modelId;
    public $action;
    public $broadcastsToClass;
    public $broadcastsToId;

    public function __construct($model, $modelId, $action, $broadcastsToClass, $broadcastsToId)
    {
        $this->model = $model;
        $this->modelId = $modelId;
        $this->action = $action;
        $this->broadcastsToClass = $broadcastsToClass;
        $this->broadcastsToId = $broadcastsToId;
    }

    public function broadcastOn()
    {
        $sendTo = $this->broadcastsToClass::findOrNew($this->broadcastsToId);

        return $sendTo->hotwireBroadcastsOn();
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->render(),
        ];
    }

    private function render()
    {
        if ($this->action === "remove") {
            return View::file(__DIR__ . '/../../resources/views/model-removed.blade.php', [
                'target' => (new $this->model)->forceFill(['id' => $this->modelId])->hotwireTargetDomId(),
                'action' => 'remove',
            ])->render();
        }

        $model = $this->model::findOrFail($this->model->id);

        return View::file(__DIR__ . '/../../resources/views/model-saved.blade.php', [
            'target' => $model->hotwireTargetDomId(),
            'action' => $this->action,
            'resourcePartialName' => $model->hotwirePartialName(),
            'data' => $model->hotwirePartialData(),
        ])->render();
    }
}
