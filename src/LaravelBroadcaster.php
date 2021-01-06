<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Database\Eloquent\Model;
use Tonysm\TurboLaravel\Events\HotwireBroadcast;

class LaravelBroadcaster
{
    public function update($model, string $action)
    {
        foreach ($model->hotwireBrodcastingTargets() as $target) {
            broadcast(new HotwireBroadcast(
                get_class($model),
                $model->id,
                $action,
                get_class($target),
                $target->id
            ));
        }
    }

    public function remove(Model $model)
    {
        foreach ($model->hotwireBrodcastingTargets() as $target) {
            broadcast(new HotwireBroadcast(
                get_class($model),
                $model->id,
                'remove',
                get_class($target),
                $target->id
            ));
        }
    }
}
