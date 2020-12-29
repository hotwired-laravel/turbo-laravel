<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Database\Eloquent\Model;
use Tonysm\TurboLaravel\Events\HotwireBroadcast;

class TurboLaravelDefaultBroadcaster
{
    public function update($model, string $action)
    {
        foreach ($model->hotwireBrodcastingTargets() as $target) {
            broadcast(new HotwireBroadcast(
                $model::class,
                $model->id,
                $action,
                $target::class,
                $target->id
            ));
        }
    }

    public function remove(Model $model)
    {
        foreach ($model->hotwireBrodcastingTargets() as $target) {
            broadcast(new HotwireBroadcast(
                $model::class,
                $model->id,
                'remove',
                $target::class,
                $target->id
            ));
        }
    }
}
