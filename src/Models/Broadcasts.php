<?php

namespace Tonysm\TurboLaravel\Models;

use Illuminate\Support\Facades\Broadcast;
use Tonysm\TurboLaravel\Jobs\BroadcastModelCreated;
use Tonysm\TurboLaravel\Jobs\BroadcastModelUpdated;
use Tonysm\TurboLaravel\LaravelBroadcaster;
use Tonysm\TurboLaravel\Facades\Turbo;
use Tonysm\TurboLaravel\TurboStreamChannelsResolver;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait Broadcasts
{
    public static function bootBroadcasts()
    {
        static::observe(new ModelObserver());
    }

    public function hotwireBroadcastCreatedLater()
    {
        $exceptSocket = Turbo::shouldBroadcastToOthers() ? Broadcast::socket() : null;

        if (! config('turbo-laravel.queue')) {
            $this->hotwireBroadcastCreatedNow($exceptSocket);

            return;
        }

        dispatch(new BroadcastModelCreated(
            $this,
            $exceptSocket
        ));
    }

    public function hotwireBroadcastCreatedNow(string $exceptSocket = null): void
    {
        $this->hotwireBroadcastUsing()
            ->exceptForSocket($exceptSocket)
            ->create($this);
    }

    public function hotwireBroadcastUpdatedLater()
    {
        $exceptSocket = Turbo::shouldBroadcastToOthers() ? Broadcast::socket() : null;

        if (! config('turbo-laravel.queue')) {
            $this->hotwireBroadcastUpdatedNow($exceptSocket);

            return;
        }

        dispatch(new BroadcastModelUpdated(
            $this,
            $exceptSocket
        ));
    }

    public function hotwireBroadcastUpdatedNow(string $exceptSocket = null)
    {
        $this->hotwireBroadcastUsing()
            ->exceptForSocket($exceptSocket)
            ->update($this);
    }

    public function hotwireBroadcastRemovalLater()
    {
        $exceptSocket = Turbo::shouldBroadcastToOthers() ? Broadcast::socket() : null;

        // We cannot queue removal broadcasts because the model will be gone once the worker
        // picks up the job to process the broadcasting. So we are broadcasting after the
        // response is sent to back to the user, before the PHP process is terminated.

        app()->terminating(function () use ($exceptSocket) {
            $this->hotwireBroadcastRemovalNow($exceptSocket);
        });
    }

    public function hotwireBroadcastRemovalNow(string $exceptSocket = null): void
    {
        $this->hotwireBroadcastUsing()
            ->exceptForSocket($exceptSocket)
            ->remove($this);
    }

    public function hotwireBroadcastUsing()
    {
        return resolve(LaravelBroadcaster::class);
    }

    public function hotwireResolveBroadcastChannelNamesUsing()
    {
        return resolve(TurboStreamChannelsResolver::class);
    }
}
