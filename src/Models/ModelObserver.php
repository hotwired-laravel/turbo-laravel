<?php

namespace Tonysm\TurboLaravel\Models;

use Illuminate\Database\Eloquent\Model;

class ModelObserver
{
    public function saved(Model $model)
    {
        $model->queueBroadcastToHotwire();
    }

    public function deleted(Model $model)
    {
        $model->queueBroadcastRemovalToHotwire();
    }
}
