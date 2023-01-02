<?php

namespace Tonysm\TurboLaravel\Models;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Collection;
use Tonysm\TurboLaravel\Broadcasting\PendingBroadcast;
use Tonysm\TurboLaravel\Broadcasting\Rendering;
use function Tonysm\TurboLaravel\dom_id;
use Tonysm\TurboLaravel\Facades\TurboStream;

use Tonysm\TurboLaravel\Models\Naming\Name;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait Broadcasts
{
    public static function bootBroadcasts()
    {
        static::observe(new ModelObserver());
    }

    public function broadcastAppend(): PendingBroadcast
    {
        return $this->broadcastAppendTo(
            $this->brodcastDefaultStreamables(inserting: true)
        );
    }

    public function broadcastPrepend(): PendingBroadcast
    {
        return $this->broadcastPrependTo(
            $this->brodcastDefaultStreamables(inserting: true)
        );
    }

    public function broadcastBefore(string $target, bool $inserting = true): PendingBroadcast
    {
        return $this->broadcastBeforeTo(
            $this->brodcastDefaultStreamables($inserting),
            $target
        );
    }

    public function broadcastAfter(string $target, bool $inserting = true): PendingBroadcast
    {
        return $this->broadcastAfterTo(
            $this->brodcastDefaultStreamables($inserting),
            $target
        );
    }

    public function broadcastInsert(): PendingBroadcast
    {
        $action = is_array($this->broadcasts) && isset($this->broadcasts['insertsBy'])
            ? $this->broadcasts['insertsBy']
            : 'append';

        return $this->broadcastActionTo(
            $this->brodcastDefaultStreamables(inserting: true),
            $action,
            Rendering::forModel($this),
        );
    }

    public function broadcastReplace(): PendingBroadcast
    {
        return $this->broadcastReplaceTo(
            $this->brodcastDefaultStreamables()
        );
    }

    public function broadcastUpdate(): PendingBroadcast
    {
        return $this->broadcastUpdateTo(
            $this->brodcastDefaultStreamables()
        );
    }

    public function broadcastRemove(): PendingBroadcast
    {
        return $this->broadcastRemoveTo(
            $this->brodcastDefaultStreamables()
        );
    }

    public function broadcastAppendTo($streamable): PendingBroadcast
    {
        return $this->broadcastActionTo($streamable, 'append', Rendering::forModel($this));
    }

    public function broadcastPrependTo($streamable): PendingBroadcast
    {
        return $this->broadcastActionTo($streamable, 'prepend', Rendering::forModel($this));
    }

    public function broadcastBeforeTo($streamable, string $target): PendingBroadcast
    {
        return $this->broadcastActionTo($streamable, 'before', Rendering::forModel($this), $target);
    }

    public function broadcastAfterTo($streamable, string $target): PendingBroadcast
    {
        return $this->broadcastActionTo($streamable, 'after', Rendering::forModel($this), $target);
    }

    public function broadcastReplaceTo($streamable): PendingBroadcast
    {
        return $this->broadcastActionTo($streamable, 'replace', Rendering::forModel($this));
    }

    public function broadcastUpdateTo($streamable): PendingBroadcast
    {
        return $this->broadcastActionTo($streamable, 'update', Rendering::forModel($this));
    }

    public function broadcastRemoveTo($streamable): PendingBroadcast
    {
        return $this->broadcastActionTo($streamable, 'remove', Rendering::empty());
    }

    public function asTurboStreamBroadcastingChannel()
    {
        return $this->toChannels(Collection::wrap($this->brodcastDefaultStreamables($this->wasRecentlyCreated)));
    }

    protected function broadcastActionTo($streamables, string $action, Rendering $rendering, ?string $target = null): PendingBroadcast
    {
        return TurboStream::broadcastAction(
            action: $action,
            target: $target ?: $this->broadcastDefaultTarget($action),
            targets: null,
            channel: $this->toChannels(Collection::wrap($streamables)),
            content: $rendering,
        );
    }

    protected function brodcastDefaultStreamables(bool $inserting = false)
    {
        if (property_exists($this, 'broadcastsTo')) {
            return Collection::wrap($this->broadcastsTo)
                ->map(fn ($related) => $this->{$related})
                ->values()
                ->all();
        }

        if (method_exists($this, 'broadcastsTo')) {
            return $this->broadcastsTo();
        }

        if ($inserting && is_array($this->broadcasts) && isset($this->broadcasts['stream'])) {
            return $this->broadcasts['stream'];
        }

        if ($inserting) {
            return Name::forModel($this)->plural;
        }

        return $this;
    }

    protected function toChannels(Collection $streamables): array
    {
        return $streamables->filter()->map(function ($streamable) {
            if ($streamable instanceof Channel) {
                return $streamable;
            }

            return new PrivateChannel(
                is_string($streamable) ? $streamable : $streamable->broadcastChannel()
            );
        })->values()->all();
    }

    protected function broadcastDefaultTarget(string $action): string
    {
        // Inserting the new element in the DOM will affect
        // the parent container, while the other actions
        // will, by default, only affect the element.

        if (in_array($action, ['append', 'prepend'])) {
            return Name::forModel($this)->plural;
        }

        return dom_id($this);
    }
}
