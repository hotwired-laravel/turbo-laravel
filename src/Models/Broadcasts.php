<?php

namespace Tonysm\TurboLaravel\Models;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Collection;
use Tonysm\TurboLaravel\Jobs\BroadcastModelCreated;
use Tonysm\TurboLaravel\Jobs\BroadcastModelUpdated;
use Tonysm\TurboLaravel\NamesResolver;
use Tonysm\TurboLaravel\LaravelBroadcaster;

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
            $this->hotwireBroadcastUsing()->create($this);
            return;
        }

        dispatch(new BroadcastModelCreated($this));
    }


    public function queueBroadcastUpdatedToHotwire()
    {
        if (! config('turbo-laravel.queue')) {
            $this->hotwireBroadcastUsing()->update($this);
            return;
        }

        dispatch(new BroadcastModelUpdated($this));
    }

    public function queueBroadcastRemovalToHotwire()
    {
        // Removals cannot be cached because we need to gather the broadcasting targets
        // using the model instance's relationships before the entity is "gone".

        $this->hotwireBroadcastUsing()->remove($this);
    }

    public function hotwireBroadcastUsing()
    {
        return resolve(LaravelBroadcaster::class);
    }

    public function hotwireBroadcastingRule(): string
    {
        return $this->wasRecentlyCreated ? 'append' : 'update';
    }

    public function hotwireTargetDomId()
    {
        return $this->hotwireResolveNamesUsing()->resourceId(static::class, $this->id);
    }

    public function hotwireTargetResourcesName()
    {
        return $this->hotwireResolveNamesUsing()->resourceName($this);
    }

    public function hotwireBrodcastingTargets()
    {
        if ($this->exists && property_exists($this, 'broadcastsTo')) {
            return Collection::wrap($this->broadcastsTo)
                ->map(fn ($attr) => data_get($this, $attr))
                ->all();
        }

        if ($this->exists && method_exists($this, 'broadcastsTo')) {
            return $this->broadcastsTo();
        }

        return $this;
    }

    public function hotwireBroadcastsOn()
    {
        return new PrivateChannel(
            $this->hotwireResolveNamesUsing()->modelPathToChannelName(get_class($this), $this->id)
        );
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
            $this->hotwireResolveNamesUsing()->resourceNameSingular($this) => $this,
        ];
    }
}
