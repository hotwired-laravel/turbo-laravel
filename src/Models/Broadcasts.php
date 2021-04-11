<?php

namespace Tonysm\TurboLaravel\Models;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Collection;
use Tonysm\TurboLaravel\Broadcasting\PendingBroadcast;
use Tonysm\TurboLaravel\Broadcasting\Rendering;
use function Tonysm\TurboLaravel\dom_id;
use Tonysm\TurboLaravel\Models\Naming\Name;
use Tonysm\TurboLaravel\NamesResolver;

use Tonysm\TurboLaravel\Views\RecordIdentifier;

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
            $this->broadcastDetaultTargets()
        );
    }

    public function broadcastPrepend(): PendingBroadcast
    {
        return $this->broadcastPrependTo(
            $this->broadcastDetaultTargets()
        );
    }

    public function broadcastInsert(): PendingBroadcast
    {
        $action = is_array($this->broadcasts)
            ? $this->broadcasts['insertsBy']
            : 'append';

        return $this->broadcastActionTo(
            $this->broadcastDetaultTargets(),
            $action,
            Rendering::forModel($this),
        );
    }

    public function broadcastReplace(): PendingBroadcast
    {
        return $this->broadcastReplaceTo(
            $this->broadcastDetaultTargets()
        );
    }

    public function broadcastUpdate(): PendingBroadcast
    {
        return $this->broadcastUpdateTo(
            $this->broadcastDetaultTargets()
        );
    }

    public function broadcastRemove(): PendingBroadcast
    {
        return $this->broadcastRemoveTo(
            $this->broadcastDetaultTargets()
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

    protected function broadcastActionTo($streamables, string $action, Rendering $rendering): PendingBroadcast
    {
        return new PendingBroadcast(
            $this->toChannels(Collection::wrap($streamables)),
            $action,
            $this->broadcastDefaultTarget($action),
            $rendering,
        );
    }

    protected function broadcastDetaultTargets()
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

        return $this;
    }

    protected function toChannels(Collection $streamables): array
    {
        return $streamables->filter()->map(function ($streamable) {
            if ($streamable instanceof Channel) {
                return $streamable;
            }

            return new PrivateChannel(
                (new RecordIdentifier($streamable))->channelName()
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

    protected function broadcastDefaultPartial(): string
    {
        return NamesResolver::partialNameFor($this);
    }
}
