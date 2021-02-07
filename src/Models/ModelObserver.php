<?php

namespace Tonysm\TurboLaravel\Models;

use Illuminate\Database\Eloquent\Model;

class ModelObserver
{
    /**
     * Only dispatch the observer's events after all database transactions have committed.
     *
     * @var bool
     */
    public $afterCommit = true;

    /**
     * @param Model|Broadcasts $model
     */
    public function created(Model $model)
    {
        $model->hotwireBroadcastCreatedLater();
    }

    /**
     * @param Model|Broadcasts $model
     */
    public function updated(Model $model)
    {
        $model->hotwireBroadcastUpdatedLater();
    }

    /**
     * @param Model|Broadcasts $model
     */
    public function deleted(Model $model)
    {
        $model->hotwireBroadcastRemovalLater();
    }
}
