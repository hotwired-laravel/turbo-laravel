<?php

namespace Tonysm\TurboLaravel\Models;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Broadcast;
use Tonysm\TurboLaravel\Jobs\BroadcastModelCreated;
use Tonysm\TurboLaravel\Jobs\BroadcastModelUpdated;
use Tonysm\TurboLaravel\LaravelBroadcaster;
use Tonysm\TurboLaravel\NamesResolver;
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
        // Removals cannot be cached because we need to gather the broadcasting targets
        // using the model instance's relationships before the entity is "gone".

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

    public function hotwireTargetDomId()
    {
        return $this->hotwireResolveNamesUsing()->resourceId(static::class, $this->id);
    }

    public function hotwireTargetResourcesName()
    {
        return $this->hotwireResolveNamesUsing()->resourceName($this);
    }

    public function hotwireBroadcastingTargets()
    {
        if (property_exists($this, 'broadcastsTo')) {
            return Collection::wrap($this->broadcastsTo)
                ->map(function ($attr) {
                    return data_get($this, $attr);
                })
                ->all();
        }

        if (method_exists($this, 'broadcastsTo')) {
            return $this->broadcastsTo();
        }

        return $this;
    }

    public function hotwireBroadcastsOn()
    {
        return Collection::wrap($this->hotwireBroadcastingTargets())
            ->filter()
            ->map(function ($item) {
                if ($item instanceof Channel) {
                    return $item;
                }

                return new PrivateChannel(
                    $this->hotwireResolveNamesUsing()->modelPathToChannelName(get_class($item), $item->id)
                );
            })
            ->all();
    }

    public function hotwireResolveNamesUsing(): NamesResolver
    {
        return resolve(NamesResolver::class);
    }

    public function hotwirePartialName()
    {
        return $this->hotwireResolveNamesUsing()->partialNameFor($this);
    }

    public function hotwirePartialData()
    {
        return [
            $this->hotwireResolveNamesUsing()->resourceVariableName($this) => $this,
        ];
    }
}
