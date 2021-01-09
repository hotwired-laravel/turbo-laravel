<?php

namespace Tonysm\TurboLaravel\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Database\Eloquent\Model;
use Tonysm\TurboLaravel\Models\Broadcasts;
use Tonysm\TurboLaravel\TurboStreamChannelsResolver;
use Tonysm\TurboLaravel\TurboStreamModelRenderer;

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
        return resolve(TurboStreamChannelsResolver::class)->hotwireBroadcastsOn($this->model);
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->render(),
        ];
    }

    public function render(): string
    {
        return resolve(TurboStreamModelRenderer::class)
            ->renderDeleted($this->model, 'remove')
            ->render();
    }
}
