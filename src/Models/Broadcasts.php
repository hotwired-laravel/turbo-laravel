<?php

namespace Tonysm\TurboLaravel\Models;

use Illuminate\Support\Facades\Broadcast;
use Tonysm\TurboLaravel\Jobs\BroadcastModelCreated;
use Tonysm\TurboLaravel\Jobs\BroadcastModelUpdated;
use Tonysm\TurboLaravel\LaravelBroadcaster;
use Tonysm\TurboLaravel\TurboFacade;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait Broadcasts
{
    /**
     * Only dispatch the observer's events after all database transactions have committed.
     *
     * @var bool
     */
    public $afterCommit = true;

    public static function bootBroadcasts()
    {
        static::observe(new ModelObserver());
    }

    public function queueBroadcastCreatedToHotwire()
    {
        if (! config('turbo-laravel.queue')) {
            $this->hotwireBroadcastUsing()
                ->exceptForSocket(TurboFacade::shouldBroadcastToOthers() ? Broadcast::socket() : null)
                ->create($this);

            return;
        }

        dispatch(new BroadcastModelCreated(
            $this,
            TurboFacade::shouldBroadcastToOthers() ? Broadcast::socket() : null
        ));
    }

    public function queueBroadcastUpdatedToHotwire()
    {
        if (! config('turbo-laravel.queue')) {
            $this->hotwireBroadcastUsing()
                ->exceptForSocket(TurboFacade::shouldBroadcastToOthers() ? Broadcast::socket() : null)
                ->update($this);

            return;
        }

        dispatch(new BroadcastModelUpdated(
            $this,
            TurboFacade::shouldBroadcastToOthers() ? Broadcast::socket() : null
        ));
    }

    public function queueBroadcastRemovalToHotwire()
    {
        // We cannot queue removal broadcasts because the model will be gone once the worker
        // picks up the job to process the broadcasting. So we are broadcasting after the
        // response is sent to back to the user, before the PHP process is terminated.

        app()->terminating(function () {
            $this->hotwireBroadcastUsing()
                ->exceptForSocket(TurboFacade::shouldBroadcastToOthers() ? Broadcast::socket() : null)
                ->remove($this);
        });
    }

    public function hotwireBroadcastUsing()
    {
        return resolve(LaravelBroadcaster::class);
    }
}
