<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Tonysm\TurboLaravel\Events\TurboStreamModelCreated;
use Tonysm\TurboLaravel\Events\TurboStreamModelDeleted;
use Tonysm\TurboLaravel\Events\TurboStreamModelUpdated;

class LaravelBroadcaster
{
    public function create($model)
    {
        $action = property_exists($model, 'turboStreamCreatedAction')
            ? $model->turboStreamCreatedAction
            : 'append';

        broadcast(new TurboStreamModelCreated(
            $model,
            $action
        ));
    }

    public function update($model)
    {
        $action = property_exists($model, 'turboStreamUpdatedAction')
            ? $model->turboStreamUpdatedAction
            : 'update';

        broadcast(new TurboStreamModelUpdated(
            $model,
            $action,
        ));
    }

    public function remove(Model $model)
    {
        $action = property_exists($model, 'turboStreamDeletedAction')
            ? $model->turboStreamDeletedAction
            : 'remove';

        broadcast(new TurboStreamModelDeleted(
            $model,
            $action
        ));
    }
}
