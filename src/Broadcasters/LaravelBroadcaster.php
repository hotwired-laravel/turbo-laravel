<?php

namespace Tonysm\TurboLaravel\Broadcasters;

use Illuminate\Database\Eloquent\Model;
use Tonysm\TurboLaravel\Events\TurboStreamModelCreated;
use Tonysm\TurboLaravel\Events\TurboStreamModelDeleted;
use Tonysm\TurboLaravel\Events\TurboStreamModelUpdated;

class LaravelBroadcaster implements Broadcaster
{
    private ?string $exceptSocket;

    public function exceptForSocket(string $socket = null): self
    {
        $this->exceptSocket = $socket;

        return $this;
    }

    public function create(Model $model): void
    {
        $action = property_exists($model, 'turboStreamCreatedAction')
            ? $model->turboStreamCreatedAction
            : 'append';

        $this->broadcast(new TurboStreamModelCreated(
            $model,
            $action
        ));
    }

    public function update(Model $model): void
    {
        $action = property_exists($model, 'turboStreamUpdatedAction')
            ? $model->turboStreamUpdatedAction
            : 'replace';

        $this->broadcast(new TurboStreamModelUpdated($model, $action));
    }

    public function remove(Model $model): void
    {
        $this->broadcast(new TurboStreamModelDeleted(
            $model,
            'remove'
        ));
    }

    private function broadcast($event): void
    {
        if ($this->exceptSocket ?? false && property_exists($event, 'socket')) {
            $event->socket = $this->exceptSocket;
        }

        broadcast($event);
    }
}
