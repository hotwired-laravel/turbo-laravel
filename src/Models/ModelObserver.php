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
    public $afterCommit;

    public function __construct()
    {
        $this->afterCommit = config('turbo-laravel.queue');
    }

    /**
     * @param Model|Broadcasts $model
     */
    public function created(Model $model)
    {
        if (! $this->shouldBroadcast($model)) {
            return;
        }

        $model->broadcastInsert()->later();
    }

    /**
     * @param Model|Broadcasts $model
     */
    public function updated(Model $model)
    {
        if (! $this->shouldBroadcast($model)) {
            return;
        }

        $model->broadcastReplace()->later();
    }

    /**
     * @param Model|Broadcasts $model
     */
    public function deleted(Model $model)
    {
        if (! $this->shouldBroadcast($model)) {
            return;
        }

        $model->broadcastRemove()->later();
    }

    private function shouldBroadcast(Model $model): bool
    {
        if (property_exists($model, 'broadcasts')) {
            return true;
        }

        if (property_exists($model, 'broadcastsTo')) {
            return true;
        }

        return method_exists($model, 'broadcastsTo');
    }
}
