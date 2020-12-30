<?php

namespace Tonysm\TurboLaravel\Models;

use Illuminate\Database\Eloquent\Model;

class ModelObserver
{
    /**
     * @param Model|Broadcasts $model
     */
    public function saved(Model $model)
    {
        $model->queueBroadcastToHotwire();
    }

    /**
     * @param Model|Broadcasts $model
     */
    public function deleted(Model $model)
    {
        $model->queueBroadcastRemovalToHotwire();
    }
}
